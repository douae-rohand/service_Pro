import api from './api';

/**
 * Service pour gérer les réservations de l'intervenant
 */
const reservationService = {
  /**
   * Récupérer toutes les réservations de l'intervenant connecté
   */
  getMyReservations() {
    return api.get('intervenants/me/reservations').then(res => res.data);
  },

  /**
   * Accepter une réservation
   */
  acceptReservation(reservationId) {
    return api.post(`intervenants/me/reservations/${reservationId}/accept`).then(res => res.data);
  },

  /**
   * Refuser une réservation
   */
  refuseReservation(reservationId) {
    return api.post(`intervenants/me/reservations/${reservationId}/refuse`).then(res => res.data);
  },

  /**
   * Obtenir les détails d'une réservation
   */
  getReservation(reservationId) {
    return api.get(`reservations/${reservationId}`).then(res => res.data);
  },

  /**
   * Générer la facture pour une réservation
   */
  generateInvoice(reservationId) {
    return api.post(`intervenants/me/reservations/${reservationId}/invoice`).then(res => res.data);
  }
};

export default reservationService;
