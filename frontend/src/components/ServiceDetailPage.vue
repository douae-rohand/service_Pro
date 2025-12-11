<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
        <p class="mt-4 text-gray-600">Chargement du service...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button
          @click="loadServiceData"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Réessayer
        </button>
        <button
          @click="$emit('back')"
          class="ml-4 px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
          Retour
        </button>
      </div>
    </div>

    <!-- Content -->
    <template v-else-if="currentService">
      <!-- Header -->
      <div class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center gap-4">
            <button
              @click="$emit('back')"
              @mouseenter="hoverBackButton = true"
              @mouseleave="hoverBackButton = false"
              class="flex items-center gap-2 text-gray-600 hover:text-white transition-all mb-4 px-4 py-2 rounded-lg"
              :style="{ backgroundColor: hoverBackButton ? currentService.color : 'transparent' }"
            >
              <ArrowLeft :size="20" />
            </button>
            <div>
              <h1 class="text-5xl" :style="{ color: currentService.color }">
                Service de {{ currentService.name }}
              </h1>
              <p class="text-gray-700 mt-2 text-lg">
                Découvrez toutes nos tâches et nos meilleurs intervenants
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Tasks Section -->
      <section class="mb-16">
        <div class="text-center mb-12">
          <h2 class="text-5xl mb-4" :style="{ color: currentService.color }">
            Sous-service disponibles
          </h2>
          <p class="text-gray-600 text-lg">
            Choisissez parmi notre large gamme de services professionnels
          </p>
        </div>
        <div v-if="currentService.tasks && currentService.tasks.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(task, index) in currentService.tasks"
            :key="task.id"
            class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all transform hover:scale-105 cursor-pointer group"
            :style="{ animation: `fadeInUp 0.6s ease-out ${index * 0.1}s both` }"
          >
            <!-- Image -->
            <div class="relative h-56 overflow-hidden">
              <img
                :src="task.image"
                :alt="task.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            </div>
            
            <!-- Content -->
            <div class="p-6">
              <h3 class="text-xl mb-2" :style="{ color: currentService.color }">
                {{ task.name }}
              </h3>
              <p class="text-gray-600 text-sm mb-4">{{ task.description }}</p>
              
              <button 
                class="w-full py-3 rounded-lg text-white transition-all hover:opacity-90 flex items-center justify-center gap-2"
                :style="{ backgroundColor: currentService.color }"
                @click="$emit('task-click', task.name)"
              >
                <CheckCircle :size="18" />
                Réserver
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <p class="text-gray-600">Aucun sous-service disponible pour le moment.</p>
        </div>
      </section>

      <!-- Intervenants Section -->
      <section class="bg-white rounded-2xl p-8 shadow-lg">
        <div class="text-center mb-10">
          <h2 class="text-4xl mb-3" :style="{ color: currentService.color }">
            Nos meilleurs intervenants
          </h2>
          <p class="text-gray-600">
            Des professionnels vérifiés et expérimentés à votre service
          </p>
        </div>
        <div v-if="currentService.intervenants && currentService.intervenants.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="intervenant in currentService.intervenants"
            :key="intervenant.id"
            class="bg-gray-50 rounded-xl shadow-md hover:shadow-lg transition-all overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-start gap-4 mb-4">
                <img
                  :src="intervenant.image"
                  :alt="intervenant.name"
                  class="w-16 h-16 rounded-full object-cover border-3"
                  :style="{ borderColor: currentService.color }"
                />
                <div class="flex-1">
                  <h3 class="text-lg">{{ intervenant.name }}</h3>
                  <span
                    v-if="intervenant.verified"
                    class="text-xs px-2 py-1 rounded-full inline-block mt-1"
                    :style="{
                      backgroundColor: currentService.color,
                      color: 'white',
                    }"
                  >
                    ✓ Vérifié
                  </span>
                  <div class="flex items-center gap-2 mt-2">
                    <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span>{{ intervenant.rating }}</span>
                    <span class="text-gray-500 text-sm">({{ intervenant.reviewCount }} avis)</span>
                  </div>
                </div>
              </div>

              <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-gray-700 text-sm">
                  <MapPin :size="16" :style="{ color: currentService.color }" />
                  <span>{{ intervenant.location }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-700">
                  <Coins :size="16" :style="{ color: currentService.color }" />
                  <span>{{ intervenant.hourlyRate }}DH/heure</span>
                </div>
              </div>

              <div class="mb-4">
                <p class="text-xs text-gray-500 mb-2">Spécialités :</p>
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(specialty, index) in intervenant.specialties"
                    :key="index"
                    class="text-xs px-2 py-1 rounded-md"
                    :style="{
                      backgroundColor: currentService.color + '20',
                      color: currentService.color
                    }"
                  >
                    {{ specialty }}
                  </span>
                </div>
              </div>

              <button
                @click="$emit('view-profile', intervenant.id)"
                class="w-full text-white py-2.5 rounded-lg transition-all hover:opacity-90"
                :style="{ backgroundColor: currentService.color }"
              >
                Voir le profil
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <p class="text-gray-600">Aucun intervenant disponible pour le moment.</p>
        </div>
        
        <!-- Button to view all intervenants -->
        <div class="text-center mt-12">
          <button
            @click="$emit('view-all-intervenants')"
            @mouseenter="hoverViewAll = true"
            @mouseleave="hoverViewAll = false"
            class="px-10 py-4 rounded-xl text-white text-lg transition-all transform hover:scale-105 hover:shadow-xl inline-flex items-center gap-3"
            :style="{ 
              backgroundColor: currentService.color,
              boxShadow: hoverViewAll 
                ? `0 15px 35px ${currentService.color}60`
                : `0 10px 25px ${currentService.color}40`
            }"
          >
            {{ currentService.name === 'Ménage' ? 'Voir tous les intervenants ménage' : 'Voir tous les jardiniers' }}
            <ArrowLeft :size="20" class="rotate-180" />
          </button>
        </div>
      </section>
      </div>
    </template>
  </div>
</template>

<script>
import { ArrowLeft, Star, Coins, MapPin, CheckCircle } from 'lucide-vue-next';
import serviceService from '@/services/serviceService';

export default {
  name: 'ServiceDetailPage',
  components: {
    ArrowLeft,
    Star,
    Coins,
    MapPin,
    CheckCircle
  },
  props: {
    service: {
      type: [String, Number],
      required: true
    }
  },
  emits: ['back', 'view-all-intervenants', 'view-profile', 'task-click'],
  data() {
    return {
      hoverBackButton: false,
      hoverViewAll: false,
      loading: true,
      error: null,
      serviceData: null,
      taches: [],
      // Configuration par défaut pour les couleurs
      serviceConfig: {
        'Jardinage': {
          color: '#92B08B',
        },
        'Ménage': {
          color: '#4682B4',
        },
      },
      // Données d'intervenants par défaut (peut être chargé depuis l'API plus tard)
      intervenants: [],
    };
  },
  async mounted() {
    await this.loadServiceData();
  },
  watch: {
    service() {
      this.loadServiceData();
    }
  },
  computed: {
    currentService() {
      if (!this.serviceData) return null;
      
      const serviceName = this.serviceData.nom_service;
      const config = this.serviceConfig[serviceName] || { color: '#6B7280' };
      
      return {
        id: this.serviceData.id,
        name: serviceName,
        description: this.serviceData.description || '',
        color: config.color,
        tasks: this.taches.map(tache => ({
          id: tache.id,
          name: tache.nom_tache,
          description: tache.description || '',
          image: tache.image_url || this.getDefaultImageForTask(tache.nom_tache),
        })),
        intervenants: this.intervenants,
      };
    }
  },
  methods: {
    async loadServiceData() {
      try {
        this.loading = true;
        this.error = null;
        
        // Récupérer le service
        const serviceId = typeof this.service === 'string' && isNaN(this.service) 
          ? this.getServiceIdByName(this.service) 
          : this.service;
        
        const serviceResponse = await serviceService.getById(serviceId);
        this.serviceData = serviceResponse.data;
        
        // Récupérer les taches (sous-services)
        const tachesResponse = await serviceService.getTaches(serviceId);
        this.taches = tachesResponse.data;
        
      } catch (err) {
        console.error('Erreur lors du chargement du service:', err);
        this.error = err.message || 'Impossible de charger les données du service. Veuillez réessayer.';
      } finally {
        this.loading = false;
      }
    },
    
    // Helper pour obtenir un ID de service par nom (compatibilité avec l'ancien code)
    getServiceIdByName(name) {
      // Cette méthode pourrait faire un appel API si nécessaire
      // Pour l'instant, on essaie de trouver dans la liste des services chargés
      return name;
    },
    
    // Helper pour obtenir une image par défaut si aucune image_url n'est fournie
    getDefaultImageForTask(taskName) {
      // Images par défaut basées sur le nom de la tâche
      const defaultImages = {
        'Tonte de pelouse': 'https://images.unsplash.com/photo-1723811898182-aff0c2eca53f?w=1080',
        'Taille de haies': 'https://images.unsplash.com/photo-1558904541-efa843a96f01?w=1080',
        'Plantation de fleurs': 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=1080',
        'Élagage d\'arbres': 'https://images.unsplash.com/photo-1626828476637-5bd713ef9f22?w=1080',
        'Désherbage': 'https://images.unsplash.com/photo-1706033844083-91d7a6fdab12?w=1080',
        'Entretien de potager': 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1080',
        'Ménage résidentiel & régulier': 'https://images.unsplash.com/photo-1758273238415-01ec03d9ef27?w=1080',
        'Nettoyage en profondeur (Deep Cleaning)': 'https://images.unsplash.com/photo-1737372805905-be0b91ec86fb?w=1080',
        'Nettoyage spécial : déménagement & post-travaux': 'https://images.unsplash.com/photo-1631304137068-16117132ee8b?w=1080',
        'Lavage vitres & surfaces spécialisées': 'https://images.unsplash.com/photo-1761689502577-0013be84f1bf?w=1080',
        'Nettoyage mobilier & textiles': 'https://images.unsplash.com/photo-1654511830326-63a577771a7e?w=1080',
        'Nettoyage professionnel (bureaux & commerces)': 'https://images.unsplash.com/photo-1762235634143-6d350fe349e8?w=1080',
      };
      
      return defaultImages[taskName] || 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1080';
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
</style>