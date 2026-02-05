<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCatalogueOrderNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $business_id;

    public function __construct($order, $business_id)
    {
        $this->order = $order;
        $this->business_id = $business_id;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $message = 'Nouvelle commande reçue';

        if (!empty($this->order->table_id)) {
            $message = 'Nouvelle commande – Table ' . $this->order->table_id;
        } elseif (!empty($this->order->delivery_address)) {
            $message = 'Nouvelle commande – Livraison';
        }

        return [
            'business_id' => $this->business_id,
            'type'        => 'catalogue_order',
            'order_id'    => $this->order->id,
            'title'       => 'Nouvelle commande',
            'message'     => $message,
            'url'         => action(
                [\Modules\ProductCatalogue\Http\Controllers\CatalogueOrderController::class, 'show'],
                [$this->order->id]
            ),
        ];
    }
}
