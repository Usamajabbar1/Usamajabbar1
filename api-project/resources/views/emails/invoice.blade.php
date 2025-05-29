@component('mail::message')
# Invoice #{{ $invoice->invoice_number }}

**Customer:** {{ $invoice->customer->name }}  
**Date:** {{ $invoice->invoice_date }}  
**Status:** {{ $invoice->status }}

---

## Items

@component('mail::table')
| Description       | Qty | Price | Total |
|-------------------|-----|--------|--------|
@foreach ($invoice->items as $item)
| {{ $item->description }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2) }} | {{ number_format($item->quantity * $item->unit_price, 2) }} |
@endforeach
@endcomponent

**Total Amount:** {{ number_format($invoice->total_amount, 2) }}

@component('mail::button', ['url' => config('app.url')])
View in App
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
