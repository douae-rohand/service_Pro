<template>
  <div class="max-w-6xl mx-auto">
    <!-- Status Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="filter in statusFilters"
          :key="filter.value"
          @click="selectedStatus = filter.value"
          class="px-4 py-2 rounded-lg transition-all"
          :class="
            selectedStatus === filter.value
              ? 'text-white shadow-md'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          "
          :style="{
            backgroundColor: selectedStatus === filter.value ? '#1A5FA3' : ''
          }"
        >
          {{ filter.label }} ({{ filter.count }})
        </button>
      </div>
    </div>

    <!-- Demands List -->
    <div class="space-y-4">
      <div
        v-for="demand in filteredDemands"
        :key="demand.id"
        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all overflow-hidden"
      >
        <div class="p-6">
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
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-xl" style="color: #2f4f4f">
                    {{ demand.service }} - {{ demand.task }}
                  </h3>
                  <span
                    class="px-3 py-1 rounded-full text-white text-sm flex items-center gap-1"
                    :style="{ backgroundColor: getStatusConfig(demand.status).color }"
                  >
                    <component :is="getStatusConfig(demand.status).icon" :size="14" />
                    {{ getStatusConfig(demand.status).label }}
                  </span>
                </div>
                <p class="text-gray-600 mb-1">Intervenant: {{ demand.intervenant.name }}</p>
                <p class="text-sm text-gray-500">
                  Demande créée le {{ formatDate(demand.createdAt) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="flex items-center gap-2 text-gray-600">
              <Calendar :size="18" style="color: #1a5fa3" />
              <span class="text-sm">
                {{ formatDate(demand.date) }}
              </span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <Clock :size="18" style="color: #1a5fa3" />
              <span class="text-sm">{{ demand.time }} ({{ demand.duration }}h)</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <MapPin :size="18" style="color: #1a5fa3" />
              <span class="text-sm">{{ demand.quartier }}</span>
            </div>
          </div>

          <!-- Description -->
          <p class="text-sm text-gray-600 mb-4 line-clamp-2">
            {{ demand.description }}
          </p>

          <!-- Intervenant Response -->
          <div
            v-if="demand.intervenantResponse"
            class="mb-4 p-3 rounded-lg border"
            style="background-color: #e8f5e9; border-color: #92b08b"
          >
            <p class="text-sm" style="color: #2f4f4f">
              <MessageCircle :size="16" class="inline mr-2" style="color: #1a5fa3" />
              Réponse de l'intervenant:
            </p>
            <p class="text-sm text-gray-600 mt-1 ml-6">
              {{ demand.intervenantResponse }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div class="flex items-center gap-4">
              <span class="text-gray-600">
                {{ demand.status === 'completed' && demand.finalCost ? 'Coût final:' : 'Coût estimé:' }}
              </span>
              <span class="text-xl px-3 py-1 rounded text-white" style="background-color: #1a5fa3">
                {{ demand.status === 'completed' && demand.finalCost ? demand.finalCost : demand.estimatedCost }} DH
              </span>
            </div>

            <div class="flex gap-2">
              <button
                @click="handleViewDetails(demand)"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2"
              >
                <FileText :size="16" />
                Détails
              </button>

              <button
                v-if="demand.status === 'pending'"
                class="px-4 py-2 rounded-lg text-white transition-all hover:shadow-lg"
                style="background-color: #e8793f"
              >
                Annuler
              </button>

              <button
                v-if="demand.canRate && !demand.rating"
                class="px-4 py-2 rounded-lg text-white transition-all hover:shadow-lg flex items-center gap-2"
                style="background-color: #92b08b"
              >
                <MessageCircle :size="16" />
                Évaluer
              </button>

              <div
                v-if="demand.rating"
                class="px-4 py-2 rounded-lg flex items-center gap-2 border"
                style="background-color: #e8f5e9; border-color: #92b08b"
              >
                <span class="text-sm" style="color: #2f4f4f">Évalué: {{ demand.rating }} ⭐</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Demands -->
    <div v-if="filteredDemands.length === 0" class="text-center py-12 bg-white rounded-lg shadow-md">
      <div class="text-gray-400 mb-4">
        <FileText :size="64" class="mx-auto" />
      </div>
      <h3 class="text-xl mb-2" style="color: #2f4f4f">
        Aucune demande
      </h3>
      <p class="text-gray-600">
        Vous n'avez pas encore de demande avec ce statut
      </p>
    </div>

    <!-- Details Modal -->
    <div
      v-if="showDetails && selectedDemand"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex items-center justify-between">
          <h2 class="text-2xl" style="color: #2f4f4f">
            Détails de la demande #{{ selectedDemand.id }}
          </h2>
          <button
            @click="showDetails = false"
            class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <XCircle :size="24" />
          </button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Status -->
          <div>
            <span
              class="px-4 py-2 rounded-full text-white flex items-center gap-2 w-fit"
              :style="{ backgroundColor: getStatusConfig(selectedDemand.status).color }"
            >
              <component :is="getStatusConfig(selectedDemand.status).icon" :size="16" />
              {{ getStatusConfig(selectedDemand.status).label }}
            </span>
          </div>

          <!-- Service Info -->
          <div>
            <h3 class="text-lg mb-3" style="color: #2f4f4f">Service</h3>
            <div class="space-y-2">
              <p><strong>Type:</strong> {{ selectedDemand.service }}</p>
              <p><strong>Tâche:</strong> {{ selectedDemand.task }}</p>
            </div>
          </div>

          <!-- Intervenant Info -->
          <div>
            <h3 class="text-lg mb-3" style="color: #2f4f4f">Intervenant</h3>
            <div class="flex items-center gap-4">
              <img
                :src="selectedDemand.intervenant.image"
                :alt="selectedDemand.intervenant.name"
                class="w-16 h-16 rounded-full object-cover border-2"
                style="border-color: #92b08b"
              />
              <div>
                <p><strong>{{ selectedDemand.intervenant.name }}</strong></p>
                <p class="text-sm text-gray-600">{{ selectedDemand.intervenant.phone }}</p>
              </div>
            </div>
          </div>

          <!-- Schedule -->
          <div>
            <h3 class="text-lg mb-3" style="color: #2f4f4f">Rendez-vous</h3>
            <div class="space-y-2">
              <p><strong>Date:</strong> {{ formatDate(selectedDemand.date) }}</p>
              <p><strong>Heure:</strong> {{ selectedDemand.time }}</p>
              <p><strong>Durée estimée:</strong> {{ selectedDemand.duration }} heure(s)</p>
            </div>
          </div>

          <!-- Address -->
          <div>
            <h3 class="text-lg mb-3" style="color: #2f4f4f">Adresse</h3>
            <div class="space-y-2">
              <p>{{ selectedDemand.address }}</p>
              <p>{{ selectedDemand.quartier }}</p>
            </div>
          </div>

          <!-- Description -->
          <div>
            <h3 class="text-lg mb-3" style="color: #2f4f4f">Description</h3>
            <p class="text-gray-600">{{ selectedDemand.description }}</p>
          </div>

          <!-- Cost -->
          <div>
            <h3 class="text-lg mb-3" style="color: #2f4f4f">Coût</h3>
            <div class="space-y-2">
              <p><strong>Coût estimé:</strong> {{ selectedDemand.estimatedCost }} DH</p>
              <p v-if="selectedDemand.finalCost">
                <strong>Coût final:</strong> {{ selectedDemand.finalCost }} DH
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
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
  FileText
} from 'lucide-vue-next';

export default {
  name: 'MyDemandsTab',
  components: {
    Calendar,
    Clock,
    MapPin,
    User,
    MessageCircle,
    CheckCircle,
    XCircle,
    AlertCircle,
    FileText
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
      demands: [
        {
          id: 1,
          service: 'Jardinage',
          task: 'Tonte de pelouse',
          intervenant: {
            name: 'Mohammed El Fassi',
            image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop',
            phone: '+212 6 12 34 56 78'
          },
          date: '2024-12-08',
          time: '09:00',
          status: 'accepted',
          address: '12 Rue Hassan II, Immeuble A',
          quartier: 'Tetouan Centre',
          estimatedCost: 180,
          duration: 2,
          description: "Tonte de pelouse d'environ 40m², avec ramassage des déchets.",
          createdAt: '2024-12-02',
          intervenantResponse: "Demande acceptée. Je serai là à l'heure prévue avec tout le matériel nécessaire.",
          canRate: false
        },
        {
          id: 2,
          service: 'Ménage',
          task: 'Nettoyage complet',
          intervenant: {
            name: 'Leila Mansouri',
            image: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop',
            phone: '+212 6 98 76 54 32'
          },
          date: '2024-12-03',
          time: '14:00',
          status: 'completed',
          address: '45 Avenue Mohammed V, Apt 5',
          quartier: 'Martil',
          estimatedCost: 270,
          finalCost: 250,
          duration: 3,
          description: "Nettoyage complet d'un appartement de 3 pièces.",
          createdAt: '2024-11-28',
          canRate: true,
          rating: 5
        },
        {
          id: 3,
          service: 'Jardinage',
          task: 'Taille de haies',
          intervenant: {
            name: 'Amina Chakir',
            image: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop',
            phone: '+212 6 55 44 33 22'
          },
          date: '2024-12-05',
          time: '10:00',
          status: 'pending',
          address: '78 Rue Ibn Khaldoun',
          quartier: 'Mdiq',
          estimatedCost: 300,
          duration: 3,
          description: 'Taille de haies sur environ 15 mètres linéaires.',
          createdAt: '2024-12-01',
          canRate: false
        },
        {
          id: 4,
          service: 'Ménage',
          task: 'Nettoyage vitres',
          intervenant: {
            name: 'Fatima Benali',
            image: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop',
            phone: '+212 6 77 88 99 00'
          },
          date: '2024-11-25',
          time: '11:00',
          status: 'cancelled',
          address: '23 Boulevard Al Massira',
          quartier: 'Tetouan Centre',
          estimatedCost: 120,
          duration: 2,
          description: 'Nettoyage des vitres intérieures et extérieures.',
          createdAt: '2024-11-20',
          canRate: false
        },
        {
          id: 5,
          service: 'Jardinage',
          task: 'Désherbage',
          intervenant: {
            name: 'Karim Tazi',
            image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop',
            phone: '+212 6 33 22 11 00'
          },
          date: '2024-12-10',
          time: '08:00',
          status: 'in-progress',
          address: '56 Rue Al Andalous',
          quartier: 'Fnideq',
          estimatedCost: 140,
          duration: 2,
          description: 'Désherbage du jardin et allées.',
          createdAt: '2024-11-30',
          canRate: false
        }
      ]
    };
  },
  computed: {
    statusConfig() {
      return {
        pending: { label: 'En attente', color: '#FEE347', icon: 'AlertCircle' },
        accepted: { label: 'Acceptée', color: '#A5D4E6', icon: 'CheckCircle' },
        'in-progress': { label: 'En cours', color: '#1A5FA3', icon: 'Clock' },
        completed: { label: 'Terminée', color: '#92B08B', icon: 'CheckCircle' },
        cancelled: { label: 'Annulée', color: '#9CA3AF', icon: 'XCircle' },
        rejected: { label: 'Refusée', color: '#E8793F', icon: 'XCircle' }
      };
    },
    statusFilters() {
      return [
        { value: 'all', label: 'Toutes', count: this.demands.length },
        { value: 'pending', label: 'En attente', count: this.demands.filter(d => d.status === 'pending').length },
        { value: 'accepted', label: 'Acceptées', count: this.demands.filter(d => d.status === 'accepted').length },
        { value: 'in-progress', label: 'En cours', count: this.demands.filter(d => d.status === 'in-progress').length },
        { value: 'completed', label: 'Terminées', count: this.demands.filter(d => d.status === 'completed').length }
      ];
    },
    filteredDemands() {
      return this.selectedStatus === 'all'
        ? this.demands
        : this.demands.filter(d => d.status === this.selectedStatus);
    }
  },
  methods: {
    getStatusConfig(status) {
      return this.statusConfig[status];
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('fr-FR');
    },
    handleViewDetails(demand) {
      this.selectedDemand = demand;
      this.showDetails = true;
    }
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>