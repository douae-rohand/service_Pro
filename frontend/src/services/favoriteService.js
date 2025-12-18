import api from './api';

/**
 * Service pour gérer les favoris
 */
const favoriteService = {
  /**
   * Récupérer les favoris d'un client
   */
  getFavorites(clientId) {
    return api.get(`clients/${clientId}/favorites`);
  },

  /**
   * Basculer le statut favori (Ajouter/Supprimer)
   */
  toggleFavorite(clientId, intervenantId, serviceId) {
    return api.post(`clients/${clientId}/favorites`, {
      intervenant_id: intervenantId,
      service_id: serviceId
    });
  },

  /**
   * Vérifier le statut favori
   */
  checkStatus(clientId, intervenantId, serviceId) {
    return api.get(`clients/${clientId}/favorites/check`, {
      params: {
        intervenant_id: intervenantId,
        service_id: serviceId
      }
    });
  },

  // Legacy support (to avoid breaking other calls if they exist, but they will fail without serviceId)
  addFavorite(clientId, intervenantId, serviceId) {
    return this.toggleFavorite(clientId, intervenantId, serviceId);
  },

  /**
   * Remove from favorites (alias for toggle since it handles removal)
   */
  removeFavorite(clientId, intervenantId, serviceId) {
    return this.toggleFavorite(clientId, intervenantId, serviceId);
  }
};

export default favoriteService;
