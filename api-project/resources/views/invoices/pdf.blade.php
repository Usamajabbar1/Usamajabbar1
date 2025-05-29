<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: #333; }
        header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        h1 { margin: 0; }
        .company-info, .customer-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        table th { background: #f0f0f0; }
        .total { text-align: right; font-weight: bold; }
        .notes { margin-top: 30px; font-style: italic; }
    </style>
</head>
<body>
    <header>
        <h1>Invoice #{{ $invoice->invoice_number }}</h1>
        <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>
    </header>

    <section class="company-info">
        <h2>Company Information</h2>
        <div>{{ $company->name }}</div>
        <div>{{ $company->address }}</div>
        <div>{{ $company->phone }}</div>
        <div>{{ $company->email }}</div>
    </section>

    <section class="customer-info">
        <h2>Bill To</h2>
        <div>{{ $invoice->customer->name }}</div>
        <div>{{ $invoice->customer->address ?? 'Address not provided' }}</div>
        <div>{{ $invoice->customer->email }}</div>
    </section>

    <section class="invoice-items">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width: 80px;">Quantity</th>
                    <th style="width: 120px;">Unit Price</th>
                    <th style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                @endphp
                @foreach ($invoice->items as $item)
                    @php
                        $lineTotal = $item->quantity * $item->unit_price;
                        $total += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>${{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total">Total</td>
                    <td>${{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </section>

    @if ($invoice->notes)
        <section class="notes">
            <strong>Notes:</strong>
            <p>{{ $invoice->notes }}</p>
        </section>
    @endif

</body>
</html>
