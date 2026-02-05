<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Business;
use App\BusinessLocation;

class PosOfflineController extends Controller
{
    public function index()
    {
        $business_id = auth()->user()->business_id;
        $business = Business::findOrFail($business_id);
        
        $locations = BusinessLocation::where('business_id', $business_id)
            ->pluck('name', 'id');
        
        return view('pos-offline.index', compact('business', 'locations'));
    }
}
