import api from './api';

/**
 * Service pour gérer les interventions
 */
const interventionService = {
  /**
   * Récupérer toutes les interventions
   */
  getAll(params = {}) {
    return api.get('interventions/index', { params });
  },

  /**
   * Récupérer les interventions d'un client
   */
  getByClientId(clientId) {
    return api.get(`clients/${clientId}/interventions`);
  },

  /**
   * Récupérer une intervention par ID
   */
  getById(id) {
    return api.get(`interventions/${id}`);
  },

  /**
   * Créer une nouvelle intervention
   */
  create(data) {
    return api.post('interventions', data);
  },

  /**
   * Mettre à jour une intervention
   */
  update(id, data) {
    return api.put(`interventions/${id}`, data);
  },

  /**
   * Supprimer une intervention
   */
  delete(id) {
    return api.delete(`interventions/${id}`);
  },

  /**
   * Récupérer les interventions à venir
   */
  getUpcoming() {
    return api.get('interventions/filter/upcoming');
  },

  /**
   * Récupérer les interventions terminées
   */
  getCompleted() {
    return api.get('interventions/filter/completed');
  },

  /**
   * Récupérer les statistiques d'un client
   */
  async getClientStatistics(clientId) {
    try {
      const response = await api.get(`clients/${clientId}/statistics`);
      return response.data;
    } catch (error) {
      console.error('Error fetching client statistics:', error);
      throw error;
    }
  },

  /**
   * Mapper les statuts de la base de données vers les statuts frontend
   */
  mapStatus(dbStatus) {
    const statusMap = {
      'en_attente': 'pending',
      'en attente': 'pending',
      'acceptee': 'accepted',
      'acceptée': 'accepted',
      'acceptées': 'accepted',
      'en_cours': 'in-progress',
      'en cours': 'in-progress',
      'terminee': 'completed',
      'terminée': 'completed',
      'terminées': 'completed',
      'annulee': 'cancelled',
      'annulée': 'cancelled',
      'annulées': 'cancelled',
      'refusee': 'rejected',
      'refusée': 'rejected',
      'refusées': 'rejected',
      'planifiee': 'accepted',
      'planifiée': 'accepted'
    };
    return statusMap[dbStatus?.toLowerCase()] || dbStatus?.toLowerCase() || 'pending';
  },

  /**
   * Transformer une intervention de la base de données vers le format frontend
   * Note: This is now handled by the backend InterventionResource
   */
  transformIntervention(intervention) {
    // Backend now returns data in the correct format via InterventionResource
    // This method is kept for backward compatibility but should not be needed
    return intervention;
    const status = this.mapStatus(intervention.status);
    
    return {
      id: intervention.id,
      service: intervention.tache?.service?.nom_service || 'N/A',
      task: intervention.tache?.nom_tache || 'N/A',
      intervenant: {
        id: intervention.intervenant?.id,
        name: `${intervention.intervenant?.utilisateur?.prenom || ''} ${intervention.intervenant?.utilisateur?.nom || ''}`.trim() || 'N/A',
        image: intervention.intervenant?.utilisateur?.url || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop',
        phone: intervention.intervenant?.utilisateur?.telephone || '',
        averageRating: null, // TODO: Calculate from evaluations
        totalReviews: 0, // TODO: Count evaluations
        ratingDistribution: {} // TODO: Calculate from evaluations
      },
      date: intervention.dateIntervention || intervention.date_intervention || intervention.date,
      time: intervention.time || '09:00', // TODO: Add time field to database
      status: status,
      address: intervention.address || '',
      quartier: intervention.ville || '',
      estimatedCost: 0, // TODO: Calculate from tache and materials
      finalCost: intervention.facture?.ttc || null,
      duration: null, // TODO: Calculate or store duration
      description: intervention.tache?.description || '',
      createdAt: intervention.createdAt || intervention.created_at || intervention.created_at,
      completedAt: status === 'completed' ? (intervention.updatedAt || intervention.updated_at) : null,
      intervenantResponse: intervention.commentaires?.find(c => c.type_auteur === 'intervenant')?.commentaire || null,
      rating: this.extractClientRating(intervention.evaluations),
      invoice: intervention.facture ? this.transformInvoice(intervention.facture, intervention) : null,
      materials: this.transformMaterials(intervention.materiels),
      photos: intervention.photos?.map(p => {
        const photoUrl = p.url || p.photo_path || p.photoPath;
        // If it's a relative path, prepend the API base URL
        if (photoUrl && !photoUrl.startsWith('http')) {
          return `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'}/storage/${photoUrl.replace(/^\//, '')}`;
        }
        return photoUrl;
      }).filter(Boolean) || []
    };
  },

  /**
   * Extraire la note du client depuis les évaluations
   */
  extractClientRating(evaluations) {
    if (!evaluations || evaluations.length === 0) return null;
    
    const clientEvaluations = evaluations.filter(e => e.type_auteur === 'client');
    if (clientEvaluations.length === 0) return null;

    const criteriaRatings = {};
    let totalRating = 0;
    
    clientEvaluations.forEach(evaluation => {
      const criteriaName = evaluation.critaire?.nom_critaire?.toLowerCase().replace(/\s+/g, '_') || 'quality';
      criteriaRatings[criteriaName] = evaluation.note || 0;
      totalRating += evaluation.note || 0;
    });

    const commentaire = clientEvaluations.find(evaluation => evaluation.commentaire)?.commentaire || '';
    
    return {
      criteriaRatings,
      overallRating: totalRating / clientEvaluations.length,
      comment: commentaire,
      wouldRecommend: true // TODO: Add this field to database
    };
  },

  /**
   * Transformer la facture
   */
  transformInvoice(facture, intervention) {
    // TODO: Extract actual duration and materials from facture or intervention
    return {
      date: facture.created_at || facture.createdAt,
      actualDuration: '2 heures', // TODO: Get from intervention or facture
      laborCost: facture.ttc || 0, // TODO: Separate labor and materials
      materialsProvided: [], // TODO: Extract from intervention_materiel
      materialsCost: 0, // TODO: Calculate from materials
      paymentDate: facture.created_at || facture.createdAt
    };
  },

  /**
   * Transformer les matériels
   */
  transformMaterials(materiels) {
    if (!materiels || materiels.length === 0) return {};
    
    const materialsObj = {};
    materiels.forEach(m => {
      materialsObj[m.nom_materiel] = true; // All materials in intervention are available
    });
    
    return materialsObj;
  },

  /**
   * Ajouter une photo à une intervention
   */
  addPhoto(interventionId, formData) {
    return api.post(`interventions/${interventionId}/photos`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }
};

export default interventionService;
