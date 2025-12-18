<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de Vérification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
        }
        .content {
            padding: 30px 20px;
            text-align: center;
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px dashed #4CAF50;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #4CAF50;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .message {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin: 20px 0;
        }
        .warning {
            color: #ff6b6b;
            font-size: 14px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 12px;
            border-top: 1px solid #eee;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Service Pro</h1>
        </div>
        <div class="content">
            <h2>Vérification de votre adresse email</h2>
            <p class="message">
                Merci de vous être inscrit sur Service Pro ! Pour finaliser votre inscription, 
                veuillez utiliser le code de vérification ci-dessous :
            </p>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>
            <p class="message">
                Entrez ce code dans la page de vérification pour activer votre compte.
            </p>
            <p class="warning">
                ⚠️ Ce code expirera dans 15 minutes. Si vous n'avez pas demandé cette vérification, 
                veuillez ignorer cet email.
            </p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Service Pro. Tous droits réservés.</p>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
