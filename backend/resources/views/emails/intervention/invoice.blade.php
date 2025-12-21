<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #305C7D; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #305C7D; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Facture Disponible</h2>
        </div>
        <div class="content">
            <p>Bonjour {{ $intervention->client->prenom }} {{ $intervention->client->nom }},</p>

            <p>Votre intervention prévue le <strong>{{ \Carbon\Carbon::parse($intervention->date_intervention)->format('d/m/Y') }}</strong> a été acceptée et confirmée.</p>

            <p>Vous pouvez dès maintenant consulter votre facture / devis dans votre espace client.</p>

            <div style="text-align: center;">
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/reservations" class="button">Voir ma facture</a>
            </div>

            <p>Détails de l'intervention :</p>
            <ul>
                <li><strong>Service :</strong> {{ $intervention->tache->service->nom_service ?? 'Service' }}</li>
                <li><strong>Intervenant :</strong> {{ $intervention->intervenant->utilisateur->prenom }} {{ $intervention->intervenant->utilisateur->nom }}</li>
                <li><strong>Statut :</strong> Confirmée</li>
            </ul>

            <p>Merci de votre confiance,</p>
            <p>L'équipe ServicePro</p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
