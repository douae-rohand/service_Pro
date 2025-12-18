import api from './api'

class RatingService {
  async getClientCriteria() {
    const response = await api.get('evaluations/client-criteria')
    return response.data
  }

  async getIntervenantCriteria() {
    const response = await api.get('evaluations/intervenant-criteria')
    return response.data
  }

  async storeClientEvaluation(interventionId, evaluations, comment = '') {
    const response = await api.post(`evaluations/interventions/${interventionId}/rate-client`, {
      evaluations,
      comment
    })
    return response.data
  }

  async getClientEvaluations(interventionId) {
    const response = await api.get(`evaluations/interventions/${interventionId}/client-ratings`)
    return response.data
  }

  async canRateClient(interventionId) {
    const response = await api.get(`evaluations/interventions/${interventionId}/can-rate-client`)
    return response.data
  }

  async getClientAverageRating(clientId) {
    const response = await api.get(`evaluations/clients/${clientId}/average-rating`)
    return response.data
  }

  async getPublicEvaluations(interventionId) {
    const response = await api.get(`evaluations/interventions/${interventionId}/public`)
    return response.data
  }
}

export default new RatingService()
