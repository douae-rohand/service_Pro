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
     * Récupérer les services et tâches actifs d'un intervenant
     */
    getActiveServicesAndTasks(id) {
        return api.get(`intervenants/${id}/active-services-tasks`);
    },

    /**
     * Demander l'activation d'un service avec documents
     */
    requestActivation(intervenantId, serviceId, formData) {
        return api.post(`intervenants/${intervenantId}/services/${serviceId}/request-activation`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
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
    },

    /**
     * Update service materials
     */
    updateServiceMaterials(intervenantId, serviceId, materials) {
        return api.post(`intervenants/${intervenantId}/services/${serviceId}/materials`, { materials });
    },

    /**
     * Update service status
     */
    updateServiceStatus(intervenantId, serviceId, status) {
        return api.post(`intervenants/${intervenantId}/services/${serviceId}/status`, { status });
    },

    /**
     * Toggle service activation
     */
    toggleService(intervenantId, serviceId) {
        return api.post(`intervenants/${intervenantId}/services/${serviceId}/toggle`);
    }
};

export default intervenantService;
