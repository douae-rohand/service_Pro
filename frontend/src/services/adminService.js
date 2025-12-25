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
     * Récupérer un intervenant par ID (utilise l'endpoint admin pour le format correct)
     */
    getIntervenant(id) {
        return api.get(`admin/intervenants/${id}`);
    },

    /**
     * Récupérer les détails complets d'un intervenant (avec photos et justificatifs)
     */
    getIntervenantDetails(id) {
        return api.get(`admin/intervenants/${id}`);
    },

    /**
     * Télécharger un justificatif
     */
    downloadJustificatif(id) {
        return api.get(`admin/justificatifs/${id}/download`, {
            responseType: 'blob'
        });
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
     * Activer/Désactiver un service pour un intervenant (Admin)
     */
    toggleIntervenantServiceStatus(intervenantId, serviceId) {
        return api.post(`admin/intervenants/${intervenantId}/services/${serviceId}/toggle-status`);
    },

    /**
     * Récupérer les tâches d'un intervenant
     */
    getIntervenantTaches(id) {
        return api.get(`intervenants/${id}/taches`);
    },

    // ============== DEMANDES ==============
    /**
     * Récupérer toutes les demandes en attente
     */
    getPendingRequests() {
        return api.get('admin/demandes');
    },

    /**
     * Approuver une demande d'intervenant pour un service spécifique
     * @param {number} id - ID de l'intervenant
     * @param {number} serviceId - ID du service à approuver
     */
    approveRequest(id, serviceId) {
        return api.post(`admin/demandes/${id}/approve`, { service_id: serviceId });
    },

    /**
     * Rejeter une demande d'intervenant pour un service spécifique
     * @param {number} id - ID de l'intervenant
     * @param {number} serviceId - ID du service à rejeter
     */
    rejectRequest(id, serviceId) {
        return api.post(`admin/demandes/${id}/reject`, { service_id: serviceId });
    },

    // ============== SERVICES ==============
    /**
     * Récupérer tous les services avec statistiques
     */
    getServices() {
        return api.get('admin/services');
    },

    /**
     * Créer un nouveau service
     */
    createService(data) {
        return api.post('admin/services', data);
    },

    /**
     * Activer/Désactiver un service
     */
    toggleServiceStatus(id) {
        return api.post(`admin/services/${id}/toggle-status`);
    },

    /**
     * Récupérer les statistiques d'un service
     */
    getServiceStats(id) {
        return api.get(`admin/services/${id}/stats`);
    },

    /**
     * Récupérer les sous-services (tâches) d'un service
     */
    getServiceTaches(serviceId) {
        return api.get(`admin/services/${serviceId}/taches`);
    },

    /**
     * Créer un nouveau sous-service (tâche)
     */
    createTache(serviceId, data) {
        return api.post(`admin/services/${serviceId}/taches`, data);
    },

    /**
     * Modifier un sous-service (tâche)
     */
    updateTache(tacheId, data) {
        return api.put(`admin/taches/${tacheId}`, data);
    },

    /**
     * Supprimer un sous-service (tâche)
     */
    deleteTache(tacheId) {
        return api.delete(`admin/taches/${tacheId}`);
    },

    // ============== CONTRAINTES ==============
    /**
     * Récupérer les contraintes d'une tâche
     */
    getTacheContraintes(tacheId) {
        return api.get(`admin/taches/${tacheId}/contraintes`);
    },

    /**
     * Créer une contrainte pour une tâche
     */
    createContrainte(tacheId, data) {
        return api.post(`admin/taches/${tacheId}/contraintes`, data);
    },

    /**
     * Modifier une contrainte
     */
    updateContrainte(contrainteId, data) {
        return api.put(`admin/contraintes/${contrainteId}`, data);
    },

    /**
     * Supprimer une contrainte
     */
    deleteContrainte(contrainteId) {
        return api.delete(`admin/contraintes/${contrainteId}`);
    },

    // ============== MATÉRIELS ==============
    /**
     * Récupérer les matériels d'un service
     */
    getServiceMateriels(serviceId) {
        return api.get(`admin/services/${serviceId}/materiels`);
    },

    /**
     * Créer un matériel pour un service
     */
    createMateriel(serviceId, data) {
        return api.post(`admin/services/${serviceId}/materiels`, data);
    },

    /**
     * Modifier un matériel
     */
    updateMateriel(materielId, data) {
        return api.put(`admin/materiels/${materielId}`, data);
    },

    /**
     * Supprimer un matériel
     */
    deleteMateriel(materielId) {
        return api.delete(`admin/materiels/${materielId}`);
    },

    // ============== RECLAMATIONS ==============
    /**
     * Récupérer toutes les réclamations avec filtres et pagination
     */
    getReclamations(params = {}) {
        return api.get('admin/reclamations', { params });
    },

    /**
     * Traiter une réclamation
     */
    handleReclamation(id, action, notes = '', statut = null) {
        const data = { action, notes };
        if (statut) {
            data.statut = statut;
        }
        return api.post(`admin/reclamations/${id}/handle`, data);
    },

    // ============== HISTORIQUE ==============
    /**
     * Récupérer l'historique des interventions
     */
    getHistorique(params = {}) {
        return api.get('admin/historique', { params });
    },

    /**
     * Exporter l'historique en CSV
     */
    exportHistoriqueCSV(params = {}) {
        return api.get('admin/historique/export/csv', {
            params,
            responseType: 'blob'
        });
    },

    /**
     * Exporter l'historique en PDF
     */
    exportHistoriquePDF(params = {}) {
        return api.get('admin/historique/export/pdf', {
            params,
            responseType: 'blob'
        });
    }
};

export default adminService;

