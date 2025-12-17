import api from './api';

/**
 * Service for client profile operations
 */
const profileService = {
    /**
     * Get client profile statistics
     */
    getStatistics(clientId) {
        return api.get(`clients/${clientId}/profile/statistics`);
    },

    /**
     * Get client profile history
     */
    getHistory(clientId) {
        return api.get(`clients/${clientId}/profile/history`);
    },

    /**
     * Get client evaluations (reviews received)
     */
    getEvaluations(clientId) {
        return api.get(`clients/${clientId}/profile/evaluations`);
    },

    /**
     * Get client favorites
     */
    getFavorites(clientId) {
        return api.get(`clients/${clientId}/favorites`);
    }
};

export default profileService;










