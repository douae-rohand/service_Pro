<template>
  <div class="max-w-6xl mx-auto">
    <!-- Loading State -->
    <!-- <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement des demandes...</p>
    </div> -->

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600 mb-6">
      <AlertCircle :size="20" class="inline mr-2" />
      {{ error }}
    </div>

    <!-- Content -->
    <div v-else>
      <!-- Status Filter -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl mb-4" style="color: #2f4f4f">Filtrer par statut</h3>
        <div class="flex flex-wrap gap-3">
          <button
            v-for="(config, key) in statusConfig"
            :key="key"
            @click="selectedStatus = key"
            class="px-4 py-2 rounded-lg border-2 transition-all"
            :class="selectedStatus === key ? 'text-white shadow-md' : 'bg-gray-50 hover:bg-gray-100'"
            :style="{
              borderColor: selectedStatus === key ? config.color : '#E5E7EB',
              backgroundColor: selectedStatus === key ? config.color : '',
              color: selectedStatus === key ? 'white' : '#2F4F4F'
            }"
          >
            {{ config.label }} ({{ config.count }})
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && !error && filteredDemands.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
        <FileText :size="64" class="mx-auto mb-4 text-gray-400" />
        <h3 class="text-xl font-bold mb-2" style="color: #2f4f4f">
          Aucune demande trouvée
        </h3>
        <p class="text-gray-600 mb-4">
          Vous n'avez pas encore de demandes de service.
        </p>
        <p class="text-sm text-gray-500">
          Vos demandes apparaîtront ici une fois créées.
        </p>
      </div>

      <!-- Demands List -->
      <div v-else class="space-y-4">
        <div
          v-for="demand in filteredDemands"
          :key="demand.id"
          class="bg-white rounded-lg shadow-md p-6 border-l-4 transition-all hover:shadow-lg"
          :style="{ borderColor: getBorderColor(demand.status) }"
        >
          <!-- Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-start gap-4 flex-1">
              <img
                :src="demand.intervenant.image"
                :alt="demand.intervenant.name"
                class="w-16 h-16 rounded-full object-cover border-2"
                style="border-color: #92b08b"
              />
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <h4 class="text-xl" style="color: #2f4f4f">
                    {{ demand.service }} - {{ demand.task }}
                  </h4>
                  <span
                    class="px-3 py-1 rounded-full text-white text-xs"
                    :style="{ backgroundColor: getStatusBgColor(demand.status) }"
                  >
                    {{ getStatusLabel(demand.status) }}
                  </span>
                </div>
                <p class="text-gray-600 mb-2">
                  <User :size="16" class="inline mr-1" />
                  {{ demand.intervenant.name }}
                </p>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                  <span>
                    <Calendar :size="14" class="inline mr-1" />
                    {{ formatDate(demand.date) }}
                  </span>
                  <span>
                    <Clock :size="14" class="inline mr-1" />
                    {{ demand.time }}
                  </span>
                  <span>
                    <MapPin :size="14" class="inline mr-1" />
                    {{ demand.quartier }}
                  </span>
                </div>
              </div>
            </div>

            <div class="text-right">
              <div class="text-2xl mb-1 px-4 py-2 rounded-lg text-white" style="background-color: #1a5fa3">
                {{ demand.finalCost || demand.estimatedCost || 0 }} DH
              </div>
              <p v-if="demand.finalCost && demand.finalCost !== demand.estimatedCost" class="text-xs text-gray-500 line-through">
                Estimé: {{ demand.estimatedCost }} DH
              </p>
            </div>
          </div>

          <!-- Rating Section -->
          <div
            v-if="demand.status === 'completed'"
            class="mb-4 p-4 rounded-lg"
            :style="{
              backgroundColor: demand.rating ? '#F0FFF0' : canRate(demand) ? '#FFF9E6' : '#FFF0F0'
            }"
          >
            <!-- Has Rating -->
            <div v-if="demand.rating">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                  <Star :size="20" fill="#FEE347" color="#FEE347" />
                  <span class="text-lg">Votre évaluation: {{ demand.rating.overallRating.toFixed(1) }}/5</span>
                </div>
                <button
                  @click="viewDemandDetails(demand)"
                  class="text-sm px-3 py-1 rounded text-white hover:shadow-md transition-all flex items-center gap-1"
                  style="background-color: #1a5fa3"
                >
                  <Eye :size="14" />
                  Voir l'évaluation
                </button>
              </div>
              <p v-if="demand.rating.comment" class="text-sm text-gray-700 italic">"{{ demand.rating.comment }}"</p>
            </div>

            <!-- Can Rate -->
            <div v-else-if="canRate(demand)">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-bold mb-1" style="color: #e8793f">
                    Évaluez cette prestation !
                  </p>
                  <p class="text-xs text-gray-600">
                    Il vous reste {{ getDaysLeftToRate(demand) }} jour{{ getDaysLeftToRate(demand) > 1 ? 's' : '' }} pour laisser votre avis
                  </p>
                </div>
                <button
                  @click="openRatingModal(demand)"
                  class="px-4 py-2 rounded-lg text-white hover:shadow-md transition-all flex items-center gap-2"
                  style="background-color: #92b08b"
                >
                  <Star :size="16" />
                  Noter maintenant
                </button>
              </div>
            </div>

            <!-- Expired -->
            <div v-else class="text-center">
              <p class="text-sm text-red-600 font-bold">
                Le délai de 7 jours pour évaluer cette prestation est dépassé
              </p>
            </div>
          </div>

          <!-- Description -->
          <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-700">
              <FileText :size="14" class="inline mr-2" />
              {{ demand.description || 'Aucune description' }}
            </p>
          </div>

          <!-- Intervenant Response -->
          <div v-if="demand.intervenantResponse" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-gray-700">
              <MessageCircle :size="14" class="inline mr-2" />
              <span class="font-bold">Réponse de l'intervenant:</span> {{ demand.intervenantResponse }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button
              @click="viewDemandDetails(demand)"
              class="flex-1 px-4 py-2 border-2 rounded-lg transition-all hover:shadow-md font-bold"
              style="border-color: #1a5fa3; color: #1a5fa3"
            >
              <Eye :size="16" class="inline mr-2" />
              Voir détails
            </button>
            <button
              v-if="demand.status === 'pending'"
              @click="cancelDemand(demand.id)"
              class="flex-1 px-4 py-2 rounded-lg text-white transition-all hover:shadow-md font-bold"
              style="background-color: #e8793f"
            >
              <XCircle :size="16" class="inline mr-2" />
              Annuler
            </button>
          </div>
        </div>

        <!-- No Demands -->
        <div v-if="filteredDemands.length === 0 && !loading" class="text-center py-12 bg-white rounded-lg shadow-md">
          <AlertCircle :size="48" class="mx-auto mb-3 text-gray-400" />
          <p class="text-gray-600">Aucune demande pour ce statut</p>
        </div>
      </div>
    </div>

    <!-- Rate Intervenant Modal -->
    <RateIntervenantModal
      v-if="demandToRate"
      :intervenant="{
        id: demandToRate.intervenant.id,
        name: demandToRate.intervenant.name,
        image: demandToRate.intervenant.image
      }"
      :service="demandToRate.service"
      :task="demandToRate.task"
      :demand-id="demandToRate.id"
      @close="demandToRate = null"
      @submit="handleRatingSubmit"
    />

    <!-- Details Modal -->
    <DemandDetailsModal
      v-if="showDetails && selectedDemand"
      :demand="selectedDemand"
      @close="showDetails = false"
      @open-rating="openRatingModalFromDetails"
    />
  </div>
</template>

<script>
import {
  Calendar,
  Clock,
  MapPin,
  User,
  MessageCircle,
  CheckCircle,
  XCircle,
  AlertCircle,
  FileText,
  Star,
  Eye
} from 'lucide-vue-next';
import RateIntervenantModal from './RateIntervenantModal.vue';
import DemandDetailsModal from './DemandDetailsModal.vue';
import interventionService from '@/services/interventionService';
import api from '@/services/api';

export default {
  name: 'EnhancedMyDemandsTab',
  components: {
    Calendar,
    Clock,
    MapPin,
    User,
    MessageCircle,
    CheckCircle,
    XCircle,
    AlertCircle,
    FileText,
    Star,
    Eye,
    RateIntervenantModal,
    DemandDetailsModal
  },
  props: {
    clientId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      selectedStatus: 'all',
      selectedDemand: null,
      showDetails: false,
      demandToRate: null,
      demands: [],
      loading: false,
      error: null,
      statusCounts: {
        all: 0,
        pending: 0,
        accepted: 0,
        'in-progress': 0,
        completed: 0,
        cancelled: 0,
        rejected: 0
      }
    };
  },
  computed: {
    statusConfig() {
      return {
        all: { label: 'Toutes', color: '#2F4F4F', count: this.statusCounts.all },
        pending: { label: 'En attente', color: '#FEE347', count: this.statusCounts.pending },
        accepted: { label: 'Acceptées', color: '#92B08B', count: this.statusCounts.accepted },
        'in-progress': { label: 'En cours', color: '#1A5FA3', count: this.statusCounts['in-progress'] },
        completed: { label: 'Terminées', color: '#92B08B', count: this.statusCounts.completed },
        cancelled: { label: 'Annulées', color: '#E8793F', count: this.statusCounts.cancelled },
        rejected: { label: 'Refusées', color: '#DC2626', count: this.statusCounts.rejected }
      };
    },
    filteredDemands() {
      return this.selectedStatus === 'all'
        ? this.demands
        : this.demands.filter(d => d.status === this.selectedStatus);
    }
  },
  mounted() {
    this.loadDemands();
  },
  methods: {
    async loadDemands() {
      if (!this.clientId) {
        console.warn('No clientId provided to loadDemands');
        this.error = 'ID client manquant';
        this.loading = false;
        return;
      }

      this.loading = true;
      this.error = null;
      
      try {
        console.log('Loading demands for clientId:', this.clientId); // Debug log
        const response = await interventionService.getByClientId(this.clientId);
        console.log('Full API Response:', response); // Debug log
        console.log('Response data:', response.data); // Debug log
        
        // Backend returns { data: [...] }
        let demandsData = [];
        if (response.data?.data && Array.isArray(response.data.data)) {
          demandsData = response.data.data;
        } else if (Array.isArray(response.data)) {
          demandsData = response.data;
        } else {
          console.warn('Unexpected response format:', response.data);
          demandsData = [];
        }
        
        this.demands = demandsData;
        console.log('Loaded demands count:', this.demands.length); // Debug log
        console.log('Demands data:', this.demands); // Debug log
        
        if (this.demands.length === 0) {
          console.warn('No demands found. Check if client_id matches in database.');
        }
        
        // Recalculate counts for all statuses
        this.statusCounts.all = this.demands.length;
        this.statusCounts.pending = this.demands.filter(d => d.status === 'pending').length;
        this.statusCounts.accepted = this.demands.filter(d => d.status === 'accepted').length;
        this.statusCounts['in-progress'] = this.demands.filter(d => d.status === 'in-progress').length;
        this.statusCounts.completed = this.demands.filter(d => d.status === 'completed').length;
        this.statusCounts.cancelled = this.demands.filter(d => d.status === 'cancelled').length;
        this.statusCounts.rejected = this.demands.filter(d => d.status === 'rejected').length;
      } catch (error) {
        console.error('Error loading demands:', error);
        console.error('Error details:', error.response); // Debug log
        this.error = error.response?.data?.message || error.message || 'Erreur lors du chargement des demandes';
        this.demands = [];
      } finally {
        this.loading = false;
      }
    },
    canRate(demand) {
      if (demand.status !== 'completed' || demand.rating) return false;
      if (!demand.completedAt) return false;

      const completedDate = new Date(demand.completedAt);
      const today = new Date();
      const daysDiff = Math.floor((today.getTime() - completedDate.getTime()) / (1000 * 3600 * 24));

      return daysDiff <= 7;
    },
    isRatingExpired(demand) {
      if (demand.status !== 'completed' || demand.rating) return false;
      if (!demand.completedAt) return false;

      const completedDate = new Date(demand.completedAt);
      const today = new Date();
      const daysDiff = Math.floor((today.getTime() - completedDate.getTime()) / (1000 * 3600 * 24));

      return daysDiff <= 7;
    },
    getDaysLeftToRate(demand) {
      if (!demand.completedAt) return 0;

      const completedDate = new Date(demand.completedAt);
      const today = new Date();
      const daysDiff = Math.floor((today.getTime() - completedDate.getTime()) / (1000 * 3600 * 24));

      return Math.max(0, 7 - daysDiff);
    },
    getBorderColor(status) {
      const colors = {
        completed: '#92B08B',
        accepted: '#1A5FA3',
        pending: '#FEE347',
        'in-progress': '#1A5FA3',
        cancelled: '#E8793F',
        rejected: '#DC2626'
      };
      return colors[status] || '#DC2626';
    },
    getStatusBgColor(status) {
      return this.getBorderColor(status);
    },
    getStatusLabel(status) {
      return this.statusConfig[status]?.label || status;
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('fr-FR');
    },
    viewDemandDetails(demand) {
      this.selectedDemand = demand;
      this.showDetails = true;
    },
    openRatingModal(demand) {
      this.demandToRate = demand;
      this.showDetails = false; // Close details modal if open
    },
    openRatingModalFromDetails() {
      if (this.selectedDemand) {
        this.openRatingModal(this.selectedDemand);
      }
    },
    async handleRatingSubmit(ratingData) {
      if (!this.demandToRate) return;

      try {
        // Send rating to API
        await api.post(`interventions/${this.demandToRate.id}/evaluations`, ratingData);
        
        // Reload demands to get updated data
        await this.loadDemands();

        this.demandToRate = null;
        alert('Merci pour votre évaluation ! Elle a été publiée avec succès.');
      } catch (error) {
        console.error('Error submitting rating:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de la publication de l\'évaluation. Veuillez réessayer.';
        alert(errorMessage);
      }
    },
    async cancelDemand(demandId) {
      if (!confirm('Êtes-vous sûr de vouloir annuler cette demande ?')) {
        return;
      }

      try {
        // Call API to cancel intervention
        await api.post(`interventions/${demandId}/cancel`);
        
        // Reload demands to get updated data
        await this.loadDemands();

        alert('Demande annulée avec succès.');
      } catch (error) {
        console.error('Error cancelling demand:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de l\'annulation de la demande. Veuillez réessayer.';
        alert(errorMessage);
      }
    }
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
