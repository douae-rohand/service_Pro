import api from './api'

const clientReclamationService = {
  /**
   * Get all reclamations for the current client
   */
  async getAll(params = {}) {
    try {
      const res = await api.get('clients/me/reclamations', { params })
      return res.data
    } catch (error) {
      console.error('Error fetching client reclamations:', error)
      throw error
    }
  },

  /**
   * Get a specific reclamation by ID
   */
  async getById(id) {
    try {
      const res = await api.get(`clients/me/reclamations/${id}`)
      return res.data
    } catch (error) {
      console.error('Error fetching client reclamation:', error)
      throw error
    }
  },

  /**
   * Create a new reclamation
   */
  async create(data) {
    try {
      const res = await api.post('clients/me/reclamations', data)
      return res.data
    } catch (error) {
      console.error('Error creating client reclamation:', error)
      throw error
    }
  },

  /**
   * Get intervenants list for reclamation form
   */
  async getIntervenantsForReclamation() {
    try {
      // This will be used to populate the dropdown of intervenants
      // You can use the existing intervenantService or create a specific endpoint
      const res = await api.get('intervenants', { params: { active: 'true' } })
      return res.data
    } catch (error) {
      console.error('Error fetching intervenants for reclamation:', error)
      throw error
    }
  }
}

export default clientReclamationService

