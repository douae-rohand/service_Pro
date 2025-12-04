# Plateforme Web de Services Ménage & Jardinage

## Contexte Académique

- **Établissement** : UNIVERSITÉ ABDELMALEK ESSAÂDI - ÉCOLE NATIONALE DES SCIENCES APPLIQUÉES DE TÉTOUAN
- **Filière** : Génie Informatique - 2ème année Cycle Ingénieur
- **Module** : Développement Web Avancé
- **Année Universitaire** : 2025-2026
- **Encadrant** : Professeur Mohammed AL ACHHAB

## Description du Projet

Plateforme web complète permettant de mettre en relation des clients avec des professionnels (intervenants) pour des services à domicile de ménage et jardinage. Le système gère l'ensemble du cycle de vie d'une intervention, depuis la demande initiale jusqu'à la facturation et l'évaluation mutuelle.

## Objectifs du Projet

- Comprendre les problématiques des clients et trouver les solutions adéquates
- Comprendre des besoins métiers spécifiques
- Utiliser des outils pour la gestion de projet et de développement
- Respecter les contraintes en termes de temps
- Travailler en équipe avec un partage de responsabilité

## Fonctionnalités Principales

### Espace Client
- Recherche et consultation des services et intervenants
- Visualisation des intervenants sur carte interactive avec filtres
- Demande de service avec formulaire détaillé
- Suivi des demandes en temps réel
- Évaluation et notation des intervenants
- Système de commentaires
- Gestion de favoris
- Suivi des demandes en temps réel
- Consultation des factures
- Dépôt de réclamations

### Espace Intervenant
- Inscription avec vérification de documents (diplômes, certifications, assurances)
- Gestion des services et sous-services proposés
- Gestion des disponibilités (régulières et ponctuelles)
- Acceptation/refus des demandes
- Ajout de photos de réalisations
- Évaluation des clients
- Consultation de l'historique et statistiques
- Gestion du profil professionnel

### Espace Administrateur
- Gestion des utilisateurs (clients et intervenants)
- Validation des demandes d'inscription des intervenants
- Gestion du catalogue de services
- Consultation des statistiques du système
- Traitement des réclamations
- Suivi de l'historique des interventions
- Supervision globale du système

### Système d'Agent Automatisé
- Envoi automatique de formulaires d'évaluation
- Relances automatiques (J+3 et J+6)
- Gestion de la visibilité des évaluations mutuelles
- Notifications par email

## Architecture Technique

### Stack Technologique Recommandée

### Backend
- **Framework** : Laravel 12.x
- **Langage** : PHP 8.2+
- **Base de données** : MySQL 8.x
- **Cache & Queue** : Redis

#### Packages Laravel principaux
```bash
- Laravel Breeze (authentification)
- Laravel Mail (emails SMTP)
- Laravel Storage (gestion fichiers)
- Spatie Laravel Permission (rôles & permissions)
- Laravel Queue + Redis (jobs asynchrones)
- Laravel Broadcasting + Redis (temps réel)
```
### Frontend
- **Framework** : React 19
- **Routage** : React Router DOM
- **Formulaires** : React Hook Form
- **HTTP Client** : Axios
- **Cartographie** : Leaflet React (OpenStreetMap)
- **Storage** : LocalStorage API (cache local)

### Styling
- **CSS3 personnalisé** (approche desktop-first)
- Design responsive

### APIs Externes
- **Cartographie** : OpenStreetMap + Leaflet.js (gratuit, open-source)
- **Emails** : 
  - Développement : Mailtrap
  - Production : Amazon SES

### Patterns de Conception Utilisés
- **MVC (Model-View-Controller)** : Séparation logique métier / affichage / contrôle
- **Repository Pattern** : Abstraction de l'accès aux données
- **Singleton** : Pour les services transversaux


### Outils de développement
- **Gestion de code** : GitHub
- **Maquettage** : figma
- **Gestion de projet** : Jira
- **Communication** : Slack

## Sécurité
- Authentification JWT
- Hashage des mots de passe (bcrypt)
- Protection CSRF
- Validation des entrées
- Limitation du taux de requêtes
- Protection contre les injections SQL

## Maquettes UI/UX

Le projet dispose de plus de 30 interfaces couvrant :

### Interface Client (12 écrans)
- Page d'accueil
- Authentification (connexion/inscription)
- Catalogue des services et sous-services
- Recherche avec carte interactive
- Profil intervenant (détails, évaluations, réalisations, disponibilités)
- Gestion des demandes
- Liste des favoris
- Profil personnel

### Interface Intervenant (16 écrans)
- Inscription spécifique
- Choix et activation des services
- Gestion des sous-services
- Gestion des réservations
- Calendrier des disponibilités (régulières et ponctuelles)
- Évaluation des clients
- Consultation des avis
- Historique des interventions
- Profil professionnel

### Interface Administrateur (13 écrans)
- Tableau de bord avec statistiques
- Gestion des clients (liste, détails)
- Gestion des intervenants (liste, détails, documents, avis)
- Validation des demandes d'inscription
- Gestion du catalogue de services
- Statistiques par service
- Gestion des réclamations
- Historique global des interventions

## Contraintes et Règles Métier

### Contraintes Techniques
- Un intervenant doit se connecter avant de proposer un service
- Un intervenant peut proposer deux services simultanément maximum
- L'authentification est obligatoire pour effectuer une demande de service
- Le client peut consulter les profils sans authentification, mais doit se connecter pour demander un service

### Système d'Évaluation Mutuelle
1. **Déclenchement** : À la fin de chaque intervention terminée
2. **Envoi initial** : Formulaires d'évaluation envoyés aux deux parties
3. **Relances automatiques** : J+3 et J+6 si pas de réponse
4. **Visibilité** : Les évaluations restent invisibles (est_visible = FALSE) jusqu'à ce que :
   - Les deux parties aient évalué (publication simultanée)
   - Ou le délai de 7 jours soit dépassé (publication unilatérale possible)
5. **Contenu** : Note globale (1-5 étoiles) + commentaire optionnel + critères détaillés

### Communication
- Avant confirmation : l'agent assure la communication entre client et intervenant
- Après confirmation : informations du client transmises à l'intervenant (nom, téléphone, adresse)
- Notifications par email à chaque étape importante
---
