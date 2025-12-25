# Analyse Complète du Projet Service_Pro

## 📋 Vue d'ensemble

**Service_Pro** est une plateforme de mise en relation entre clients et intervenants pour des services à domicile (ménage, jardinage). Le projet utilise une architecture **Laravel (Backend)** et **Vue.js 3 (Frontend)**.

---

## 🏗️ Architecture Technique

### Backend (Laravel)
- **Framework**: Laravel (API REST)
- **Authentification**: Laravel Sanctum (tokens)
- **Base de données**: MySQL/MariaDB
- **Structure**: MVC avec API Controllers

### Frontend (Vue.js)
- **Framework**: Vue.js 3 (Composition API)
- **Routing**: Vue Router
- **HTTP Client**: Axios
- **Styling**: Tailwind CSS
- **Build Tool**: Vite

---

## 👥 Espaces Utilisateurs

### 1. **Espace Admin**
**Contrôleur**: `AdminController.php`
**Composants Frontend**: `components/Admin/`

#### Fonctionnalités:
- **Dashboard** avec statistiques:
  - Total clients/intervenants
  - Interventions du mois
  - Taux de satisfaction
  - Heures totales
  - Taux de croissance mensuel

- **Gestion Clients**:
  - Liste avec recherche/filtres
  - Détails (réservations, montant total, avis)
  - Activation/suspension
  - Feedback et historique

- **Gestion Intervenants**:
  - Liste avec filtres (statut, service)
  - Profil détaillé (services, tâches, matériels)
  - Gestion des justificatifs
  - Activation/suspension
  - Statistiques (missions, évaluations)

- **Gestion Services**:
  - CRUD services
  - Gestion des tâches par service
  - Activation/désactivation
  - Statistiques par service

- **Demandes d'Activation**:
  - Validation des demandes d'intervenants
  - Approbation/refus avec emails
  - Gestion des justificatifs

- **Réclamations**:
  - Liste des réclamations
  - Traitement (répondre, résoudre)
  - Notifications par email

- **Historique**:
  - Export CSV/PDF
  - Filtres par date, statut, type

#### Routes API (Préfixe: `/api/admin/`):
```php
GET    /admin/stats
GET    /admin/clients
GET    /admin/clients/{id}
POST   /admin/clients/{id}/toggle-status
GET    /admin/intervenants
GET    /admin/intervenants/{id}
POST   /admin/intervenants/{id}/toggle-status
GET    /admin/demandes
POST   /admin/demandes/{id}/approve
POST   /admin/demandes/{id}/reject
GET    /admin/services
POST   /admin/services
POST   /admin/services/{id}/toggle-status
GET    /admin/reclamations
POST   /admin/reclamations/{id}/handle
GET    /admin/historique
```

---

### 2. **Espace Client**
**Contrôleurs**: `ClientController.php`, `ClientProfileController.php`, `ClientReclamationController.php`, `FavorisController.php`
**Composants Frontend**: `components/client/`, `ClientHomePage.vue`, `ClientProfile.vue`, etc.

#### Fonctionnalités:
- **Page d'accueil client**:
  - Statistiques personnelles
  - Navigation vers réservations/favoris/profil

- **Réservations**:
  - Liste des interventions (pending, accepted, completed)
  - Détails par intervention
  - Annulation
  - Évaluation de l'intervenant
  - Factures

- **Favoris**:
  - Liste des intervenants favoris
  - Ajout/suppression
  - Navigation vers réservation

- **Profil**:
  - Modification des informations
  - Photo de profil
  - Statistiques (réservations, dépenses)

- **Réclamations**:
  - Création de réclamations
  - Suivi des réclamations
  - Historique

- **Recherche et Réservation**:
  - Recherche d'intervenants par service/tâche
  - Filtres (ville, note, disponibilité)
  - Création de demande d'intervention
  - Sélection de date/heure
  - Gestion des contraintes

#### Routes API:
```php
GET    /clients/{id}
PUT    /clients/{id}
GET    /clients/{id}/interventions
GET    /clients/{id}/favorites
POST   /clients/{id}/favorites
DELETE /clients/{id}/favorites/{intervenantId}
GET    /clients/{id}/profile/statistics
GET    /clients/{id}/profile/history
GET    /clients/me/reclamations
POST   /clients/me/reclamations
```

---

### 3. **Espace Intervenant**
**Contrôleur**: `IntervenantController.php`, `InterventionControllerIntervenant.php`
**Composants Frontend**: `components/intervenant/`, `IntervenantDashbord.vue`

#### Fonctionnalités:
- **Dashboard** avec onglets:
  - **Profil**: Informations personnelles, bio, photo
  - **Services**: Sélection et activation de services
  - **Mes Services**: Gestion des tâches (tarifs, matériels, statut)
  - **Réservations**: Demandes d'interventions
  - **Disponibilités**: Planning régulier et ponctuel
  - **Avis Clients**: Évaluation des clients
  - **Statistiques Avis**: Notes et distribution

- **Gestion Services**:
  - Demande d'activation avec justificatifs (carte d'identité, assurance)
  - Présentation et expérience
  - Statuts: `demmande`, `active`, `desactive`, `refuse`, `archive`

- **Gestion Tâches**:
  - Configuration tarif horaire
  - Matériels possédés avec prix
  - Activation/désactivation
  - Compteur de missions complétées

- **Réservations**:
  - Liste des demandes (pending, accepted, completed)
  - Acceptation/refus
  - Génération de factures (PDF)
  - Détails client (profil, historique, notes)

- **Disponibilités**:
  - Planning hebdomadaire (lundi-dimanche)
  - Disponibilités ponctuelles (exceptions)
  - Création/modification/suppression

- **Évaluations**:
  - Évaluation des clients (critères multiples)
  - Consultation des avis reçus
  - Statistiques de notation

#### Routes API:
```php
GET    /intervenants/me/taches
PUT    /intervenants/me/taches/{tacheId}
POST   /intervenants/me/taches/{tacheId}/toggle-active
DELETE /intervenants/me/taches/{tacheId}
GET    /intervenants/me/disponibilites
POST   /intervenants/me/disponibilites/regular
POST   /intervenants/me/disponibilites/special
DELETE /intervenants/me/disponibilites/{id}
GET    /intervenants/me/reservations
POST   /intervenants/me/reservations/{id}/accept
POST   /intervenants/me/reservations/{id}/refuse
POST   /intervenants/me/reservations/{id}/invoice
POST   /intervenants/{id}/services/{serviceId}/request-activation
POST   /intervenants/{id}/services/{serviceId}/toggle
POST   /intervenants/{id}/services/{serviceId}/status
POST   /intervenants/{id}/services/{serviceId}/materials
```

---

## 🗄️ Structure de la Base de Données

### Tables Principales

#### **utilisateur**
- ID (PK)
- nom, prenom, email, password
- telephone, url, profile_photo
- google_id, google_pw (OAuth)
- email_verification_code, email_verified_at
- created_at, updated_at

#### **client**
- id (PK, FK → utilisateur.id)
- address, ville
- is_active
- admin_id

#### **intervenant**
- id (PK, FK → utilisateur.id)
- address, ville, bio
- is_active
- admin_id

#### **service**
- id (PK)
- nom_service, description
- status (active/inactive)

#### **tache**
- id (PK)
- service_id (FK)
- nom_tache, description
- status (actif/inactif)
- image_url

#### **intervenant_service** (Pivot)
- intervenant_id, service_id (PK composite)
- status (active/desactive/demmande/refuse/archive)
- presentation, experience
- created_at, updated_at

#### **intervenant_tache** (Pivot)
- intervenant_id, tache_id (PK composite)
- prix_tache (tarif horaire)
- status (actif/inactif)
- created_at, updated_at

#### **intervention**
- id (PK)
- address, ville
- status (en attend/acceptee/refuse/termine)
- date_intervention
- duration_hours
- description
- client_id, intervenant_id, tache_id (FK)
- created_at, updated_at

#### **disponibilite**
- id (PK)
- intervenant_id (FK)
- type (reguliere/ponctuelle)
- jours_semaine (pour régulière)
- date_specific (pour ponctuelle)
- heure_debut, heure_fin
- created_at, updated_at

#### **evaluation**
- id (PK)
- intervention_id (FK)
- critaire_id (FK)
- note (1-5)
- type_auteur (client/intervenant)
- comment (optionnel)
- created_at, updated_at

#### **facture**
- id (PK)
- intervention_id (FK)
- fichier_path (PDF)
- ttc (montant total)
- created_at, updated_at

#### **reclamation**
- id (PK)
- intervention_id (FK)
- sujet, description
- statut (en_attente/traitee/resolue)
- signale_par (polymorphique)
- concernant (polymorphique)
- created_at, updated_at

#### **favorise** (Pivot)
- client_id, intervenant_id (PK composite)
- created_at, updated_at

#### **materiel**
- id (PK)
- service_id (FK)
- nom_materiel, description

#### **intervenant_materiel** (Pivot)
- intervenant_id, materiel_id (PK composite)
- prix_materiel
- created_at, updated_at

---

## 🔐 Authentification

### Système d'Auth
- **Laravel Sanctum** pour les tokens API
- **OAuth Google** (optionnel)
- **Vérification email** avec code à 6 chiffres
- **Reset password** avec code

### Routes Auth:
```php
POST   /auth/register
POST   /auth/login
POST   /auth/logout
GET    /auth/user
PUT    /auth/profile
POST   /auth/profile/avatar
POST   /auth/change-password
POST   /auth/forgot-password
POST   /auth/verify-code
POST   /auth/reset-password
POST   /auth/verify-email
POST   /auth/resend-verification
GET    /auth/google/redirect
GET    /auth/google/callback
```

### Middleware
- `auth:sanctum`: Protection des routes authentifiées
- `admin`: Vérification du rôle admin (dans `EnsureAdmin.php`)

---

## 🔄 Flux Principaux

### 1. **Création d'une Intervention (Client)**
```
Client → Recherche Intervenant → Sélection Tâche → 
Sélection Date/Heure → Remplissage Formulaire → 
Création Intervention (status: "en attend") → 
Notification Intervenant → Intervenant Accepte/Refuse
```

### 2. **Activation d'un Service (Intervenant)**
```
Intervenant → Sélection Service → Upload Justificatifs → 
Demande (status: "demmande") → Admin Valide → 
Status: "active" → Email de confirmation
```

### 3. **Évaluation Mutuelle**
```
Intervention Terminée → Client Évalue Intervenant → 
Intervenant Évalue Client → 
Publication Publique (si les 2 ont noté OU 7 jours passés)
```

### 4. **Génération Facture**
```
Intervention Acceptée → Intervenant Génère Facture (PDF) → 
Calcul: (duration_hours × prix_tache) + matériels → 
Stockage PDF → Notification Client
```

---

## 📧 Système de Notifications

### Emails (Laravel Mail)
- `InterventionAccepted`: Confirmation d'acceptation
- `InterventionInvoiceMail`: Envoi de facture
- `ServiceRequestApproved`: Service activé
- `ServiceRequestRejected`: Service refusé
- `ReclamationReply`: Réponse à réclamation
- `ReclamationResolved`: Réclamation résolue
- `VerificationCode`: Code de vérification email
- `ResetPasswordCode`: Code de reset password

### Notifications DB
- Stockées dans table `notifications`
- Type: `InterventionAcceptedNotification`
- Affichage dans barre de notifications (frontend)

---

## 🎨 Frontend - Structure

### Composants Principaux

#### **Admin**
- `AdminDashboard.vue`: Dashboard principal
- `AdminClients.vue`: Liste clients
- `AdminIntervenants.vue`: Liste intervenants
- `AdminServices.vue`: Gestion services
- `AdminDemandes.vue`: Demandes d'activation
- `AdminReclamations.vue`: Réclamations
- `AdminHistorique.vue`: Historique
- `AdminIntervenantProfile.vue`: Profil détaillé intervenant

#### **Client**
- `ClientHomePage.vue`: Accueil client
- `ClientProfile.vue`: Profil
- `ClientReservationsPage.vue`: Réservations
- `MyFavoritesTab.vue`: Favoris
- `ClientReclamationsTab.vue`: Réclamations
- `BookingPage.vue`: Page de réservation

#### **Intervenant**
- `IntervenantDashbord.vue`: Dashboard avec onglets
- `ProfileTab.vue`: Profil
- `ServiceSelectionTab.vue`: Sélection services
- `MyServicesTab.vue`: Gestion tâches
- `ReservationsTab.vue`: Réservations
- `AvailabilityTab.vue`: Disponibilités
- `ClientReviewsTab.vue`: Avis clients
- `ReviewsStatsTab.vue`: Statistiques avis

#### **Commun**
- `Header.vue`: En-tête avec navigation
- `Footer.vue`: Pied de page
- `LoginModal.vue`: Modal connexion
- `SignupModal.vue`: Modal inscription
- `BookingModal.vue`: Modal réservation
- `RateIntervenantModal.vue`: Modal évaluation

### Services Frontend (`src/services/`)
- `api.js`: Configuration Axios
- `authService.js`: Authentification
- `interventionService.js`: Gestion interventions
- `intervenantService.js`: Gestion intervenants
- `clientService.js`: Gestion clients
- `serviceService.js`: Gestion services
- `evaluationService.js`: Évaluations
- `favoriteService.js`: Favoris
- `adminService.js`: API admin
- `bookingService.js`: Réservations
- `availabilityService.js`: Disponibilités

---

## 🔍 Points Techniques Importants

### 1. **Validation des Données Personnelles**
- Utilisation de `InputValidator` pour détecter les informations sensibles
- Validation des contraintes, adresses, descriptions
- Protection contre l'injection de données personnelles

### 2. **Gestion des Évaluations Publiques/Privées**
- Règle: Publique si **les 2 parties ont noté** OU **7 jours passés**
- Méthode `areRatingsPublic()` dans modèle `Intervention`
- Filtrage côté backend et frontend

### 3. **Optimisation des Requêtes**
- Eager loading avec `with()`
- Préchargement des données (éviter N+1)
- Cache pour les statistiques admin (60 secondes)
- Pagination standardisée

### 4. **Génération PDF**
- Service `PDFService` pour factures
- Stockage dans `storage/app/public`
- Génération à la demande

### 5. **Gestion des Statuts**
- **Intervention**: `en attend`, `acceptee`, `refuse`, `termine`
- **Service Intervenant**: `demmande`, `active`, `desactive`, `refuse`, `archive`
- **Tâche Intervenant**: `active`/`inactive` (boolean dans pivot)
- **Client/Intervenant**: `is_active` (boolean)

---

## 📁 Structure des Dossiers

### Backend
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── Admin/
│   │   │       ├── Client/
│   │   │       ├── Intervenant/
│   │   │       ├── Intervention/
│   │   │       └── Auth/
│   │   ├── Middleware/
│   │   └── Resources/
│   ├── Models/
│   ├── Mail/
│   ├── Services/
│   └── Utils/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
└── storage/
```

### Frontend
```
frontend/
├── src/
│   ├── components/
│   │   ├── Admin/
│   │   ├── client/
│   │   ├── intervenant/
│   │   └── [commun]
│   ├── services/
│   ├── router/
│   ├── composables/
│   ├── utils/
│   └── views/
└── public/
```

---

## 🚀 Fonctionnalités Avancées

### 1. **Recherche Avancée d'Intervenants**
- Filtres: service, tâche, ville, statut
- Recherche par nom
- Tri par note, disponibilité
- Pagination

### 2. **Gestion des Matériels**
- Matériels par service
- Possession par intervenant avec prix
- Utilisation dans interventions
- Distinction matériel client vs intervenant

### 3. **Planning de Disponibilités**
- Planning hebdomadaire récurrent
- Exceptions ponctuelles
- Vérification lors de la réservation

### 4. **Système de Favoris**
- Ajout/suppression
- Liste avec statistiques
- Navigation rapide vers réservation

### 5. **Export de Données**
- CSV/PDF pour historique admin
- Factures PDF
- Statistiques exportables

---

## 🔒 Sécurité

- **Sanctum** pour authentification API
- **Validation** stricte des inputs
- **Protection CSRF** (Laravel)
- **Hashage** des mots de passe (bcrypt)
- **Middleware** de vérification de rôle
- **Validation** des données personnelles
- **CORS** configuré

---

## 📊 Statistiques et Analytics

### Admin Dashboard
- Total clients/intervenants
- Interventions du mois
- Taux de satisfaction (moyenne notes)
- Heures totales
- Taux de croissance mensuel

### Client
- Nombre de réservations par statut
- Montant total dépensé
- Dernière intervention
- Nombre d'avis donnés

### Intervenant
- Missions complétées
- Revenus totaux
- Note moyenne reçue
- Distribution des notes
- Taux de complétion

---

## 🐛 Points d'Attention

1. **Statuts multiples**: Gestion de différentes variantes (`termine`/`terminee`/`completed`)
2. **Relations complexes**: Nombreuses relations many-to-many avec pivots
3. **Validation email**: Système avec codes à 6 chiffres
4. **Gestion fichiers**: Upload de justificatifs, photos, avatars
5. **Notifications**: Système hybride (email + DB)

---

## 📝 Notes de Développement

- **Backend**: Laravel avec API RESTful
- **Frontend**: Vue.js 3 avec Composition API
- **Base de données**: Structure relationnelle complexe
- **Authentification**: Multi-rôles (Admin, Client, Intervenant)
- **Architecture**: Séparation claire backend/frontend
- **Scalabilité**: Optimisations requêtes, cache, pagination

---

## 🎯 Conclusion

Le projet **Service_Pro** est une plateforme complète de mise en relation avec:
- ✅ Gestion multi-rôles (Admin, Client, Intervenant)
- ✅ Système de réservations complet
- ✅ Évaluations mutuelles
- ✅ Gestion des services et tâches
- ✅ Planning de disponibilités
- ✅ Génération de factures
- ✅ Système de réclamations
- ✅ Notifications et emails
- ✅ Interface admin complète

L'architecture est bien structurée avec une séparation claire des responsabilités et des optimisations pour les performances.

