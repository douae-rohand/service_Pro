<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'intervention</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #E8793F 0%, #D16834 100%); padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Verde Net</h1>
                            <p style="margin: 5px 0 0 0; color: #ffffff; font-size: 16px;">Confirmation d'intervention</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 22px;">
                                Bonjour {{ $intervenant->prenom }},
                            </h2>
                            <p style="margin: 0 0 25px 0; color: #555555; font-size: 15px; line-height: 1.7;">
                                Vous avez accepté une nouvelle intervention. Voici les informations détaillées pour vous rendre sur place et effectuer la mission :
                            </p>

                            <!-- Client Info Table -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 25px 0; border: 1px solid #eeeeee; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 15px; background-color: #f9f9f9; width: 40%; font-weight: bold; color: #555555;">Client</td>
                                    <td style="padding: 15px; color: #333333;">{{ $client->nom }} {{ $client->prenom }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; background-color: #ffffff; width: 40%; font-weight: bold; color: #555555;">Téléphone</td>
                                    <td style="padding: 15px; color: #333333;">{{ $client->telephone }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; background-color: #f9f9f9; width: 40%; font-weight: bold; color: #555555;">Email</td>
                                    <td style="padding: 15px; color: #333333;">{{ $client->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px; background-color: #ffffff; width: 40%; font-weight: bold; color: #555555;">Adresse exacte</td>
                                    <td style="padding: 15px; color: #E8793F; font-weight: bold;">{{ $intervention->address }}, {{ $intervention->ville }}</td>
                                </tr>
                            </table>

                            <!-- Mission Info -->
                            <h3 style="color: #E8793F; font-size: 18px; margin: 30px 0 15px 0; border-bottom: 2px solid #f4f4f4; padding-bottom: 5px;">Détails de la mission</h3>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 10px;">
                                        <strong>Service :</strong> {{ $intervention->tache->service->nom_service }}<br>
                                        <strong>Tâche :</strong> {{ $intervention->tache->nom_tache }}<br>
                                        <strong>Date :</strong> {{ \Carbon\Carbon::parse($intervention->date_intervention)->locale('fr')->isoFormat('LL') }}<br>
                                        <strong>Horaire :</strong> {{ \Carbon\Carbon::parse($intervention->date_intervention)->format('H:i') }}
                                    </td>
                                </tr>
                            </table>

                            @if($intervention->message)
                            <div style="background-color: #fff7ed; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #ffedd5;">
                                <strong style="color: #9a3412; display: block; margin-bottom: 5px;">Message du client :</strong>
                                <p style="margin: 0; font-style: italic; color: #555555;">"{{ $intervention->message }}"</p>
                            </div>
                            @endif

                            <p style="margin: 30px 0 0 0; color: #333333; font-size: 15px; line-height: 1.7;">
                                Bonne chance pour votre intervention !<br>
                                <strong style="color: #E8793F;">L'équipe Verde Net</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9f9f9; padding: 25px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="margin: 0; color: #999999; font-size: 12px;">© {{ date('Y') }} Verde Net. Tous droits réservés.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
