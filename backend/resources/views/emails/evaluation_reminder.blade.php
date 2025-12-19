<!DOCTYPE html>
<html>
<head>
    <title>Évaluez votre intervention</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">Votre avis compte pour nous !</h2>
        
        <p>Bonjour {{ $intervention->client->utilisateur->prenom ?? 'Client' }},</p>
        
        <p>Votre intervention du <strong>{{ $intervention->date_intervention->format('d/m/Y') }}</strong> est terminée.</p>
        
        <p>Nous espérons que vous avez été satisfait du service. Merci de prendre un instant pour noter votre intervenant.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}" style="background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Évaluer mon intervention
            </a>
        </div>
        
        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>L'équipe Service Pro</p>
    </div>
</body>
</html>
