import api from './api'

class ClientService {
    async getClientProfile(interventionId) {
        const response = await api.get(`clients/${interventionId}/profile-for-intervenant`)
        return response.data
    }
}

export default new ClientService()
