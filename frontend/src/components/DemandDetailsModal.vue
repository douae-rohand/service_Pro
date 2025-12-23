<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
    <!-- Overlay -->
    <div 
      class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity animate-fade-in"
      @click="$emit('close')"
    ></div>

    <!-- Modal Container -->
    <div 
      class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col animate-scale-in"
    >
      <!-- Header -->
      <header class="flex items-center justify-between p-6 border-b border-gray-100 bg-white shrink-0">
        <div class="flex items-center gap-4">
          <img
            :src="demand.intervenant.image || 'https://via.placeholder.com/150'"
            :alt="demand.intervenant.name"
            class="w-12 h-12 rounded-full object-cover border-2 border-gray-100"
          />
          <div>
            <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ demand.service }}</h2>
            <p class="text-sm text-gray-500 font-medium">{{ demand.intervenant.name }}</p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
        >
          <X :size="24" />
        </button>
      </header>

      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto p-6 space-y-8 no-scrollbar">
        <!-- Status -->
        <div class="flex items-center">
          <span 
            class="px-3 py-1 rounded-full text-xs font-bold border"
            :style="{ 
              backgroundColor: getStatusColor(demand.status) + '10', 
              color: getStatusColor(demand.status),
              borderColor: getStatusColor(demand.status) + '30'
            }"
          >
            {{ getStatusLabel(demand.status) }}
          </span>
        </div>

        <!-- Main Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100/50">
            <h3 class="text-xs font-black text-[#305C7D] uppercase tracking-widest mb-4 flex items-center gap-2">
              <Calendar :size="14" class="text-gray-400" />
              Planification
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Date</span>
                <span class="font-bold text-gray-800">{{ formatDate(demand.date) }}</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Heure</span>
                <span class="font-bold text-gray-800">{{ demand.time }}</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Tâche</span>
                <span class="font-bold text-gray-800">{{ demand.task }}</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100/50">
            <h3 class="text-xs font-black text-[#305C7D] uppercase tracking-widest mb-4 flex items-center gap-2">
              <MapPin :size="14" class="text-gray-400" />
              Localisation
            </h3>
            <div class="space-y-3">
              <div class="text-sm font-bold text-gray-800">{{ demand.address }}</div>
              <div class="text-sm text-gray-500">{{ demand.quartier }}, Tetouan</div>
            </div>
          </div>
        </div>

        <!-- Financial Summary (Integrated) -->
        <div class="bg-white rounded-2xl border-2 border-gray-100 p-6 space-y-4">
          <h3 class="text-xs font-black text-[#305C7D] uppercase tracking-widest">Coût de l'intervention</h3>
          
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Estimation de base</span>
              <span class="font-bold text-gray-800">{{ demand.estimatedCost }} DH</span>
            </div>

            <template v-if="demand.status === 'completed' && demand.invoice">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Main d'œuvre réelle</span>
                <span class="font-bold text-gray-800">{{ demand.invoice.laborCost }} DH</span>
              </div>
              <div v-if="demand.invoice.materialsCost > 0" class="flex justify-between text-sm">
                <span class="text-gray-500">Frais de matériel</span>
                <span class="font-bold text-gray-800">{{ demand.invoice.materialsCost }} DH</span>
              </div>
            </template>

            <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
              <span class="font-bold text-gray-900">{{ demand.status === 'completed' ? 'Montant Total' : 'Total Estimé' }}</span>
              <div class="text-xl font-black text-[#305C7D]">
                {{ demand.finalCost || calculateTotal() || demand.estimatedCost }} DH
              </div>
            </div>
            
            <div v-if="['completed', 'accepted'].includes(demand.status)" class="pt-2 flex justify-end">
              <button 
                @click="showInvoice = true"
                class="text-sm font-semibold text-[#305C7D] hover:underline flex items-center gap-1"
              >
                <FileText :size="14" />
                {{ demand.status === 'completed' ? 'Voir la facture' : 'Voir le devis / facture' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <footer class="p-6 border-t border-gray-100 bg-gray-50/30 flex flex-wrap gap-3 justify-end shrink-0">
        <button
          v-if="demand.status === 'completed' && !demand.rating && canRate(demand)"
          @click="openRatingModal"
          class="px-6 py-2.5 bg-[#FEE347] text-[#305C7D] font-bold rounded-xl text-sm transition-transform hover:scale-105"
        >
          Noter l'intervenant
        </button>
        <button
          v-if="demand.status === 'completed'"
          @click="$emit('open-reclamation')"
          class="px-6 py-2.5 bg-red-50 text-red-600 font-bold rounded-xl text-sm transition-transform hover:scale-105"
        >
          Signaler un problème
        </button>
        <button
          @click="$emit('close')"
          class="px-6 py-2.5 bg-[#305C7D] text-white font-bold rounded-xl text-sm transition-transform hover:scale-105 shadow-md shadow-[#305C7D]/20"
        >
          Fermer
        </button>
      </footer>
    </div>

    <!-- Lightbox -->
    <div
      v-if="selectedPhoto"
      class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
      @click="selectedPhoto = null"
    >
      <button class="absolute top-6 right-6 text-white p-2">
        <X :size="32" />
      </button>
      <img
        :src="selectedPhoto"
        class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl animate-scale-in"
      />
    </div>

    <!-- Invoice Modal -->
    <InvoiceModal
      v-if="showInvoice"
      :invoice="computedInvoice"
      :demand="demand"
      @close="showInvoice = false"
    />
  </div>
</template>

<script>
import {
  X,
  Calendar,
  Clock,
  MapPin,
  User,
  FileText,
  Star,
  DollarSign,
  CheckCircle,
  Package,
  AlertCircle,
  Download,
  Camera,
  ZoomIn
} from 'lucide-vue-next';
import InvoiceModal from './InvoiceModal.vue';

export default {
  name: 'DemandDetailsModal',
  components: {
    X,
    Calendar,
    Clock,
    MapPin,
    User,
    FileText,
    Star,
    DollarSign,
    CheckCircle,
    Package,
    AlertCircle,
    Download,
    Camera,
    ZoomIn,
    InvoiceModal
  },
  props: {
    demand: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'open-rating', 'open-reclamation'],
  data() {
    return {
      selectedPhoto: null,
      imageErrors: {},
      showInvoice: false
    };
  },
  computed: {
    computedInvoice() {
      if (this.demand.invoice) return this.demand.invoice;
      
      // Virtual invoice for accepted/in-progress/completed if missing
      return {
        date: this.demand.date,
        actualDuration: 'Estimée',
        laborCost: this.demand.estimatedCost || 0,
        materialsCost: 0,
        materialsProvided: [],
        paymentDate: null,
        isProvisional: this.demand.status !== 'completed'
      };
    }
  },
  methods: {
    getStatusColor(status) {
      const colors = {
        pending: '#FEE347',
        accepted: '#1A5FA3',
        'in-progress': '#92B08B',
        completed: '#92B08B',
        cancelled: '#6B7280',
        refused: '#E8793F'
      };
      return colors[status] || '#6B7280';
    },
    getStatusLabel(status) {
      const labels = {
        pending: 'En attente',
        accepted: 'Acceptée',
        'in-progress': 'En cours',
        completed: 'Complétée',
        cancelled: 'Annulée',
        refused: 'Refusée'
      };
      return labels[status] || status;
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    },
    calculateTotal() {
      if (!this.demand.invoice) return 0;
      return (this.demand.invoice.laborCost || 0) + (this.demand.invoice.materialsCost || 0);
    },
    openRatingModal() {
      this.$emit('open-rating');
    },
    canRate(demand) {
      if (demand.status !== 'completed' || demand.rating) return false;
      if (!demand.completedAt) return false;
      const completedDate = new Date(demand.completedAt);
      const today = new Date();
      const daysDiff = Math.floor((today.getTime() - completedDate.getTime()) / (1000 * 3600 * 24));
      return daysDiff <= 7;
    },
    handleImageError(event) {
      const img = event.target;
      if (!this.imageErrors[img.src]) {
        this.imageErrors[img.src] = true;
        img.src = 'https://via.placeholder.com/400x300?text=Indisponible';
      }
    }
  }
};
</script>

<style scoped>
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes scaleIn { 
  from { opacity: 0; transform: scale(0.98) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.animate-fade-in { animation: fadeIn 0.3s ease-out both; }
.animate-scale-in { animation: scaleIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.cursor-zoom-in { cursor: zoom-in; }
</style>
