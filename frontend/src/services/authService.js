import api from './api';

/**
 * Service pour l'authentification
 */
const authService = {
    /**
     * Connexion utilisateur
     */
    async login(credentials) {
        const res = await api.post('auth/login', credentials);
        if (res.data?.user) {
            localStorage.setItem('user', JSON.stringify(res.data.user));
        }
        return res;
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

    verifyEmail(email, code) {
        return api.post('auth/verify-email', { email, code });
    },

    resendVerification(email) {
        return api.post('auth/resend-verification', { email });
    },

    /**
     * Déconnexion utilisateur
     */
    async logout() {
        try {
            await api.post('auth/logout');
        } catch (error) {
            console.error('API Logout failed:', error);
        } finally {
            this.setAuthToken(null);
        }
    },

    async register(userData) {
        const res = await api.post('auth/register', userData);
        if (res.data?.user) {
            localStorage.setItem('user', JSON.stringify(res.data.user));
        }
        return res;
    },

    /**
     * Récupérer l'utilisateur connecté (API)
     */
    async getCurrentUser() {
        try {
            const res = await api.get('auth/user');
            if (res.data) {
                localStorage.setItem('user', JSON.stringify(res.data));
            }
            return res;
        } catch (error) {
            console.error('Error fetching user:', error);
            throw error;
        }
    },

    /**
     * Récupérer l'utilisateur depuis le cache (synchrone)
     */
    getUserSync() {
        try {
            const userStr = localStorage.getItem('user');
            return userStr ? JSON.parse(userStr) : null;
        } catch (e) {
            return null;
        }
    },

    /**
     * Mettre à jour le profil
     */
    updateProfile(data) {
        // If data is FormData, don't set Content-Type header (axios will set it automatically)
        const config = data instanceof FormData ? {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        } : {};

        if (data instanceof FormData) {
            return api.post('auth/profile', data, config);
        }

        return api.put('auth/profile', data, config);
    },

    /**
     * Changer le mot de passe
     */
    changePassword(data) {
        return api.post('auth/change-password', data);
    },

    /**
     * Mettre à jour la photo de profil
     */
    updateAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);

        return api.post('auth/profile/avatar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
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
            localStorage.removeItem('user');
        }
    },

    /**
     * Récupérer le token du localStorage
     */
    getToken() {
        const token = localStorage.getItem('token');
        // console.log('authService.getToken:', token ? 'Token présent' : 'Pas de token');
        return token;
    },

    /**
     * Vérifier si l'utilisateur est connecté
     */
    isAuthenticated() {
        const hasToken = !!this.getToken();
        console.log('authService.isAuthenticated:', hasToken);
        return hasToken;
    }
};

export default authService;
