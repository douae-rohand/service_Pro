<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #305C7D;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #305C7D;
            text-transform: uppercase;
        }
        .invoice-title {
            float: right;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .details-box {
            width: 100%;
            margin-bottom: 30px;
        }
        .left-col {
            width: 50%;
            float: left;
        }
        .right-col {
            width: 50%;
            float: right;
            text-align: right;
        }
        .client-info, .provider-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            font-weight: bold;
            color: #374151;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            width: 100%;
            text-align: right;
        }
        .total-row {
            font-size: 16px;
            font-weight: bold;
        }
        .final-total {
            font-size: 20px;
            color: #305C7D;
            margin-top: 10px;
            border-top: 2px solid #305C7D;
            padding-top: 10px;
            display: inline-block;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="company-name">Verde Net</span>
        <span class="invoice-title">FACTURE</span>
    </div>

    <div class="details-box">
        <div class="left-col">
            <strong>Facturé à :</strong><br>
            {{ $intervention->client->utilisateur->prenom }} {{ $intervention->client->utilisateur->nom }}<br>
            {{ $intervention->address }}<br>
            {{ $intervention->ville }}
        </div>
        <div class="right-col">
            <strong>Date :</strong> {{ \Carbon\Carbon::parse($intervention->date_intervention)->format('d/m/Y') }}<br>
            <strong>N° Facture :</strong> INV-{{ \Carbon\Carbon::parse($intervention->date_intervention)->format('Ymd') }}-{{ $intervention->id }}
        </div>
        <div class="clear"></div>
    </div>

    <div class="details-box">
        <div class="left-col">
            <strong>Prestataire :</strong><br>
            {{ $intervention->intervenant->utilisateur->prenom }} {{ $intervention->intervenant->utilisateur->nom }}<br>
            Service : {{ $intervention->tache->service->nom_service ?? 'N/A' }}
        </div>
        <div class="clear"></div>
    </div>

    @php
        $duration = $intervention->duration_hours ?? 1;
        $hourlyRate = 0;
        $intervenantTache = \App\Models\IntervenantTache::where('tache_id', $intervention->tache_id)
            ->where('intervenant_id', $intervention->intervenant_id)
            ->first();
        if ($intervenantTache) {
            $hourlyRate = $intervenantTache->prix_tache;
        }
        $taskTotal = $hourlyRate * $duration;
        
        $materialsCost = 0;
        $materialsCostInfo = $intervention->informations->where('nom', 'Coût_Matériel')->first();
        if ($materialsCostInfo) {
            $costString = $materialsCostInfo->pivot->information ?? '0';
            $materialsCost = (float) preg_replace('/[^0-9.]/', '', $costString);
        }
        
        $total = $taskTotal + $materialsCost;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Service de {{ $intervention->tache->service->nom_service ?? 'Service' }}</strong><br>
                    <small>Tâche : {{ $intervention->tache->nom_tache ?? 'Standard' }}</small><br>
                    <small>Durée estimée / réalisée : {{ $intervention->duration_hours }}h</small>
                </td>
                <td class="text-right">
                    {{ number_format($taskTotal, 2) }} DH
                </td>
            </tr>
            @if($intervention->informations && $intervention->informations->isNotEmpty())
                @foreach($intervention->informations as $info)
                    @if(str_contains($info->nom, 'Coût_Matériel'))
                        <tr>
                            <td>Frais de matériel</td>
                            <td class="text-right">{{ $info->pivot->information }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <!-- Assuming TTC is the final price stored in Facture model or we calculate it -->
        @php
            // Using calculated $total from above
        @endphp
        
        <div class="total-row">
            Sous-total : {{ number_format($total, 2) }} DH
        </div>
        <div class="total-row">
            TVA (0%) : 0.00 DH
        </div>
        <div class="final-total">
            Total : {{ number_format($total, 2) }} DH
        </div>
    </div>

    <div class="footer">
        Merci de votre confiance !<br>
        Verde Net - Plateforme de services à domicile - contact@verdenet.com
    </div>
</body>
</html>
