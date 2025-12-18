<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réclamation vous concernant</title>
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
            background-color: #F44336;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .warning-box {
            background-color: #FFF3E0;
            border-left: 4px solid #FF9800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-box strong {
            color: #E65100;
            font-size: 16px;
        }
        .alert-box {
            background-color: #FFEBEE;
            border: 2px solid #F44336;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .alert-box strong {
            color: #C62828;
            font-size: 18px;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .message {
            background-color: white;
            padding: 20px;
            border-left: 4px solid #F44336;
            margin: 20px 0;
        }
        .info-box {
            background-color: #E3F2FD;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info-box strong {
            color: #1565C0;
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
        .action-required {
            background-color: #FFF9C4;
            border: 2px dashed #FBC02D;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
        .action-required strong {
            color: #F57F17;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Service Pro</h1>
    </div>
    
    <div class="content">
        <div class="alert-box">
            <strong>⚠️ ATTENTION : Une réclamation vous concerne</strong>
        </div>

        <h2>Bonjour {{ $recipient->prenom }} {{ $recipient->nom }},</h2>
        
        <div class="message">
            <p><strong>Une réclamation a été déposée à votre encontre.</strong></p>
            <p>Il est important que vous preniez connaissance de cette réclamation et que vous y répondiez de manière appropriée.</p>
        </div>

        <div class="info-box">
            <p><strong>Signalé par :</strong> {{ $signaleParName }}</p>
            <p><strong>Raison de la réclamation :</strong> {{ $raison }}</p>
        </div>

        <div class="action-required">
            <strong>Action requise :</strong>
            <p>Nous vous demandons de prendre cette réclamation au sérieux et de prendre les mesures nécessaires pour résoudre la situation.</p>
            <p>Votre réputation et votre relation avec la plateforme peuvent être affectées si cette réclamation n'est pas traitée correctement.</p>
        </div>
        
        @if(!empty($adminNotes))
        <div class="message">
            <p><strong>Notes de l'administration :</strong></p>
            <p>{{ $adminNotes }}</p>
        </div>
        @endif
        
        <p>Nous vous encourageons à prendre contact avec nous pour discuter de cette situation et trouver une solution appropriée.</p>
        
        <p>Cordialement,<br>
        <strong>L'équipe Service Pro</strong></p>
    </div>
    
    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre directement.</p>
        <p>Pour toute question, contactez notre service client.</p>
    </div>
</body>
</html>
