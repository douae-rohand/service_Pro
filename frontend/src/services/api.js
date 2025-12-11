import axios from "axios";

// Use VITE_API_URL from environment; fallback to local Laravel API
// Ensures exactly one trailing slash in the baseURL
const baseURL = (import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api").replace(/\/+$/, "");

const api = axios.create({
  baseURL: `${baseURL}/`,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000, // Timeout de 10 secondes
});

// Intercepteur pour ajouter le token d'authentification
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      // Le serveur a répondu avec un code d'erreur
      const { status, data } = error.response;
      
      if (status === 401) {
        // Token invalide ou expiré
        localStorage.removeItem('token');
        delete api.defaults.headers.common['Authorization'];
      }
      
      // Retourner l'erreur avec les détails du serveur
      return Promise.reject({
        message: data.message || 'Une erreur est survenue',
        errors: data.errors || {},
        status: status,
        data: data
      });
    } else if (error.request) {
      // La requête a été faite mais aucune réponse n'a été reçue
      return Promise.reject({
        message: 'Impossible de se connecter au serveur. Vérifiez votre connexion.',
        errors: {}
      });
    } else {
      // Une erreur s'est produite lors de la configuration de la requête
      return Promise.reject({
        message: error.message || 'Une erreur est survenue',
        errors: {}
      });
    }
  }
);

export default api;
    