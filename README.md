# 🌱 Garden Care – Convention de Codage

##  Introduction

Cette convention décrit les règles de codage utilisées pour le projet **Garden Care**, une plateforme de mise en relation entre clients et jardiniers.  
Elle assure : cohérence du code, maintenabilité, collaboration efficace et respect des bonnes pratiques Laravel, React et MySQL.

---

# 📁 1. Organisation du Projet

##  Backend — Laravel

```
/backend
│
├── app
│   ├── Http
│   │   └── Controllers
│   ├── Models
│   ├── Events
│   ├── Listeners
│   ├── Policies
│   └── Services
│
├── routes
│   ├── web.php
│   └── api.php
│
├── database
│   ├── migrations
│   ├── factories
│   └── seeders
│
├── storage
└── config
```

##  Frontend — React

```
/frontend
│
├── src
│   ├── Components
│   │   ├── Common
│   │   ├── Client
│   │   ├── Jardinier
│   │   └── Admin
│   ├── Pages
│   ├── Services
│   ├── Hooks
│   ├── Utils
│   └── Assets
```

---

#  2. Règles de Nommage

##  Backend (Laravel)

| Élément      | Style            | Exemple                        |
|--------------|------------------|--------------------------------|
| Classes      | PascalCase       | `ServiceRequestController`     |
| Méthodes     | camelCase        | `approveRequest()`             |
| Variables    | camelCase        | `$servicePrice`                |
| Models       | PascalCase       | `Jardinier`                    |
| Controllers  | PascalCase       | `AuthController`               |
| Routes       | kebab-case       | `/api/service-requests`        |
| Tables       | snake_case       | `service_requests`             |
| Colonnes     | snake_case       | `request_date`                 |
| Constantes   | UPPER_SNAKE_CASE | `MAX_FILE_SIZE`                |

##  Frontend (React)

| Élément      | Style         | Exemple              |
|--------------|---------------|----------------------|
| Composants   | PascalCase    | `JardinierCard.jsx`  |
| Variables    | camelCase     | `serviceList`        |
| Fonctions    | camelCase     | `fetchServices()`    |
| Hooks        | camelCase     | `useChatStream()`    |
| CSS Classes  | kebab-case    | `.profile-card`      |
| Props        | camelCase     | `serviceId`          |

##  Base de Données (MySQL)

- Tables : `snake_case`
- Colonnes : `snake_case`
- Clés étrangères : `{model}_id`
- Tables pivot : `{model1}_{model2}`

Exemples :

```
clients
jardiniers
service_requests
jardinier_documents
chat_messages
ratings
```

---

#  3. Style de Code

##  Laravel & React

- Indentation : **4 espaces**
- Accolades : **sur la même ligne**
- Longueur max : **120 caractères**

### Exemple Laravel

```php
public function store(Request $request)
{
    $data = $request->validate([
        'description' => 'required|string|max:500',
    ]);

    ServiceRequest::create($data);
}
```

### Exemple React

```jsx
const JardinierCard = ({ name, rating }) => {
    return (
        <div className="jardinier-card">
            <h3>{name}</h3>
            <p>{rating} ★</p>
        </div>
    );
};
```

##  CSS

- Indentation : **2 espaces**
- Style : **kebab-case**

```css
.profile-card {
  background-color: var(--primary-color);
  padding: 1rem;
}
```

---

#  4. Règles de Sécurité

- Hash password → bcrypt
- Protection CSRF / XSS / SQL Injection
- Vérification MIME pour uploads
- Sessions expirent après 2h
- Blocage après 5 échecs de connexion
- HTTPS obligatoire en production

---

#  5. Fichiers & Uploads

- Extensions autorisées : `jpg`, `jpeg`, `png`, `pdf`
- Taille max : **2 Mo**
- Renommage automatique : **UUID**
- Stockage Laravel : `storage/app/public/uploads`

---

#  6. Chat Temps Réel

### Envoi (AJAX)

```javascript
axios.post('/api/messages', {
  to: jardinierId,
  message: text
});
```

### Réception (SSE)

```javascript
const stream = new EventSource(`/api/messages/stream/${userId}`);
```

---

#  7. Convention API – REST

### Format JSON

```json
{
  "status": "success",
  "message": "Request created",
  "data": {
    "id": 15
  }
}
```

### Codes HTTP

- 200 OK
- 201 Created
- 400 Bad Request
- 401 Unauthorized
- 404 Not Found
- 422 Validation Error
- 500 Server Error

---

#  8. Documentation et Commentaires

## PHPDoc obligatoire pour :

- Controllers
- Services
- Méthodes complexes
- Events & Listeners

### Exemple

```php
/**
 * Envoie une demande de service à un jardinier.
 *
 * @param Request $request
 * @return JsonResponse
 */
```

## Commentaires React

```jsx
// Load messages when component mounts
useEffect(() => {
    loadMessages();
}, []);
```

---

#  9. Bonnes Pratiques Garden Care

- Validation frontend + backend obligatoire
- Pagination sur toutes les listes (`paginate(10)`)
- QueryBuilder recommandé
- Chargement paresseux (Lazy Loading) pour React
- Commentaire obligatoire si note ≤ 3
- Chat actif uniquement en état : pending / negotiating

---

#  10. Conclusion

Cette convention garantit une qualité professionnelle du code Garden Care.
Elle doit être respectée par tous les membres du projet et pourra évoluer si nécessaire.
