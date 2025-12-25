# Gestion des Services d'Intervenant par l'Admin

## 📋 Vue d'ensemble

Cette fonctionnalité permet à l'administrateur de gérer les services des intervenants directement depuis le profil de l'intervenant dans l'espace admin. L'admin peut activer/désactiver les services d'un intervenant via un toggle switch.

---

## 🎯 Fonctionnalités

### 1. Activation/Désactivation de Services

L'admin peut activer ou désactiver un service pour un intervenant depuis la page de profil détaillé de l'intervenant.

- **Toggle Switch** : Permet de basculer entre actif/désactivé
- **Couleur du toggle** :
  - 🟢 **Vert** : Service actif ou archivé
  - ⚪ **Gris** : Service désactivé
- **Préservation des données** : La présentation et l'expérience sont conservées lors de la désactivation

### 2. Gestion des Statuts

Les différents statuts des services sont gérés de manière différente :

| Statut | Affichage | Modifiable par Admin | Description |
|--------|-----------|---------------------|-------------|
| `active` | ✅ Affiché | ✅ Oui | Service actif, toggle vert |
| `desactive` | ✅ Affiché | ✅ Oui | Service désactivé, toggle gris |
| `archive` | ✅ Affiché | ❌ Non | Service archivé par l'intervenant, toggle vert désactivé avec message |
| `refuse` | ❌ Masqué | ❌ Non | Service refusé par l'admin, non affiché |
| `demmande` | ❌ Masqué | ❌ Non | Service en attente, géré dans la page Demandes |

---

## 🔧 Modifications Backend

### Fichier : `backend/app/Http/Controllers/Api/Admin/AdminController.php`

#### 1. Méthode `getIntervenantDetails()`

**Modifications principales :**

```php
// Filtrer les services pour exclure 'refuse' et 'demmande'
$tousServices = $intervenant->services->filter(function($service) {
    $pivotStatus = $service->pivot->status ?? null;
    // Exclure les services refusés et en demande
    if ($pivotStatus === 'refuse' || $pivotStatus === 'demmande') {
        return false;
    }
    // Inclure seulement active, desactive, archive
    return in_array($pivotStatus, ['active', 'desactive', 'archive']);
});

// Construire allServicesWithDetailsAll avec TOUS les services (active, desactive, archive uniquement)
$allServicesWithDetails = $tousServices->map(function($service) {
    $status = $service->pivot->status ?? null;
    // S'assurer que le statut est une string (et non null)
    $status = $status ? (string)$status : null;
    
    return [
        'nom_service' => $service->nom_service,
        'id' => $service->id,
        'experience' => $this->formatExperience($service->pivot->experience ?? null),
        'presentation' => $service->pivot->presentation ?? null,
        'status' => $status // Statut: active, desactive, ou archive (toujours string)
    ];
})->toArray();
```

**Champs retournés :**

- `allServices` : Array de strings (noms des services) - TOUS les services (active, desactive, archive) pour navigation
- `allServicesWithDetails` : Array d'objets - Services ACTIFS uniquement (pour affichage principal)
- `allServicesWithDetailsAll` : Array d'objets - TOUS les services avec détails complets (nom, id, experience, presentation, status)

#### 2. Nouvelle méthode `toggleIntervenantServiceStatus()`

**Route :** `POST /api/admin/intervenants/{intervenantId}/services/{serviceId}/toggle-status`

**Fonctionnalité :**

```php
public function toggleIntervenantServiceStatus($intervenantId, $serviceId)
{
    // Vérifier que la relation existe
    $existing = DB::table('intervenant_service')
        ->where('intervenant_id', $intervenantId)
        ->where('service_id', $serviceId)
        ->first();

    if (!$existing) {
        return response()->json([
            'error' => 'Relation intervenant-service non trouvée'
        ], 404);
    }

    // Toggle le statut (active <-> desactive)
    // Ne pas toucher aux statuts 'archive', 'refuse', 'demmande'
    if (!in_array($existing->status, ['active', 'desactive'])) {
        return response()->json([
            'error' => 'Impossible de modifier le statut de ce service. Statut actuel: ' . $existing->status
        ], 400);
    }
    
    $newStatus = ($existing->status === 'active') ? 'desactive' : 'active';

    // Mettre à jour le statut SANS supprimer présentation et expérience
    DB::table('intervenant_service')
        ->where('intervenant_id', $intervenantId)
        ->where('service_id', $serviceId)
        ->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

    return response()->json([
        'message' => $newStatus === 'active' ? 'Service activé pour cet intervenant' : 'Service désactivé pour cet intervenant',
        'status' => $newStatus,
        'isActive' => $newStatus === 'active'
    ]);
}
```

**Règles :**

- ✅ Permet de toggle uniquement entre `'active'` et `'desactive'`
- ❌ Bloque la modification des services avec statut `'archive'`, `'refuse'`, ou `'demmande'`
- ✅ Préserve la présentation et l'expérience lors de la désactivation

---

## 🎨 Modifications Frontend

### Fichier : `frontend/src/components/Admin/AdminIntervenantProfile.vue`

#### 1. Toggle Switch dans la section "Tâches/Service"

**Position :** À droite dans la section qui affiche :
- Service : [Nom du service]
- [Nombre] tâche(s) proposée(s) pour ce service
- Expérience : [Expérience]

**Code du toggle :**

```vue
<!-- Toggle Switch pour activer/désactiver le service -->
<div class="flex flex-col items-end gap-1">
  <label class="relative inline-flex items-center cursor-pointer">
    <input
      type="checkbox"
      :checked="isCurrentServiceActive"
      @change="toggleServiceStatus"
      :disabled="isTogglingService || isCurrentServiceArchived"
      class="sr-only peer"
    />
    <div 
      class="w-11 h-6 rounded-full transition-colors duration-200 peer-focus:outline-none"
      :style="{
        backgroundColor: isCurrentServiceActive ? '#4CAF50' : '#9CA3AF',
        opacity: isCurrentServiceArchived ? 0.7 : 1
      }"
    >
      <div 
        class="w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform duration-200 mt-0.5 ml-0.5"
        :style="{
          transform: isCurrentServiceActive ? 'translateX(20px)' : 'translateX(0px)'
        }"
      ></div>
    </div>
  </label>
  <span v-if="isCurrentServiceArchived" class="text-xs text-gray-500 italic text-right">
    (Archivé)
  </span>
</div>
```

#### 2. Computed Properties

**`currentServiceStatus` :** Retourne le statut actuel du service

```javascript
const currentServiceStatus = computed(() => {
  if (!intervenantData.value || !intervenantData.value.allServicesWithDetailsAll) {
    return null
  }
  
  const index = currentServiceIndex.value
  if (index < 0 || index >= intervenantData.value.allServicesWithDetailsAll.length) {
    return null
  }
  
  const serviceDetails = intervenantData.value.allServicesWithDetailsAll[index]
  return serviceDetails?.status || null
})
```

**`isCurrentServiceActive` :** Détermine si le toggle doit être vert

```javascript
const isCurrentServiceActive = computed(() => {
  const status = currentServiceStatus.value
  // Le toggle est vert si le service est 'active' ou 'archive'
  return status === 'active' || status === 'archive'
})
```

**`isCurrentServiceArchived` :** Vérifie si le service est archivé

```javascript
const isCurrentServiceArchived = computed(() => {
  return currentServiceStatus.value === 'archive'
})
```

**`getCurrentServiceId` :** Récupère l'ID du service actuel

```javascript
const getCurrentServiceId = () => {
  if (!intervenantData.value || !intervenantData.value.allServicesWithDetailsAll) {
    return null
  }
  
  const index = currentServiceIndex.value
  if (index < 0 || index >= intervenantData.value.allServicesWithDetailsAll.length) {
    return null
  }
  
  const serviceDetails = intervenantData.value.allServicesWithDetailsAll[index]
  return serviceDetails?.id || null
}
```

#### 3. Fonction `toggleServiceStatus()`

```javascript
const toggleServiceStatus = async () => {
  if (!intervenantData.value || !intervenantData.value.id) {
    showError('Erreur: données de l\'intervenant non disponibles')
    return
  }
  
  // Ne pas permettre le toggle si le service est archivé
  if (isCurrentServiceArchived.value) {
    info('Ce service est archivé par l\'intervenant. L\'admin ne peut pas le modifier.')
    return
  }
  
  const serviceId = getCurrentServiceId()
  if (!serviceId) {
    showError('Erreur: impossible de récupérer l\'ID du service')
    return
  }
  
  try {
    isTogglingService.value = true
    
    const response = await adminService.toggleIntervenantServiceStatus(
      intervenantData.value.id,
      serviceId
    )
    
    // Mettre à jour le statut localement
    if (intervenantData.value.allServicesWithDetailsAll) {
      const index = currentServiceIndex.value
      if (index >= 0 && index < intervenantData.value.allServicesWithDetailsAll.length) {
        intervenantData.value.allServicesWithDetailsAll[index].status = response.data.status
      }
    }
    
    // Rafraîchir les données depuis le serveur
    await fetchIntervenantDetails(intervenantData.value.id)
    
    success(response.data.message || 'Statut du service mis à jour avec succès')
  } catch (error) {
    console.error('Erreur lors du toggle du service:', error)
    showError(error.response?.data?.error || 'Erreur lors de la modification du statut du service')
  } finally {
    isTogglingService.value = false
  }
}
```

#### 4. Message pour services archivés

Dans la section d'affichage du service, un message est ajouté si le service est archivé :

```vue
<!-- Message si service archivé -->
<span v-if="isCurrentServiceArchived" class="text-xs text-gray-500 italic">
  • L'intervenant a archivé ce service
</span>
```

---

## 📡 Routes API

### Route Admin

**Endpoint :** `POST /api/admin/intervenants/{intervenantId}/services/{serviceId}/toggle-status`

**Headers :**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Réponse succès (200) :**
```json
{
  "message": "Service activé pour cet intervenant",
  "status": "active",
  "isActive": true
}
```

**Réponse erreur (400) :** Statut non modifiable (archive, refuse, demmande)
```json
{
  "error": "Impossible de modifier le statut de ce service. Statut actuel: archive"
}
```

**Réponse erreur (404) :** Relation non trouvée
```json
{
  "error": "Relation intervenant-service non trouvée"
}
```

---

## 🔄 Service Frontend

### Fichier : `frontend/src/services/adminService.js`

**Nouvelle méthode :**

```javascript
/**
 * Activer/Désactiver un service pour un intervenant (Admin)
 */
toggleIntervenantServiceStatus(intervenantId, serviceId) {
  return api.post(`admin/intervenants/${intervenantId}/services/${serviceId}/toggle-status`);
}
```

---

## 🗄️ Structure de la Base de Données

### Table : `intervenant_service`

| Colonne | Type | Description |
|---------|------|-------------|
| `intervenant_id` | integer | ID de l'intervenant (PK) |
| `service_id` | integer | ID du service (PK) |
| `status` | enum | Statut: `'active'`, `'desactive'`, `'demmande'`, `'refuse'`, `'archive'` |
| `presentation` | text | Présentation du service par l'intervenant |
| `experience` | decimal(5,2) | Années d'expérience |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de mise à jour |

**Clé primaire composite :** `(intervenant_id, service_id)`

---

## 📊 Comportement par Statut

### Statut `active`

- ✅ Service affiché dans le profil de l'intervenant
- ✅ Toggle **vert** et activé
- ✅ L'admin peut désactiver le service
- ✅ Service visible dans la liste principale des intervenants

### Statut `desactive`

- ✅ Service affiché dans le profil de l'intervenant
- ⚪ Toggle **gris** et activé
- ✅ L'admin peut activer le service
- ❌ Service **non visible** dans la liste principale des intervenants

### Statut `archive`

- ✅ Service affiché dans le profil de l'intervenant
- 🟢 Toggle **vert** mais **désactivé**
- ❌ L'admin **ne peut pas** modifier le statut
- ℹ️ Message affiché : "L'intervenant a archivé ce service"
- ✅ Badge "(Archivé)" affiché sous le toggle
- ✅ Service reste dans la navigation entre services

### Statut `refuse`

- ❌ Service **non affiché** dans le profil de l'intervenant
- ❌ Non modifiable par l'admin
- ❌ Service non visible dans la liste principale

### Statut `demmande`

- ❌ Service **non affiché** dans le profil de l'intervenant
- ✅ Géré dans la page **AdminDemandes.vue**
- ❌ Service non visible dans la liste principale

---

## 🎯 Flux Utilisateur

### Activation d'un Service

1. Admin ouvre le profil de l'intervenant
2. Navigue vers l'onglet "Tâches/Service"
3. Voit le service avec toggle gris (statut `desactive`)
4. Clique sur le toggle
5. Le service passe à `active`
6. Toggle devient vert
7. Service devient visible dans la liste principale

### Désactivation d'un Service

1. Admin ouvre le profil de l'intervenant
2. Navigue vers l'onglet "Tâches/Service"
3. Voit le service avec toggle vert (statut `active`)
4. Clique sur le toggle
5. Le service passe à `desactive`
6. Toggle devient gris
7. Service disparaît de la liste principale
8. **Les données (présentation, expérience) sont conservées**

### Service Archivé

1. Admin ouvre le profil de l'intervenant
2. Navigue vers l'onglet "Tâches/Service"
3. Voit le service avec toggle vert mais désactivé
4. Message affiché : "L'intervenant a archivé ce service"
5. Badge "(Archivé)" visible
6. L'admin **ne peut pas** modifier le statut
7. Si tentative de modification, message d'information affiché

---

## 🔍 Points Techniques Importants

### 1. Utilisation de l'Index

Les computed `currentServiceStatus` et `getCurrentServiceId` utilisent l'index pour trouver le service dans `allServicesWithDetailsAll` plutôt que de chercher par nom. Cela garantit :

- ✅ Fiabilité : Pas de problèmes de casse ou d'espaces
- ✅ Performance : Accès direct par index
- ✅ Correspondance : `allServices` et `allServicesWithDetailsAll` ont le même ordre

### 2. Réactivité Vue.js

Les computed properties sont réactives et se mettent à jour automatiquement quand :
- L'index du service change (`currentServiceIndex`)
- Les données de l'intervenant changent (`intervenantData`)
- Le statut est modifié après le toggle

### 3. Préservation des Données

Lors de la désactivation d'un service :
- ✅ `presentation` est conservée
- ✅ `experience` est conservée
- ✅ Seul le `status` change de `'active'` à `'desactive'`

### 4. Filtrage dans la Liste Principale

Dans `AdminIntervenants.vue`, seuls les services avec statut `'active'` sont affichés dans les cartes d'intervenant. Cela se fait automatiquement côté backend dans la méthode `getIntervenants()`.

---

## 🐛 Gestion des Erreurs

### Erreur : "Erreur: impossible de récupérer l'ID du service"

**Cause possible :** L'index du service ne correspond pas à un élément valide dans `allServicesWithDetailsAll`

**Solution :** Vérifier que :
- `allServicesWithDetailsAll` contient des données
- `currentServiceIndex` est valide
- Les données sont bien chargées depuis le serveur

### Erreur : "Impossible de modifier le statut de ce service"

**Cause :** Tentative de modifier un service avec statut `'archive'`, `'refuse'`, ou `'demmande'`

**Solution :** C'est un comportement normal. Ces statuts ne peuvent pas être modifiés via le toggle.

---

## 📝 Notes de Développement

### Backend

- Le statut est toujours retourné comme **string** (pas null) pour garantir la comparaison
- Les services avec statut `'refuse'` et `'demmande'` sont **exclus** de `allServicesWithDetailsAll`
- La méthode `toggleIntervenantServiceStatus` vérifie explicitement que le statut est modifiable

### Frontend

- Utilisation de **computed properties** pour la réactivité
- Le toggle utilise directement l'**index** pour trouver le service (plus fiable)
- Gestion des états de chargement avec `isTogglingService`
- Messages d'information pour les services archivés

---

## ✅ Checklist de Test

- [ ] Toggle fonctionne pour service `active` → `desactive`
- [ ] Toggle fonctionne pour service `desactive` → `active`
- [ ] Toggle vert pour service `active`
- [ ] Toggle gris pour service `desactive`
- [ ] Toggle vert désactivé pour service `archive`
- [ ] Message affiché pour service `archive`
- [ ] Services `refuse` non affichés
- [ ] Services `demmande` non affichés
- [ ] Services `desactive` non visibles dans la liste principale
- [ ] Services `active` visibles dans la liste principale
- [ ] Présentation et expérience conservées après désactivation
- [ ] Erreur affichée si tentative de modifier service archivé

---

## 🔗 Fichiers Modifiés

### Backend

1. `backend/app/Http/Controllers/Api/Admin/AdminController.php`
   - Méthode `getIntervenantDetails()` : Filtrage des services
   - Nouvelle méthode `toggleIntervenantServiceStatus()`

2. `backend/routes/api.php`
   - Nouvelle route : `POST /api/admin/intervenants/{intervenantId}/services/{serviceId}/toggle-status`

### Frontend

1. `frontend/src/components/Admin/AdminIntervenantProfile.vue`
   - Ajout du toggle switch
   - Computed properties pour la gestion du statut
   - Fonction `toggleServiceStatus()`
   - Affichage des messages pour services archivés

2. `frontend/src/services/adminService.js`
   - Nouvelle méthode `toggleIntervenantServiceStatus()`

---

**Date de création :** Décembre 2024  
**Version :** 1.0  
**Auteur :** Développement ServicePro

