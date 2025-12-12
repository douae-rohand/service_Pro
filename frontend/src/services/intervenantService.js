import api from './api'

const intervenantService = {
  // Fetch intervenants for a task via the Tache resource which includes intervenants relation
  async getByTask(taskId) {
    try {
      const res = await api.get(`taches/${taskId}`)
      // The TacheController returns the tache object with an `intervenants` relation
      const intervenants = res.data?.intervenants ?? []
      return { data: intervenants }
    } catch (error) {
      console.error('Error fetching intervenants via getByTask:', error)
      throw error
    }
  },

  async getIntervenantByTask(taskId) {
    try {
      const res = await api.get(`taches/${taskId}/intervenants`)
      const intervenants = res.data?.intervenants ?? []
      return { data: intervenants }
    } catch (error) {
      console.error('Error fetching intervenants via getIntervenantByTask:', error)
      throw error
    }
  },

  // Fallback generic list with optional filters
  list(params = {}) {
    return api.get('intervenants', { params })
  }
}

export default intervenantService