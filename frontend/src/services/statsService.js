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
    }
};

export default statsService;

