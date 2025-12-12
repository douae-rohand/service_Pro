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
   * Ajouter un intervenant aux favoris
   */
  addFavorite(clientId, intervenantId) {
    return api.post(`clients/${clientId}/favorites`, { intervenantId });
  },

  /**
   * Retirer un intervenant des favoris
   */
  removeFavorite(clientId, intervenantId) {
    return api.delete(`clients/${clientId}/favorites/${intervenantId}`);
  },
};

export default favoriteService;


