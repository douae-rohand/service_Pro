import api from './api';

/**
 * Service pour l'authentification
 */
const authService = {
    /**
     * Connexion utilisateur
     */
    login(credentials) {
        return api.post('auth/login', credentials);
    },

    forgotPassword(email) {
        return api.post('auth/forgot-password', { email });
    },

    verifyCode(email, code) {
        return api.post('auth/verify-code', { email, code });
    },

    resetPassword({ email, code, password, password_confirmation }) {
        return api.post('auth/reset-password', { email, code, password, password_confirmation });
    },

    /**
     * Déconnexion utilisateur
     */
    logout() {
        return api.post('auth/logout');
    },

    /**
     * Inscription utilisateur
     */
    register(userData) {
        return api.post('auth/register', userData);
    },

    /**
     * Récupérer l'utilisateur connecté
     */
    getCurrentUser() {
        return api.get('auth/user');
    },

    /**
     * Mettre à jour le profil
     */
    updateProfile(data) {
        return api.put('auth/profile', data);
    },

    /**
     * Changer le mot de passe
     */
    changePassword(data) {
        return api.post('auth/change-password', data);
    },

    /**
     * Définir le token d'authentification
     */
    setAuthToken(token) {
        if (token) {
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            localStorage.setItem('token', token);
        } else {
            delete api.defaults.headers.common['Authorization'];
            localStorage.removeItem('token');
        }
    },

    /**
     * Récupérer le token du localStorage
     */
    getToken() {
        return localStorage.getItem('token');
    },

    /**
     * Vérifier si l'utilisateur est connecté
     */
    isAuthenticated() {
        return !!this.getToken();
    }
};

export default authService;
