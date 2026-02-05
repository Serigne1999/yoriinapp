<?php

namespace Modules\Multiservices\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Multiservices\Entities\Operator;
use Yajra\DataTables\Facades\DataTables;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            abort(403, 'Accès non autorisé');
        }

        $businessId = auth()->user()->business_id;

        if ($request->ajax()) {
            $operators = Operator::forBusiness($businessId)
                ->orderBy('display_order')
                ->select('multiservice_operators.*');

            return DataTables::of($operators)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<button class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>';
                    $html .= '<ul class="dropdown-menu dropdown-menu-right ">';
                    $html .= '<li><a href="#" class="edit-operator" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Modifier</a></li>';
                    
                    if ($row->is_active) {
                        $html .= '<li><a href="#" class="toggle-operator" data-id="' . $row->id . '"><i class="fa fa-eye-slash"></i> Désactiver</a></li>';
                    } else {
                        $html .= '<li><a href="#" class="toggle-operator" data-id="' . $row->id . '"><i class="fa fa-eye"></i> Activer</a></li>';
                    }
                    
                    $html .= '<li><a href="#" class="delete-operator" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Supprimer</a></li>';
                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('logo', function ($row) {
                    if ($row->logo) {
                        // Vérifier si c'est une URL externe ou un chemin local
                        $logoSrc = (strpos($row->logo, 'http') === 0) 
                            ? $row->logo  // URL externe
                            : '/' . $row->logo;  // Fichier local
                        
                        return '<img src="' . $logoSrc . '" style="max-width: 60px; max-height: 60px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd; padding: 5px; background: white;">';
                    } elseif ($row->icon) {
                        return '<i class="fa ' . $row->icon . ' fa-2x"></i>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active 
                        ? '<span class="label label-success">Actif</span>' 
                        : '<span class="label label-default">Inactif</span>';
                })
                ->rawColumns(['action', 'logo', 'is_active'])
                ->make(true);
        }

        return view('multiservices::operators.index');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            return response()->json(['success' => false, 'msg' => 'Accès non autorisé']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|alpha_dash',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $businessId = auth()->user()->business_id;

        // Vérifier si le code existe déjà
        $exists = Operator::forBusiness($businessId)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'msg' => 'Ce code existe déjà']);
        }

        // Gérer l'upload du logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $request->code . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/operators'), $filename);
            $logoPath = 'uploads/operators/' . $filename;
        }

        // Obtenir le prochain display_order
        $maxOrder = Operator::forBusiness($businessId)->max('display_order');

        Operator::create([
            'business_id' => $businessId,
            'name' => $request->name,
            'code' => $request->code,
            'color' => '#3b82f6', // Couleur par défaut
            'icon' => $request->icon,
            'logo' => $logoPath,
            'description' => $request->description,
            'display_order' => $maxOrder + 1,
        ]);

        return response()->json(['success' => true, 'msg' => 'Opérateur créé avec succès']);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            return response()->json(['success' => false, 'msg' => 'Accès non autorisé']);
        }

        // Validation simple sans le logo
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $operator = Operator::forBusiness(auth()->user()->business_id)->findOrFail($id);

        $updateData = [
            'name' => $request->name,
            'icon' => $request->icon,
            'description' => $request->description,
            'display_order' => $request->display_order ?? $operator->display_order,
        ];

        // Gérer l'upload du logo si un fichier est fourni
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            // DEBUG
            \Log::info('Upload logo:', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension()
            ]);
            // Vérifier le type manuellement
            // Vérifier le type manuellement
            $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/svg', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false, 
                    'msg' => 'Le logo doit être un fichier PNG, JPG, JPEG ou SVG'
                ]);
            }
            
            // Vérifier la taille (max 2MB)
            if ($file->getSize() > 2048000) {
                return response()->json([
                    'success' => false, 
                    'msg' => 'Le logo ne doit pas dépasser 2 MB'
                ]);
            }
            
            // Supprimer l'ancien logo si c'est un fichier local
            if ($operator->logo && strpos($operator->logo, 'http') !== 0) {
                $oldPath = public_path($operator->logo);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            
            // Sauvegarder le nouveau logo
            $filename = time() . '_' . $operator->code . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/operators'), $filename);
            $updateData['logo'] = 'uploads/operators/' . $filename;
        }

        $operator->update($updateData);

        return response()->json(['success' => true, 'msg' => 'Opérateur modifié avec succès']);
    }

    public function get($id)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            return response()->json(['success' => false, 'msg' => 'Accès non autorisé']);
        }

        $operator = Operator::forBusiness(auth()->user()->business_id)->findOrFail($id);
        
        return response()->json(['success' => true, 'data' => $operator]);
    }
}
