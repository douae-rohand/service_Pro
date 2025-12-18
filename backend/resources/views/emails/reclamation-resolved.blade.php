<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réclamation résolue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .success-box {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .success-box strong {
            color: #2E7D32;
            font-size: 18px;
        }
        .message {
            background-color: white;
            padding: 20px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }
        .info-box {
            background-color: #E3F2FD;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info-box strong {
            color: #1A5FA3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Service Pro</h1>
    </div>
    
    <div class="content">
        <div class="success-box">
            <strong>✅ Votre réclamation a été résolue</strong>
        </div>

        <h2>Bonjour {{ $recipient->prenom }} {{ $recipient->nom }},</h2>
        
        <div class="info-box">
            <p><strong>Raison de la réclamation :</strong> {{ $raison }}</p>
            @if($concernantName)
            <p><strong>Concernant :</strong> {{ $concernantName }}</p>
            @endif
        </div>
        
        <p>Nous espérons que cette résolution répond à vos attentes. Si vous avez des questions supplémentaires ou des préoccupations, n'hésitez pas à nous contacter.</p>
        
        <p>Nous vous remercions de votre patience et de votre confiance en Service Pro.</p>
        
        <p>Cordialement,<br>
        <strong>L'équipe Service Pro</strong></p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
    </div>
</body>
</html>

