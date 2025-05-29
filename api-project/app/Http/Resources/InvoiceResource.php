<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'total_amount' => $this->total_amount,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer?->name ?? '—', // ✅ Add this line
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
