import api from './api';

/**
 * Service pour gérer les réservations
 */
const bookingService = {
  /**
   * Récupérer les services d'un intervenant
   */
  getIntervenantServices(intervenantId) {
    return api.get(`intervenants/${intervenantId}/services`);
  },

  /**
   * Récupérer les tâches d'un service
   */
  getServiceTaches(serviceId, intervenantId) {
    return api.get(`services/${serviceId}/taches`, {
      params: { intervenantId }
    });
  },

  /**
   * Récupérer les disponibilités d'un intervenant
   */
  getIntervenantDisponibilites(intervenantId, date) {
    return api.get(`intervenants/${intervenantId}/disponibilites`, {
      params: { date }
    });
  },

  getTaskContraintes(tacheId) {
    return api.get(`taches/${tacheId}/contraintes`);
  },

  /**
   * Créer une intervention (réservation)
   */
  createIntervention(data) {
    return api.post('interventions', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },
};

export default bookingService;

