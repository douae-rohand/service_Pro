import interventionService from './interventionService';

/**
 * Service pour gérer les demandes (alias pour interventions côté client)
 */
const demandService = {
  /**
   * Récupérer les statistiques d'un client
   */
  async getClientStatistics(clientId) {
    return await interventionService.getClientStatistics(clientId);
  },

  /**
   * Récupérer les demandes d'un client
   */
  async getClientDemands(clientId) {
    const response = await interventionService.getByClientId(clientId);
    const interventions = response.data.data || response.data || [];
    return interventions.map(intervention => 
      interventionService.transformIntervention(intervention)
    );
  }
};

export { demandService };

