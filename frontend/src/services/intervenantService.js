import api from './api'

const intervenantService = {
  // Récupérer tous les intervenants avec pagination et filtres
  // intervenantService.js - Version avec debug
  async getAllIntervenants(params = {}) {
    try {
      console.log('🔍 Calling API with params:', params);

      const res = await api.get('intervenants/search', { params });

      console.log('✅ API Response:', {
        status: res.status,
        statusText: res.statusText,
        dataKeys: Object.keys(res.data || {}),
        hasDataArray: Array.isArray(res.data?.data),
        pagination: res.data ? {
          current_page: res.data.current_page,
          last_page: res.data.last_page,
          total: res.data.total,
          data_count: res.data.data?.length || 0
        } : 'No data'
      });

      return res.data; // Laravel pagination returns {data: [...], current_page: 1, ...}

    } catch (error) {
      console.error('❌ API Error Details:', {
        message: error.message,
        status: error.response?.status,
        statusText: error.response?.statusText,
        url: error.config?.url,
        params: error.config?.params,
        responseData: error.response?.data
      });

      // Si c'est une 404, essayez la route de base
      if (error.response?.status === 404) {
        console.log('🔄 Trying base route instead...');
        try {
          const res = await api.get('intervenants', { params });
          return res.data;
        } catch (fallbackError) {
          console.error('❌ Fallback also failed:', fallbackError);
          throw fallbackError;
        }
      }

      throw error;
    }
  },

  // Récupérer un intervenant spécifique
  async getIntervenant(id) {
    try {
      const res = await api.get(`intervenants/${id}`)
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

  // Méthodes existantes (gardez-les si vous les utilisez)
  async getByTask(taskId) {
    try {
      const res = await api.get(`taches/${taskId}`)
      const intervenants = res.data?.intervenants ?? []
      return { data: intervenants }
    } catch (error) {
      console.error('Error fetching intervenants via getByTask:', error)
      throw error
    }
  },

  async getIntervenantByTask(taskId) {
    try {
      console.log('🔍[SERVICE] getIntervenantByTask for task:', taskId);

      const res = await api.get(`taches/${taskId}/intervenants`);

      console.log('📦[SERVICE] API Response:', {
        status: res.status,
        data: res.data,
        hasIntervenants: !!res.data?.intervenants,
        intervenantsCount: res.data?.intervenants?.length || 0
      });

      // ⭐⭐ CORRECTION : Votre API retourne {intervenants: [...]}
      // Retournez directement ce tableau
      const intervenants = res.data?.intervenants || [];

      return {
        data: intervenants,
        rawResponse: res.data // Pour debug
      };

    } catch (error) {
      console.error('❌[SERVICE] Error in getIntervenantByTask:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status
      });
      throw error;
    }
  },


  // Méthode legacy
  list(params = {}) {
    return api.get('intervenants', { params })
  },

  // Récupérer les évaluations d'un intervenant
  async getEvaluations(intervenantId) {
    try {
      const res = await api.get(`intervenants/${intervenantId}/evaluations`)
      return res.data
    } catch (error) {
      console.error('Error fetching intervenant evaluations:', error)
      throw error
    }
  },

  // --- Merged methods from the second declaration ---

  /**
   * Récupérer tous les intervenants - Alias for getAllIntervenants
   */
  getAll(params = {}) {
    return this.getAllIntervenants(params);
  },

  /**
   * Récupérer un intervenant par ID - Alias for getIntervenant
   */
  getById(id) {
    return this.getIntervenant(id);
  },

  /**
   * Récupérer les interventions d'un intervenant
   */
  getInterventions(id) {
    return api.get(`intervenants/${id}/interventions`);
  },

  /**
   * Récupérer les disponibilités d'un intervenant
   */
  getDisponibilites(id) {
    return api.get(`intervenants/${id}/disponibilites`);
  },

  /**
   * Récupérer les tâches d'un intervenant - Alias for getIntervenantTaches
   */
  getTaches(id) {
    return this.getIntervenantTaches(id);
  },

  /**
   * Récupérer les services et tâches actifs d'un intervenant
   */
  getActiveServicesAndTasks(id) {
    return api.get(`intervenants/${id}/active-services-tasks`);
  },

  /**
   * Demander l'activation d'un service avec documents
   */
  requestActivation(intervenantId, serviceId, formData) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/request-activation`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  /**
   * Créer un nouvel intervenant
   */
  create(data) {
    return api.post('intervenants', data);
  },

  /**
   * Mettre à jour un intervenant
   */
  update(id, data) {
    return api.put(`intervenants/${id}`, data);
  },

  /**
   * Supprimer un intervenant
   */
  delete(id) {
    return api.delete(`intervenants/${id}`);
  },

  /**
   * Update service materials
   */
  updateServiceMaterials(intervenantId, serviceId, materials) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/materials`, { materials });
  },

  /**
   * Update service status
   */
  updateServiceStatus(intervenantId, serviceId, status) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/status`, { status });
  },

  /**
   * Toggle service activation
   */
  toggleService(intervenantId, serviceId) {
    return api.post(`intervenants/${intervenantId}/services/${serviceId}/toggle`);
  }
}

export default intervenantService;
