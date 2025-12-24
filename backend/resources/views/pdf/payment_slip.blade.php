<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Payement</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #E8793F; padding-bottom: 10px; }
        .header h1 { color: #E8793F; margin: 0; text-transform: uppercase; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #ddd; margin-bottom: 10px; color: #444; }
        .grid { display: flex; justify-content: space-between; }
        .col { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        .totals { margin-top: 30px; float: right; width: 300px; }
        .totals table { margin-top: 0; }
        .totals th { text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; padding: 10px 0; }
        .highlight { color: #E8793F; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fiche de Payement</h1>
        <p>Référence : {{ $slip_number }} | Date : {{ $date }}</p>
    </div>

    <div class="section">
        <div style="float: left; width: 50%;">
            <p class="section-title">INTERVENANT</p>
            <p>
                <strong>{{ $intervenant->utilisateur->nom }} {{ $intervenant->utilisateur->prenom }}</strong><br>
                {{ $intervenant->utilisateur->email }}
            </p>
        </div>
        <div style="float: right; width: 50%; text-align: right;">
            <p class="section-title">CLIENT</p>
            <p>
                <strong>{{ $client->utilisateur->nom }} {{ $client->utilisateur->prenom }}</strong><br>
                {{ $intervention->ville }}
            </p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="section">
        <p class="section-title">DÉTAILS DE L'INTERVENTION</p>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Tâche</th>
                    <th>Date</th>
                    <th>Durée</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $service->nom_service }}</td>
                    <td>{{ $tache->nom_tache }}</td>
                    <td>{{ $intervention->date_intervention->format('d/m/Y') }}</td>
                    <td>{{ $duration }} h</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <p class="section-title">DÉTAIL DES COÛTS (HT)</p>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Montant HT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Main d'œuvre ({{ $duration }}h x {{ number_format($hourly_rate, 2) }} MAD/h)</td>
                    <td style="text-align: right;">{{ number_format($ht_tache, 2) }} MAD</td>
                </tr>
                @if(count($materials_details) > 0)
                    @foreach($materials_details as $mat)
                    <tr>
                        <td>Matériel : {{ $mat['name'] }}</td>
                        <td style="text-align: right;">{{ number_format($mat['price'], 2) }} MAD</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="totals">
        <table>
            <tr>
                <th>Total Brut (HT)</th>
                <td style="text-align: right;">{{ number_format($ht_total, 2) }} MAD</td>
            </tr>
            <tr>
                <th>Commission Plateforme ({{ $tva_taux }}%)</th>
                <td style="text-align: right;">{{ number_format($tva_montant, 2) }} MAD</td>
            </tr>
            <tr style="font-size: 1.2em; font-weight: bold; background-color: #f9f9f9;">
                <th>Total Net à Percevoir</th>
                <td style="text-align: right;" class="highlight">{{ number_format($ttc, 2) }} MAD</td>
            </tr>
        </table>
        <p style="font-size: 0.8em; color: #777; margin-top: 10px; text-align: right; width: 100%;">
            * Cette déduction de {{ $tva_taux }}% correspond aux frais de service de la plateforme.
        </p>
    </div>

    <div class="footer">
        <p>Cette fiche de payement est générée automatiquement par la plateforme VERDE NET.</p>
    </div>
</body>
</html>
