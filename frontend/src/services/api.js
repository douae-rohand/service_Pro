import axios from "axios";

// Use VITE_API_URL from environment; fallback to local Laravel API
// Ensures exactly one trailing slash in the baseURL
const baseURL = (import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api").replace(/\/+$/, "");

const api = axios.create({
  baseURL: `${baseURL}/`
});

// Add request interceptor to include token from localStorage
api.interceptors.request.use(
  (config) => {
    // TODO: Remove this condition when authentication is implemented
    // Skip adding token for test routes (intervenants/me/taches) during development
    const isTestRoute = config.url && (
      config.url.includes('intervenants/me/taches') ||
      config.url.includes('intervenants/test')
    );
    
    if (!isTestRoute) {
      const token = localStorage.getItem('token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } else {
      // Explicitly remove Authorization header for test routes
      delete config.headers.Authorization;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;
    