import api from './api';

/**
 * Service pour gérer les intervenants
 */
const intervenantService = {
    /**
     * Récupérer tous les intervenants
     */
    getAll(params = {}) {
        return api.get('intervenants', { params });
    },

    /**
     * Récupérer un intervenant par ID avec toutes ses informations
     */
    getById(id) {
        return api.get(`intervenants/${id}`);
    },

    /**
     * Récupérer les interventions d'un intervenant
     */
    getInterventions(intervenantId) {
        return api.get(`intervenants/${intervenantId}/interventions`);
    },

    /**
     * Récupérer les disponibilités d'un intervenant
     */
    getDisponibilites(intervenantId) {
        return api.get(`intervenants/${intervenantId}/disponibilites`);
    },

    /**
     * Récupérer les tâches qu'un intervenant peut effectuer
     */
    getTaches(intervenantId) {
        return api.get(`intervenants/${intervenantId}/taches`);
    },
};

export default intervenantService;

