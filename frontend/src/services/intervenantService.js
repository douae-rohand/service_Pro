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
     * Récupérer un intervenant par ID
     */
    getById(id) {
        return api.get(`intervenants/${id}`);
    },

    /**
     * Récupérer les interventions d'un intervenant
     */
    getInterventions(id) {
        return api.get(`intervenants/${id}/interventions`);
    },

    /**
     * Récupérer les disponibilités d'un intervenant
     */
    getDisponibilites(id) {
        return api.get(`intervenants/${id}/disponibilites`);
    },

    /**
     * Récupérer les tâches d'un intervenant
     */
    getTaches(id) {
        return api.get(`intervenants/${id}/taches`);
    },

    /**
     * Créer un nouvel intervenant
     */
    create(data) {
        return api.post('intervenants', data);
    },

    /**
     * Mettre à jour un intervenant
     */
    update(id, data) {
        return api.put(`intervenants/${id}`, data);
    },

    /**
     * Supprimer un intervenant
     */
    delete(id) {
        return api.delete(`intervenants/${id}`);
    }
};

export default intervenantService;
