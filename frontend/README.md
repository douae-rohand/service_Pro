# ServicePro - Frontend Vue.js

Landing page pour ServicePro convertie de React vers Vue.js et intégrée avec Laravel.

## Installation

1. Installer les dépendances :
```bash
npm install
```

2. Lancer le serveur de développement :
```bash
npm run dev
```

Le frontend sera accessible sur `http://localhost:5173`

## Configuration

### Backend Laravel

Assurez-vous que le backend Laravel est lancé sur `http://127.0.0.1:8000`

Le fichier `vite.config.js` configure déjà un proxy pour les requêtes API :
- Les requêtes vers `/api` seront automatiquement redirigées vers `http://127.0.0.1:8000/api`

### Variables d'environnement

Vous pouvez créer un fichier `.env` dans le dossier `frontend` pour configurer l'URL de l'API :

```
VITE_API_URL=http://127.0.0.1:8000/api
```

## Structure du projet

```
frontend/
├── src/
│   ├── components/
│   │   ├── Header.vue          # En-tête avec navigation
│   │   ├── HeroSection.vue      # Section hero avec barre de recherche
│   │   ├── StatsSection.vue     # Section statistiques
│   │   ├── ServicesSection.vue  # Section services (Jardinage/Ménage)
│   │   ├── TestimonialsSection.vue # Section témoignages
│   │   ├── Footer.vue           # Pied de page
│   │   ├── LoginModal.vue       # Modal de connexion
│   │   └── SignupModal.vue      # Modal d'inscription
│   ├── services/
│   │   ├── api.js              # Configuration Axios
│   │   ├── authService.js      # Service d'authentification
│   │   ├── serviceService.js   # Service pour les services
│   │   └── interventionService.js # Service pour les interventions
│   ├── App.vue                 # Composant principal
│   ├── main.js                 # Point d'entrée
│   └── style.css               # Styles Tailwind CSS
├── index.html
├── package.json
├── vite.config.js
├── tailwind.config.js
└── postcss.config.js
```

## Fonctionnalités

- ✅ Landing page complète avec toutes les sections
- ✅ Modals de connexion et d'inscription
- ✅ Intégration avec l'API Laravel
- ✅ Design responsive
- ✅ Tailwind CSS pour le styling

## Intégration avec Laravel

Les services API sont déjà configurés dans `src/services/` :
- `authService.js` : Gestion de l'authentification (login, register, logout)
- `serviceService.js` : Gestion des services
- `interventionService.js` : Gestion des interventions

Les modals `LoginModal.vue` et `SignupModal.vue` utilisent déjà ces services pour communiquer avec le backend Laravel.

## Prochaines étapes

1. Implémenter les routes pour la navigation entre les pages
2. Ajouter la page de recherche de services
3. Ajouter la page de détail des services
4. Implémenter le système de réservation
5. Ajouter les images réelles au lieu des placeholders

## Build pour production

```bash
npm run build
```

Les fichiers seront générés dans le dossier `dist/`.
