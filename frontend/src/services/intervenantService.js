import api from './api'

const intervenantService = {
  // Récupérer tous les intervenants avec pagination et filtres
  async getAllIntervenants(params = {}) {
    try {
      console.log('🔍 Calling API with params:', params);
      const res = await api.get('intervenants/search', { params });
      return res.data;
    } catch (error) {
      console.error('❌ API Error:', error);
      throw error;
    }
  },

  // Récupérer un intervenant spécifique
  async getIntervenant(id) {
    try {
      console.log(`🔍[SERVICE] getIntervenant calling API for id: ${id}`);
      const res = await api.get(`intervenants/${id}`)
      console.log('✅[SERVICE] getIntervenant Response:', res.data);
      return res.data
    } catch (error) {
      console.error('Error fetching intervenant:', error)
      throw error
    }
  },

  // Récupérer les services d'un intervenant
  async getIntervenantServices(intervenantId) {
    try {
      const res = await api.get(`intervenants/${intervenantId}/services`)
      return res.data
    } catch (error) {
      console.error('Error fetching intervenant services:', error)
      throw error
    }
  },

  // Récupérer les tâches d'un intervenant
  async getIntervenantTaches(intervenantId) {
    try {
      const res = await api.get(`intervenants/${intervenantId}/taches`)
      return res.data
    } catch (error) {
      console.error('Error fetching intervenant taches:', error)
      throw error
    }
  },

  async getByTask(taskId) {
    try {
      const res = await api.get(`taches/${taskId}`)
      const intervenants = res.data?.intervenants ?? []
      return { data: intervenants }
    } catch (error) {
      console.error('Error in getByTask:', error)
      throw error
    }
  },

  async getIntervenantByTask(taskId) {
    try {
      const res = await api.get(`taches/${taskId}/intervenants`);
      // Backend returns: {status: 'success', data: [...], meta: {...}}
      // So we need to access res.data.data (the data key from the response)
      const intervenantsArray = res.data?.data || res.data?.intervenants || [];
      return {
        data: intervenantsArray,
        rawResponse: res.data
      };
    } catch (error) {
      console.error('Error in getIntervenantByTask:', error)
      throw error;
    }
  },

  list(params = {}) {
    return api.get('intervenants', { params }).then(res => res.data)
  },

  async getEvaluations(intervenantId) {
    try {
      const res = await api.get(`intervenants/${intervenantId}/evaluations`)
      return res.data
    } catch (error) {
      console.error('Error fetching evaluations:', error)
      throw error
    }
  },

  getAll(params = {}) {
    return this.getAllIntervenants(params);
  },

  getById(id) {
    return this.getIntervenant(id);
  },

  getInterventions(id) {
    return api.get(`intervenants/${id}/interventions`).then(res => res.data);
  },

  getDisponibilites(id) {
    return api.get(`intervenants/${id}/disponibilites`).then(res => res.data);
  },

  getTaches(id) {
    return this.getIntervenantTaches(id);
  },

  getActiveServicesAndTasks(id) {
    return api.get(`intervenants/${id}/active-services-tasks`).then(res => res.data);
  },

  requestActivation(intervenantId, serviceId, formData) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/request-activation`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }).then(res => res.data);
  },

  create(data) {
    return api.post('intervenants', data).then(res => res.data);
  },

  update(id, data) {
    return api.put(`intervenants/${id}`, data).then(res => res.data);
  },

  delete(id) {
    return api.delete(`intervenants/${id}`).then(res => res.data);
  },

  updateServiceMaterials(intervenantId, serviceId, materials) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/materials`, { materials }).then(res => res.data);
  },

  getIntervenantMaterials(intervenantId, serviceId) {
    return api.get(`intervenants/${intervenantId}/services/${serviceId}/materials`).then(res => res.data);
  },

  deleteIntervenantMaterial(intervenantId, materialId) {
    return api.delete(`intervenants/${intervenantId}/materials/${materialId}`).then(res => res.data);
  },

  updateServiceStatus(intervenantId, serviceId, status) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/status`, { status }).then(res => res.data);
  },

  toggleService(intervenantId, serviceId) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/toggle`).then(res => res.data);
  }
}

export default intervenantService;
