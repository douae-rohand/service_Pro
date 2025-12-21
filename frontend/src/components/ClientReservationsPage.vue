<template>
  <div class="min-h-screen bg-[#F9FAFB]">
    <!-- Hero Section -->
    <section 
      class="relative py-16 px-4 sm:px-6 lg:px-8 overflow-hidden mb-8"
      style="background: linear-gradient(to bottom, #E8F5E9, #FFFFFF)"
    >
      <!-- Decorative shapes -->
      <div class="absolute top-10 left-10 w-32 h-32 rounded-full opacity-30 blur-3xl animate-float" style="background-color: #92B08B"></div>
      <div class="absolute bottom-10 right-10 w-40 h-40 bg-yellow-100 rounded-full opacity-30 blur-3xl animate-float-delayed"></div>
      
      <div class="max-w-6xl mx-auto text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-fade-in-up" style="color: #305C7D">
          Mes <span class="relative inline-block">
            Réservations
            <svg class="absolute -bottom-2 left-0 w-full" height="8" viewBox="0 0 200 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 5.5C50 2 150 2 199 5.5" stroke="#FCD34D" stroke-width="3" stroke-linecap="round"/>
            </svg>
          </span>
        </h1>
        <p class="text-gray-600 text-lg animate-fade-in-up-delayed max-w-2xl mx-auto">
          Suivez l'état de vos demandes de service et gérez vos interventions en toute simplicité.
        </p>
      </div>
    </section>

    <!-- Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
      <!-- Error State -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-3xl p-6 text-red-600 mb-8 flex items-center gap-4 animate-fade-in">
        <AlertCircle :size="24" class="flex-shrink-0" />
        <span class="font-medium">{{ error }}</span>
      </div>

      <!-- Main Interface -->
      <div v-else>
        <!-- Status Filter Tabs -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-2 mb-8 overflow-x-auto no-scrollbar animate-fade-in">
          <div class="flex gap-1 min-w-max">
            <button
              v-for="(config, key) in statusConfig"
              :key="key"
              @click="selectedStatus = key"
              class="px-5 py-3 rounded-2xl transition-all duration-300 font-semibold text-sm flex items-center gap-2"
              :style="{
                backgroundColor: selectedStatus === key ? config.color : 'transparent',
                color: selectedStatus === key ? 'white' : '#64748B'
              }"
            >
              <div 
                v-if="selectedStatus !== key" 
                class="w-2 h-2 rounded-full" 
                :style="{ backgroundColor: config.color }"
              ></div>
              {{ config.label }}
              <span 
                class="px-2 py-0.5 rounded-full text-[10px] ml-1"
                :style="{ 
                  backgroundColor: selectedStatus === key ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.05)',
                  color: selectedStatus === key ? 'white' : '#64748B'
                }"
              >
                {{ config.count }}
              </span>
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-20 animate-fade-in">
          <div class="w-16 h-16 border-4 border-gray-100 border-t-[#305C7D] rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-500 font-medium">Récupération de vos réservations...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading && !error && filteredDemands.length === 0" class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-16 text-center animate-fade-in">
          <div class="w-24 h-24 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <FileText :size="40" class="text-gray-300" />
          </div>
          <h3 class="text-2xl font-bold mb-3" style="color: #305C7D">Aucune réservation</h3>
          <p class="text-gray-500 mb-8 max-w-sm mx-auto">
            Vous n'avez pas encore de réservations correspondant à ce statut.
          </p>
          <button 
            @click="selectedStatus = 'all'" 
            class="px-8 py-3 bg-[#305C7D] text-white rounded-2xl font-bold hover:shadow-lg transition-all transform hover:scale-105"
          >
            Voir tout
          </button>
        </div>

        <!-- Demands List (Ultra-Compact) -->
        <div v-else class="space-y-3 animate-fade-in">
          <div
            v-for="(demand, index) in filteredDemands"
            :key="demand.id"
            class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-3 transition-all duration-300 hover:shadow-md hover:border-gray-200 relative overflow-hidden"
            :style="{ 
              animationDelay: `${index * 50}ms`,
              borderLeft: `4px solid ${getBorderColor(demand.status)}`
            }"
          >
            <div class="relative z-10 flex items-center gap-4">
              <!-- Minimalist Avatar -->
              <div class="relative flex-shrink-0">
                <img
                  :src="demand.intervenant.image || 'https://via.placeholder.com/150'"
                  :alt="demand.intervenant.name"
                  class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm"
                />
                <div 
                  class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white"
                  :style="{ backgroundColor: getStatusBgColor(demand.status) }"
                ></div>
              </div>

              <!-- Compact Info -->
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mb-0.5">
                  <h4 class="font-bold text-[#305C7D] truncate">
                    {{ demand.service }}
                  </h4>
                  <div class="px-2 py-0.5 rounded-full text-[8px] uppercase tracking-tighter font-black" :style="{ backgroundColor: getStatusBgColor(demand.status) + '15', color: getStatusBgColor(demand.status) }">
                    {{ getStatusLabel(demand.status) }}
                  </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-500">
                  <div class="flex items-center gap-1">
                    <span class="font-semibold text-gray-700">{{ demand.intervenant.name }}</span>
                  </div>
                  <div class="flex items-center gap-1 opacity-70">
                    <Calendar :size="10" />
                    {{ formatDate(demand.date) }}
                  </div>
                  <div class="flex items-center gap-1 opacity-70">
                    <Clock :size="10" />
                    {{ demand.time }}
                  </div>
                  <div class="flex items-center gap-1 opacity-70 hidden sm:flex">
                    <MapPin :size="10" />
                    {{ demand.quartier }}
                  </div>
                </div>
              </div>

              <!-- Price & Action -->
              <div class="flex items-center gap-4 pl-4 border-l border-gray-50">
                <div class="text-right hidden sm:block">
                  <div class="text-lg font-black text-[#1a5fa3]">
                    {{ demand.finalCost || demand.estimatedCost || 0 }}<span class="text-[10px] ml-0.5">DH</span>
                  </div>
                </div>
                <button
                  @click="viewDemandDetails(demand)"
                  class="p-2.5 bg-gray-50 text-gray-400 rounded-xl hover:bg-[#305C7D] hover:text-white transition-all shadow-sm"
                  title="Voir détails"
                >
                  <Eye :size="16" />
                </button>
              </div>
            </div>

            <!-- Ultra-Tiny Rating Link -->
            <div
              v-if="demand.status === 'completed' && !demand.rating && canRate(demand)"
              class="mt-2 pt-2 border-t border-dashed border-gray-100 flex items-center justify-between text-[10px]"
            >
              <span class="text-orange-600 font-bold">Prêt pour évaluation !</span>
              <button
                @click="openRatingModal(demand)"
                class="text-[#305C7D] font-black hover:underline"
              >
                Noter maintenant →
              </button>
            </div>
          </div>
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
  AlertCircle,
  FileText,
  Star,
  Eye,
  ArrowLeft
} from 'lucide-vue-next';
import RateIntervenantModal from './RateIntervenantModal.vue';
import DemandDetailsModal from './DemandDetailsModal.vue';
import interventionService from '@/services/interventionService';
import api from '@/services/api';
import authService from '@/services/authService';

export default {
  name: 'ClientReservationsPage',
  components: {
    Calendar,
    Clock,
    MapPin,
    User,
    MessageCircle,
    AlertCircle,
    FileText,
    Star,
    Eye,
    ArrowLeft,
    RateIntervenantModal,
    DemandDetailsModal
  },
  emits: ['back'],
  data() {
    return {
      selectedStatus: 'all',
      selectedDemand: null,
      showDetails: false,
      demandToRate: null,
      demands: [],
      loading: false,
      error: null,
      clientId: null,
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
        all: { label: 'Toutes', color: '#305C7D', count: this.statusCounts.all },
        pending: { label: 'En attente', color: '#FCD34D', count: this.statusCounts.pending },
        accepted: { label: 'Acceptées', color: '#4682B4', count: this.statusCounts.accepted },
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
  async mounted() {
    // Récupérer l'ID du client depuis l'utilisateur connecté
    const user = authService.getUserSync();
    if (user && user.client) {
      this.clientId = user.client.id || user.client_id || user.id;
      await this.loadDemands();
    } else {
      this.error = 'Vous devez être connecté pour voir vos réservations';
    }
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
        const response = await interventionService.getByClientId(this.clientId);
        
        let demandsData = [];
        if (response.data?.data && Array.isArray(response.data.data)) {
          demandsData = response.data.data;
        } else if (Array.isArray(response.data)) {
          demandsData = response.data;
        } else {
          demandsData = [];
        }
        
        this.demands = demandsData;
        
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
        this.error = error.response?.data?.message || error.message || 'Erreur lors du chargement des réservations';
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
        accepted: '#4682B4',
        pending: '#FCD34D',
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
      this.showDetails = false;
    },
    openRatingModalFromDetails() {
      if (this.selectedDemand) {
        this.openRatingModal(this.selectedDemand);
      }
    },
    async handleRatingSubmit(ratingData) {
      if (!this.demandToRate) return;

      try {
        await api.post(`interventions/${this.demandToRate.id}/evaluations`, ratingData);
        await this.loadDemands();
        this.demandToRate = null;
        alert('Merci pour votre évaluation ! Elle a été publiée avec succès.');
      } catch (error) {
        console.error('Error submitting rating:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de la publication de l\'évaluation. Veuillez réessayer.';
        alert(errorMessage);
      }
    }
  }
};
</script>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out;
}

.animate-fade-in-up-delayed {
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float 6s ease-in-out infinite 1s;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

