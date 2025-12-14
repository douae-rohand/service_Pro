# 📸 Guide : Modification de la Photo de Profil

## Comment ça fonctionne ?

### 1. **Frontend (Vue.js)**

#### Étape 1 : Sélection de l'image
- L'utilisateur clique sur l'icône crayon (✏️) sur la photo de profil
- Un input file caché s'ouvre pour sélectionner une image
- L'image est validée côté client :
  - Types acceptés : JPEG, PNG, JPG, GIF
  - Taille maximale : 2MB

#### Étape 2 : Prévisualisation
- Avant l'upload, l'image sélectionnée est affichée en prévisualisation
- Utilise `FileReader` pour créer une URL de données (data URL)

#### Étape 3 : Upload vers le serveur
- L'image est envoyée via `FormData` (multipart/form-data)
- Un indicateur de chargement s'affiche pendant l'upload
- L'API utilisée : `POST /api/auth/profile/avatar`

#### Étape 4 : Mise à jour de l'affichage
- Une fois l'upload réussi, la nouvelle URL est reçue du serveur
- La photo de profil est mise à jour automatiquement
- Un message de succès s'affiche

### 2. **Backend (Laravel)**

#### Étape 1 : Réception de la requête
- Route : `POST /api/auth/profile/avatar`
- Middleware : `auth:sanctum` (utilisateur authentifié requis)
- Controller : `AuthController@updateAvatar`

#### Étape 2 : Validation
- Vérifie que le fichier est une image valide
- Types acceptés : jpeg, png, jpg, gif
- Taille maximale : 2MB (2048 KB)

#### Étape 3 : Stockage
- Le fichier est stocké dans : `storage/app/public/avatars/`
- Laravel génère un nom de fichier unique automatiquement
- Exemple : `avatars/abc123def456.jpg`

#### Étape 4 : Mise à jour de la base de données
- L'URL complète est sauvegardée dans la colonne `url` de la table `utilisateur`
- Format de l'URL : `http://127.0.0.1:8000/storage/avatars/abc123def456.jpg`

#### Étape 5 : Réponse
- Retourne l'URL complète de l'image uploadée
- Le frontend utilise cette URL pour mettre à jour l'affichage

## Structure des fichiers

```
backend/
├── app/
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── Auth/
│                   └── AuthController.php  ← Méthode updateAvatar()
├── storage/
│   └── app/
│       └── public/
│           └── avatars/  ← Images stockées ici
│               └── [fichiers uploadés]
└── public/
    └── storage/  ← Lien symbolique vers storage/app/public

frontend/
├── src/
│   ├── components/
│   │   └── ClientProfile.vue  ← Interface utilisateur
│   └── services/
│       └── authService.js  ← Méthode updateAvatar()
```

## Configuration requise

### Backend
1. **Lien symbolique de stockage** :
   ```bash
   cd backend
   php artisan storage:link
   ```
   Cela crée un lien symbolique de `public/storage` vers `storage/app/public`

2. **Permissions** :
   Assurez-vous que le dossier `storage/app/public/avatars` est accessible en écriture :
   ```bash
   chmod -R 775 storage/app/public
   ```

### Frontend
Aucune configuration supplémentaire nécessaire. Le service API gère automatiquement :
- L'en-tête `Content-Type: multipart/form-data` pour les uploads
- L'ajout du token d'authentification dans les requêtes

## Flux de données

```
[Utilisateur]
    ↓ (clique sur ✏️)
[Input File] → Sélection image
    ↓
[FileReader] → Prévisualisation
    ↓
[FormData] → Création du formulaire
    ↓
[API Call] → POST /api/auth/profile/avatar
    ↓
[Laravel] → Validation + Stockage
    ↓
[Database] → Mise à jour utilisateur.url
    ↓
[Response] → URL de l'image
    ↓
[Frontend] → Mise à jour de l'affichage
```

## Sécurité

1. **Authentification** : Seul l'utilisateur connecté peut modifier sa propre photo
2. **Validation** : Types et tailles de fichiers sont validés
3. **Stockage sécurisé** : Les fichiers sont stockés dans `storage/app/public` (accessible via le lien symbolique)
4. **Noms uniques** : Laravel génère automatiquement des noms de fichiers uniques pour éviter les collisions

## Dépannage

### Problème : L'image ne s'affiche pas après l'upload
**Solution** : Vérifiez que le lien symbolique existe :
```bash
cd backend
php artisan storage:link
```

### Problème : Erreur 403 ou 500 lors de l'upload
**Solution** : Vérifiez les permissions du dossier storage :
```bash
chmod -R 775 storage/app/public
```

### Problème : L'image est trop grande
**Solution** : Le backend limite à 2MB. Réduisez la taille de l'image avant l'upload.

### Problème : Type de fichier non accepté
**Solution** : Utilisez uniquement JPEG, PNG, JPG ou GIF.

## Code clé

### Frontend - Sélection et upload
```javascript
handleAvatarChange(event) {
  const file = event.target.files[0];
  
  // Validation
  if (!validTypes.includes(file.type)) {
    alert('Type de fichier invalide');
    return;
  }
  
  // Prévisualisation
  const reader = new FileReader();
  reader.onload = (e) => {
    this.previewImage = e.target.result;
  };
  reader.readAsDataURL(file);
  
  // Upload
  await authService.updateAvatar(file);
}
```

### Backend - Stockage
```php
public function updateAvatar(Request $request) {
    // Validation
    $validated = $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    
    // Stockage
    $path = $request->file('avatar')->store('avatars', 'public');
    
    // URL complète
    $url = url('storage/' . $path);
    
    // Mise à jour BDD
    $user->update(['url' => $url]);
    
    return response()->json(['url' => $url]);
}
```

## Test

Pour tester la fonctionnalité :
1. Connectez-vous en tant que client
2. Allez dans "Mon Profil"
3. Cliquez sur l'icône ✏️ sur la photo de profil
4. Sélectionnez une image (JPEG, PNG, JPG ou GIF, max 2MB)
5. L'image devrait se mettre à jour automatiquement

