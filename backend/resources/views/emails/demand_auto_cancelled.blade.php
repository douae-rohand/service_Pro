<x-mail::message>
# Demande de service non traitée

Bonjour {{ $intervention->client->utilisateur->prenom }},

Votre demande de service pour **{{ $intervention->tache->nom }}** n'a pas reçu de réponse de la part de l'intervenant après 48 heures.

Conformément à notre politique, la demande a été automatiquement annulée pour vous permettre de solliciter un autre professionnel.

**Détails de la demande :**
- **Service :** {{ $intervention->tache->nom }}
- **Date de création :** {{ $intervention->created_at->format('d/m/Y H:i') }}
- **Statut :** Annulée (Automatique)

<x-mail::button :url="config('app.url')">
Retourner sur ServicePro
</x-mail::button>

Merci,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
