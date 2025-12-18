import api from './api'

const availabilityService = {
  // Get current intervenant's disponibilites
  getMyDisponibilites() {
    return api.get('intervenants/me/disponibilites')
  },

  // Create regular availability (weekly schedule)
  createRegularAvailability(availabilities) {
    return api.post('intervenants/me/disponibilites/regular', {
      availabilities
    })
  },

  // Create special availability (one-time exception)
  createSpecialAvailability(data) {
    return api.post('intervenants/me/disponibilites/special', data)
  },

  // Delete a specific disponibilite
  deleteDisponibilite(id) {
    return api.delete(`intervenants/me/disponibilites/${id}`)
  }
}

export default availabilityService
