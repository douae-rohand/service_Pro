import axios from "axios";

// Use VITE_API_URL from environment; fallback to local Laravel API
// Ensures exactly one trailing slash in the baseURL
const baseURL = (import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api").replace(/\/+$/, "");

const api = axios.create({
  baseURL: `${baseURL}/`
});

export default api;
    