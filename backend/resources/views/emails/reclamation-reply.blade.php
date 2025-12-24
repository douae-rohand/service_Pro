<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réponse à votre réclamation</title>
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
            background-color: #1A5FA3;
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
        .message {
            background-color: white;
            padding: 20px;
            border-left: 4px solid #1A5FA3;
            margin: 20px 0;
        }
        .info-box {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info-box strong {
            color: #1A5FA3;
        }
        .priority-high {
            color: #F44336;
            font-weight: bold;
        }
        .priority-medium {
            color: #FF9800;
            font-weight: bold;
        }
        .priority-low {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-en_attente {
            background-color: #FFE5B4;
            color: #8B6914;
        }
        .status-en_cours {
            background-color: #BBDEFB;
            color: #1565C0;
        }
        .status-resolu {
            background-color: #C8E6C9;
            color: #2E7D32;
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
        <h1>Verde Net</h1>
    </div>
    
    <div class="content">
        <h2>Bonjour {{ $recipient->prenom }} {{ $recipient->nom }},</h2>
        
        <div class="message">
            <p><strong>Nous avons reçu et examiné votre réclamation.</strong></p>
        </div>
        
        <div class="info-box">
            <p><strong>Raison de la réclamation :</strong> {{ $raison }}</p>
        </div>
        
        @if(!empty($adminNotes))
        <div class="message">
            <p><strong>Notes de l'administration :</strong></p>
            <p>{{ $adminNotes }}</p>
        </div>
        @endif
        
        <p>Nous prenons votre réclamation très au sérieux et nous travaillons à résoudre cette situation.</p>
        
        <p>Si vous avez des questions supplémentaires, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        <strong>L'équipe Verde Net</strong></p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
    </div>
</body>
</html>
