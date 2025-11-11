<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receipt['receipt_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .receipt-info table {
            width: 100%;
        }
        .receipt-info td {
            padding: 5px 0;
        }
        .receipt-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .summary table {
            width: 100%;
        }
        .summary td {
            padding: 5px 0;
        }
        .summary td:first-child {
            text-align: left;
        }
        .summary td:last-child {
            text-align: right;
        }
        .summary .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            clear: both;
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
        }
        .payment-status.paid {
            background-color: #d4edda;
            color: #155724;
        }
        .payment-status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>RESTAURANT RECEIPT</h1>
            <p>Thank you for dining with us!</p>
        </div>

        <div class="receipt-info">
            <table>
                <tr>
                    <td>Receipt Number:</td>
                    <td>{{ $receipt['receipt_number'] }}</td>
                </tr>
                <tr>
                    <td>Order ID:</td>
                    <td>#{{ $receipt['order_id'] }}</td>
                </tr>
                <tr>
                    <td>Table:</td>
                    <td>{{ $receipt['table']['number'] }} (Capacity: {{ $receipt['table']['capacity'] }} persons)</td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td>{{ \Carbon\Carbon::parse($receipt['date'])->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <span class="payment-status {{ $receipt['payment_status'] }}">
                            {{ strtoupper($receipt['payment_status']) }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        @if($receipt['order_info']['notes'])
        <div style="margin-bottom: 20px; padding: 10px; background: #f9f9f9; border-left: 3px solid #333;">
            <strong>Notes:</strong> {{ $receipt['order_info']['notes'] }}
        </div>
        @endif

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Item</th>
                    <th class="text-center" style="width: 10%;">Type</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Price</th>
                    <th class="text-right" style="width: 20%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt['items'] as $item)
                <tr>
                    <td>
                        {{ $item['name'] }}
                        @if($item['special_instructions'])
                            <br><small style="color: #666;"><em>{{ $item['special_instructions'] }}</em></small>
                        @endif
                    </td>
                    <td class="text-center">{{ ucfirst($item['type']) }}</td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td class="text-right">Rp {{ $item['unit_price'] }}</td>
                    <td class="text-right">Rp {{ $item['subtotal'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>Rp {{ $receipt['summary']['subtotal'] }}</td>
                </tr>
                <tr>
                    <td>Tax ({{ $receipt['summary']['tax_rate'] }}):</td>
                    <td>Rp {{ $receipt['summary']['tax'] }}</td>
                </tr>
                <tr>
                    <td>Service Charge ({{ $receipt['summary']['service_charge_rate'] }}):</td>
                    <td>Rp {{ $receipt['summary']['service_charge'] }}</td>
                </tr>
                <tr class="grand-total">
                    <td>GRAND TOTAL:</td>
                    <td>Rp {{ $receipt['summary']['grand_total'] }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p><strong>Thank you for your visit!</strong></p>
            <p style="font-size: 10px; margin-top: 10px;">
                Opened: {{ \Carbon\Carbon::parse($receipt['order_info']['opened_at'])->format('d M Y, H:i') }}
                @if($receipt['order_info']['closed_at'])
                    | Closed: {{ \Carbon\Carbon::parse($receipt['order_info']['closed_at'])->format('d M Y, H:i') }}
                @endif
            </p>
        </div>
    </div>
</body>
</html>
