<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-details {
            float: right;
            text-align: right;
        }
        .client-details {
            float: left;
            text-align: left;
        }
        .clear {
            clear: both;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-amount {
            font-size: 1.2em;
            font-weight: bold;
            color: #E8793F;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 0.8em;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FACTURE</h1>
            <p>ServicePro</p>
        </div>

        <div class="invoice-info">
            <div class="client-details">
                <strong>Facturé à :</strong><br>
                {{ $client->utilisateur->prenom ?? '' }} {{ $client->utilisateur->nom ?? '' }}<br>
                {{ $client->utilisateur->email ?? '' }}<br>
                {{ $intervention->address ?? '' }}<br>
                {{ $intervention->ville ?? '' }}
            </div>
            <div class="invoice-details">
                <strong>Numéro de facture :</strong> {{ $invoice_number }}<br>
                <strong>Date :</strong> {{ $date }}<br>
                <strong>Intervenant :</strong> {{ $intervenant->utilisateur->prenom ?? '' }} {{ $intervenant->utilisateur->nom ?? '' }}
            </div>
        </div>
        <div class="clear"></div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantité/Durée</th>
                    <th>Prix Unit. (DH)</th>
                    <th>Total (DH)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Service / Task --}}
                <tr>
                    <td>
                        <strong>Service :</strong> {{ $service->nom_service ?? 'Service' }}<br>
                        <strong>Tâche :</strong> {{ $tache->nom_tache ?? 'Tâche' }}
                    </td>
                    <td>{{ $duration }} h</td> 
                    <td>{{ number_format($hourly_rate, 2) }}</td>
                    <td>{{ number_format($task_total, 2) }}</td>
                </tr>

                {{-- Materials --}}
                @foreach($materials_details as $material)
                <tr>
                    <td>
                        <strong>Matériel :</strong> {{ $material['name'] }}
                    </td>
                    <td>1</td> 
                    <td>{{ number_format($material['price'], 2) }}</td>
                    <td>{{ number_format($material['price'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <p><strong>Total à payer :</strong> <span class="total-amount">{{ number_format($total_ttc, 2) }} DH</span></p>
        </div>

        <div class="footer">
            <p>Merci pour votre confiance !</p>
            <p>ServicePro Inc.</p>
        </div>
    </div>
</body>
</html>
