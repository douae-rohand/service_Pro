import api from './api';

/**
 * Service pour gérer les taches de l'intervenant
 */
const intervenantTacheService = {
  /**
   * Récupérer toutes les taches de l'intervenant connecté
   */
  getMyTaches() {
    return api.get('intervenants/me/taches').then(res => res.data);
  },

  /**
   * Mettre à jour une tache de l'intervenant
   */
  updateMyTache(tacheId, data) {
    return api.put(`intervenants/me/taches/${tacheId}`, data).then(res => res.data);
  },

  /**
   * Activer/Désactiver une tache
   */
  toggleActive(tacheId) {
    return api.post(`intervenants/me/taches/${tacheId}/toggle-active`).then(res => res.data);
  },

  /**
   * Supprimer une tache (retirer la relation)
   */
  deleteTache(tacheId) {
    return api.delete(`intervenants/me/taches/${tacheId}`).then(res => res.data);
  },
};

export default intervenantTacheService;

