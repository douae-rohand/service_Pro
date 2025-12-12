import api from './api';

/**
 * Service pour l'administration
 */
const adminService = {
    // ============== DASHBOARD ==============
    /**
     * Récupérer les statistiques du dashboard
     */
    getStats() {
        return api.get('admin/stats');
    },

    // ============== CLIENTS ==============
    /**
     * Récupérer tous les clients
     */
    getClients(params = {}) {
        return api.get('admin/clients', { params });
    },

    /**
     * Récupérer un client par ID
     */
    getClient(id) {
        return api.get(`clients/${id}`);
    },

    /**
     * Récupérer les détails complets d'un client
     */
    getClientDetails(id) {
        return api.get(`admin/clients/${id}`);
    },

    /**
     * Mettre à jour un client
     */
    updateClient(id, data) {
        return api.put(`clients/${id}`, data);
    },

    /**
     * Suspendre/Activer un client
     */
    toggleClientStatus(id) {
        return api.post(`admin/clients/${id}/toggle-status`);
    },

    /**
     * Récupérer les interventions d'un client
     */
    getClientInterventions(id) {
        return api.get(`clients/${id}/interventions`);
    },

    // ============== INTERVENANTS ==============
    /**
     * Récupérer tous les intervenants
     */
    getIntervenants(params = {}) {
        return api.get('admin/intervenants', { params });
    },

    /**
     * Récupérer un intervenant par ID
     */
    getIntervenant(id) {
        return api.get(`intervenants/${id}`);
    },

    /**
     * Mettre à jour un intervenant
     */
    updateIntervenant(id, data) {
        return api.put(`intervenants/${id}`, data);
    },

    /**
     * Suspendre/Activer un intervenant
     */
    toggleIntervenantStatus(id) {
        return api.post(`admin/intervenants/${id}/toggle-status`);
    },

    /**
     * Récupérer les tâches d'un intervenant
     */
    getIntervenantTaches(id) {
        return api.get(`intervenants/${id}/taches`);
    }
};

export default adminService;

