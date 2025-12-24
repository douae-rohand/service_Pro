<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rappel d'évaluation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Rappel : Évaluez votre client</h2>
        
        <p>Bonjour {{ $intervention->intervenant->utilisateur->prenom }},</p>
        
        <p>Votre intervention avec <strong>{{ $intervention->client->utilisateur->prenom }} {{ $intervention->client->utilisateur->nom }}</strong> pour le service <strong>{{ $intervention->tache->nom }}</strong> est terminée.</p>
        
        <p>Nous vous invitons à partager votre expérience en évaluant le client. Votre avis contribue à maintenir une communauté de qualité !</p>
        
        <div style="margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/interventions/{{ $intervention->id }}/evaluate" 
               style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Évaluer maintenant
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            <strong>Note :</strong> Vous avez jusqu'au {{ $intervention->updated_at->addDays(7)->format('d/m/Y') }} pour évaluer. Après cette date, les évaluations seront rendues publiques automatiquement.
        </p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px;">
            Cet email est un rappel automatique. Si vous avez déjà évalué, veuillez ignorer ce message.
        </p>
    </div>
</body>
</html>
