<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number ?? $invoice->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 6px 0;
        }
        .muted {
            color: #6b7280;
        }
        .box {
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 6px;
            text-align: left;
        }
        th {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .right {
            text-align: right;
        }
        .totals {
            margin-top: 16px;
            width: 40%;
            margin-left: auto;
        }
        .totals td {
            border: none;
            padding: 4px 0;
        }
        .total-line {
            font-weight: bold;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Facture #{{ $invoice->number ?? $invoice->id }}</h1>
            <div class="muted">
                Periode: {{ optional($invoice->period_start)->format('d/m/Y') ?? '-' }}
                -
                {{ optional($invoice->period_end)->format('d/m/Y') ?? '-' }}
            </div>
            <div class="muted">
                Statut: {{ $invoice->status }}
            </div>
        </div>
        <div class="box">
            <div>Parent: {{ $parentName }}</div>
            <div>Babysitter: {{ $babysitterName }}</div>
            <div>Devise: {{ $invoice->currency }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Date</th>
                <th class="right">Quantite</th>
                <th class="right">Prix unitaire</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>
                        @if($item->service_date)
                            {{ \Illuminate\Support\Carbon::parse($item->service_date)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="right">{{ number_format((float) ($item->quantity ?? 0), 2) }}</td>
                    <td class="right">{{ number_format((float) ($item->unit_price ?? 0), 2) }}</td>
                    <td class="right">{{ number_format((float) ($item->total_amount ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tbody>
            <tr>
                <td class="right muted">Sous-total</td>
                <td class="right">{{ number_format((float) ($invoice->subtotal_amount ?? 0), 2) }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td class="right muted">TVA ({{ number_format((float) $vatPercent, 2) }}%)</td>
                <td class="right">{{ number_format((float) ($invoice->tax_amount ?? 0), 2) }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td class="right total-line">Total</td>
                <td class="right total-line">{{ number_format((float) ($invoice->total_amount ?? 0), 2) }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
