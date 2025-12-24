<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Service activé</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%); padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: 0.5px;">Verde Net</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 22px; font-weight: 500;">
                                Bonjour {{ $intervenant->utilisateur->prenom ?? $intervenant->prenom ?? '' }} {{ $intervenant->utilisateur->nom ?? $intervenant->nom ?? '' }},
                            </h2>
                            
                            <!-- Status Message -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 25px 0;">
                                <tr>
                                    <td style="background-color: #e8f5e9; border-left: 4px solid #43a047; padding: 20px; border-radius: 4px;">
                                        <p style="margin: 0; color: #2e7d32; font-size: 16px; font-weight: 600; line-height: 1.5;">
                                            Service activé
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Main Message -->
                            <p style="margin: 25px 0; color: #555555; font-size: 15px; line-height: 1.7;">
                                Nous avons le plaisir de vous informer que le service "<strong>{{ $service->nom_service ?? 'Service' }}</strong>" a été réactivé sur notre plateforme.
                            </p>
                            
                            <p style="margin: 20px 0; color: #555555; font-size: 15px; line-height: 1.7;">
                                Le service est maintenant actif et visible pour les clients. Vous pouvez à nouveau recevoir des demandes pour ce service.
                            </p>
                            
                            <p style="margin: 20px 0; color: #555555; font-size: 15px; line-height: 1.7;">
                                Nous vous remercions de votre patience et restons à votre disposition pour toute question.
                            </p>
                            
                            <!-- Signature -->
                            <p style="margin: 30px 0 0 0; color: #333333; font-size: 15px; line-height: 1.7;">
                                Cordialement,<br>
                                <strong style="color: #43a047;">L'équipe Verde Net</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9f9f9; padding: 25px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="margin: 0 0 10px 0; color: #999999; font-size: 12px; line-height: 1.6;">
                                Cet email a été envoyé automatiquement. Merci de ne pas y répondre directement.
                            </p>
                            <p style="margin: 0; color: #999999; font-size: 12px; line-height: 1.6;">
                                © {{ date('Y') }} Verde Net. Tous droits réservés.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
