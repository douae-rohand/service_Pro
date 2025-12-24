import api from './api';

/**
 * Service pour gérer les réservations de l'intervenant
 */
const reservationService = {
  /**
   * Récupérer toutes les réservations de l'intervenant connecté
   */
  getMyReservations() {
    return api.get('intervenants/me/reservations').then(res => {
      // Map data to expected format for frontend if needed
      const reservations = res.data.reservations || res.data;

      const mappedReservations = reservations.map(r => ({
        ...r,
        // Ensure flat properties for filtering and display
        service: r.service || r.tache?.service?.nom_service || 'Service Inconnu',
        task: r.task || r.tache?.nom_tache || 'Tâche Inconnue',
        clientName: r.clientName || (r.client?.utilisateur ? `${r.client.utilisateur.prenom} ${r.client.utilisateur.nom}` : 'Client Inconnu'),
        clientImage: (function () {
          const img = r.clientImage || r.client?.utilisateur?.profile_photo;
          if (!img) return 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&h=300&fit=crop';
          if (img.startsWith('http')) return img;
          return `http://127.0.0.1:8000/storage/${img}`;
        })(),
        // Use raw strings from backend as requested
        date: r.date || 'N/A',
        time: r.time ? r.time.substring(0, 5) : 'N/A', // Ensure H:i format
        location: r.ville || r.location || 'Adresse non spécifiée'
      }));

      return { reservations: mappedReservations };
    });
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
