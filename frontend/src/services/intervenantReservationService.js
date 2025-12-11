import api from './api';

/**
 * Service pour gérer les réservations de l'intervenant
 */
const reservationService = {
  /**
   * Récupérer toutes les réservations de l'intervenant connecté
   */
  getMyReservations() {
    return api.get('intervenants/me/reservations');
  },

  /**
   * Accepter une réservation
   */
  acceptReservation(reservationId) {
    return api.post(`reservations/${reservationId}/accept`);
  },

  /**
   * Refuser une réservation
   */
  refuseReservation(reservationId) {
    return api.post(`reservations/${reservationId}/refuse`);
  },

  /**
   * Obtenir les détails d'une réservation
   */
  getReservation(reservationId) {
    return api.get(`reservations/${reservationId}`);
  }
};

export default reservationService;
