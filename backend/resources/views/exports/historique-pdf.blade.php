<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Interventions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #2F4F4F;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            font-size: 18px;
            color: #2F4F4F;
            margin-bottom: 5px;
        }
        .header .date {
            font-size: 9px;
            color: #666;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
            font-size: 9px;
        }
        .filters strong {
            color: #2F4F4F;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead {
            background: linear-gradient(135deg, #E8F5E9 0%, #E3F2FD 100%);
        }
        th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border-bottom: 2px solid #ddd;
            color: #2F4F4F;
        }
        td {
            padding: 6px 5px;
            font-size: 9px;
            border-bottom: 1px solid #eee;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-jardinage {
            background-color: #E8F5E9;
            color: #2E7D32;
        }
        .badge-menage {
            background-color: #E3F2FD;
            color: #1565C0;
        }
        .badge-termine {
            background-color: #4CAF50;
            color: white;
        }
        .badge-acceptee {
            background-color: #2196F3;
            color: white;
        }
        .badge-refusee {
            background-color: #F44336;
            color: white;
        }
        .badge-en-attente {
            background-color: #FF9800;
            color: white;
        }
        .montant {
            color: #4CAF50;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Historique des Interventions</h1>
        <div class="date">Généré le {{ date('d/m/Y à H:i') }}</div>
    </div>

    @if($filters['dateDebut'] || $filters['dateFin'] || $filters['statut'] || $filters['service'])
    <div class="filters">
        <strong>Filtres appliqués :</strong>
        @if($filters['dateDebut'])
            Date début: {{ $filters['dateDebut'] }}
        @endif
        @if($filters['dateFin'])
            @if($filters['dateDebut']) | @endif
            Date fin: {{ $filters['dateFin'] }}
        @endif
        @if($filters['statut'])
            @if($filters['dateDebut'] || $filters['dateFin']) | @endif
            Statut: {{ $filters['statut'] }}
        @endif
        @if($filters['service'])
            @if($filters['dateDebut'] || $filters['dateFin'] || $filters['statut']) | @endif
            Service: {{ $filters['service'] }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Intervenant</th>
                <th>Service</th>
                <th>Tâche</th>
                <th>Date</th>
                <th>Durée</th>
                <th>Montant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($interventions as $intervention)
            <tr>
                <td>#{{ $intervention['id'] }}</td>
                <td>{{ $intervention['client'] }}</td>
                <td>{{ $intervention['intervenant'] }}</td>
                <td>
                    <span class="badge {{ $intervention['service'] === 'Jardinage' ? 'badge-jardinage' : 'badge-menage' }}">
                        {{ $intervention['service'] }}
                    </span>
                </td>
                <td>{{ $intervention['tache'] }}</td>
                <td>{{ $intervention['date'] }}</td>
                <td>{{ $intervention['duree'] }}</td>
                <td class="montant">{{ $intervention['montant'] }} DH</td>
                <td>
                    <span class="badge badge-{{ str_replace('é', 'e', str_replace('_', '-', strtolower($intervention['statut']))) }}">
                        {{ $intervention['statut'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                    Aucune intervention trouvée
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total: {{ count($interventions) }} intervention(s)</p>
        <p>Verde Net - Système de gestion des interventions</p>
    </div>
</body>
</html>
