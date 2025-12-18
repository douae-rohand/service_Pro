import api from './api';

/**
 * Service pour gérer les statistiques
 */
const statsService = {
    /**
     * Récupérer toutes les statistiques
     */
    getAll() {
        return api.get('stats');
    },

    /**
     * Récupérer les avis et statistiques d'un intervenant
     */
    getIntervenantReviewsStats(intervenantId) {
        return api.get(`intervenants/${intervenantId}/reviews-stats`);
    }
};

export default statsService;

