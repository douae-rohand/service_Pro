<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-40">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <button
          @click="$emit('back')"
          class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-all"
        >
          <ArrowLeftIcon :size="20" />
          <span>Retour</span>
        </button>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="text-center py-16">
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button
          @click="$emit('back')"
          class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all"
        >
          Retour
        </button>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-if="!error" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Profile Header Card -->
      <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
        <div class="flex items-start gap-6">
          <!-- Profile Image -->
          <div class="flex-shrink-0 relative">
            <ImageWithFallback
              :src="intervenant.profileImage"
              :alt="intervenant.name"
              class="w-32 h-32 rounded-full object-cover"
            />
          </div>

          <!-- Profile Info -->
          <div class="flex-1">
            <h1 class="text-4xl mb-2">{{ intervenant.name }}</h1>
            <div class="flex items-center gap-3 text-gray-600 mb-3">
              <div class="flex items-center gap-1">
                <MapPinIcon :size="16" />
                <span>{{ intervenant.location }}</span>
              </div>
              <span>•</span>
              <span>{{ formatExperience(intervenant.experience) }} d'expérience</span>
            </div>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mb-4">
              <StarIcon
                v-for="star in 5"
                :key="star"
                :size="20"
                class="fill-yellow-400 text-yellow-400"
              />
              <span class="text-lg ml-1">{{ intervenant.rating }}</span>
              <span class="text-gray-600">({{ intervenant.reviewCount }} avis)</span>
            </div>
          </div>

          <!-- Favorite Button -->
          <button
            @click="handleFavoriteClick"
            class="px-5 py-2.5 rounded-lg border-2 transition-all flex items-center gap-2"
            :style="{ borderColor: primaryColor, color: primaryColor }"
          >
            <HeartIcon :size="18" :class="{ 'fill-current': isFavorite }" />
            Ajouter aux favoris
          </button>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="bg-white rounded-2xl shadow-sm mb-6">
        <div class="border-b border-gray-200">
          <div class="flex gap-2 px-4">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'px-6 py-4 transition-all relative',
                activeTab !== tab.id ? 'text-gray-600 hover:text-gray-900' : ''
              ]"
              :style="activeTab === tab.id ? { color: primaryColor } : {}"
            >
              {{ tab.label }}
              <div
                v-if="activeTab === tab.id"
                class="absolute bottom-0 left-0 right-0 h-0.5"
                :style="{ backgroundColor: primaryColor }"
              />
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="p-8">
          <!-- À propos Tab -->
          <div v-if="activeTab === 'apropos'">
            <h2 class="text-2xl mb-6">À propos de {{ intervenant.name.split(' ')[0] }}</h2>
            <div class="space-y-4 text-gray-700 leading-relaxed">
              <p v-for="(paragraph, index) in intervenant.bio" :key="index">
                {{ paragraph }}
              </p>
            </div>
          </div>

          <!-- Services Tab -->
          <div v-if="activeTab === 'services'">
            <h2 class="text-2xl mb-6">Services proposés</h2>
            <div class="space-y-6">
              <div
                v-for="serviceItem in intervenant.services"
                :key="serviceItem.id"
                class="border border-gray-200 rounded-xl p-6"
              >
                <h3 class="text-xl mb-3">{{ serviceItem.name }}</h3>
                <p class="text-gray-600 mb-4 leading-relaxed">
                  {{ serviceItem.description }}
                </p>
                <div class="flex items-center gap-3 mb-4">
                  <div class="flex items-center gap-2 text-gray-600">
                    <ClockIcon :size="18" :style="{ color: primaryColor }" />
                    <span>{{ serviceItem.duration }}</span>
                  </div>
                  <span class="text-lg font-bold" :style="{ color: primaryColor }">
                    {{ serviceItem.price }}DH/h
                  </span>
                </div>
                <div class="flex items-center justify-end">
                  <button
                    @click="handleBookingClick(null, serviceItem)"
                    class="px-8 py-3 rounded-lg text-white transition-all hover:opacity-90"
                    :style="{ backgroundColor: buttonColor }"
                  >
                    Réserver ce service
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Avis Tab -->
          <div v-if="activeTab === 'avis'">
            <h2 class="text-2xl mb-6">Avis & Évaluations</h2>
            
            <!-- Rating Summary -->
            <div class="flex flex-col md:flex-row gap-8 mb-8 pb-8 border-b border-gray-200">
              <div class="text-center">
                <div class="text-6xl mb-2">{{ intervenant.rating }}</div>
                <div class="flex items-center justify-center gap-1 mb-2">
                  <StarIcon
                    v-for="star in 5"
                    :key="star"
                    :size="20"
                    class="fill-yellow-400 text-yellow-400"
                  />
                </div>
                <p class="text-gray-600">{{ intervenant.reviewCount }} avis</p>
              </div>

              <!-- Rating Distribution -->
              <div class="flex-1">
                <div
                  v-for="item in ratingDistribution"
                  :key="item.stars"
                  class="flex items-center gap-3 mb-2"
                >
                  <span class="text-sm w-12">{{ item.stars }} ★</span>
                  <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all"
                      :style="{ 
                        width: item.percentage + '%', 
                        backgroundColor: primaryColor 
                      }"
                    />
                  </div>
                  <span class="text-sm text-gray-600 w-16">{{ item.percentage }}%</span>
                </div>
              </div>
            </div>

            <!-- Reviews List -->
            <div class="space-y-6">
              <div
                v-for="review in intervenant.reviews"
                :key="review.id"
                class="border-b border-gray-100 pb-6 last:border-0"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="review.clientAvatar" 
                      class="w-10 h-10 rounded-full object-cover border border-gray-100"
                    />
                    <div>
                      <h4 class="text-lg mb-1">{{ review.clientName }}</h4>
                      <p class="text-sm text-gray-500">
                        {{ formatDate(review.date) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1">
                    <StarIcon
                      v-for="star in 5"
                      :key="star"
                      :size="16"
                      :class="star <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                    />
                  </div>
                </div>
                <p class="text-gray-700 leading-relaxed">{{ review.comment }}</p>
              </div>
            </div>
          </div>

          <!-- Photos Tab -->
          <div v-if="activeTab === 'photos'">
            <h2 class="text-2xl mb-6">Photos des réalisations</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div
                v-for="(photo, index) in intervenant.photos"
                :key="index"
                class="relative aspect-video rounded-xl overflow-hidden cursor-pointer group"
                @click="selectedImage = photo"
              >
                <ImageWithFallback
                  :src="photo"
                  :alt="`Réalisation ${index + 1}`"
                  class="w-full h-full object-cover transition-transform group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                  <CameraIcon class="text-white opacity-0 group-hover:opacity-100 transition-opacity" :size="32" />
                </div>
              </div>
            </div>
          </div>

          <!-- Disponibilités Tab -->
          <div v-if="activeTab === 'disponibilites'">
            <h2 class="text-2xl mb-6">Disponibilités</h2>
            <div class="space-y-3 mb-8">
              <div
                v-for="(day, index) in intervenant.availability"
                :key="index"
                class="flex items-center justify-between py-4 border-b border-gray-200 last:border-0"
              >
                <div class="flex flex-col">
                  <span
                    class="text-lg font-medium"
                    :style="{ color: day.available ? primaryColor : '#9CA3AF' }"
                  >
                    {{ day.day }}
                  </span>
                  <span class="text-xs uppercase tracking-wider text-gray-400">
                    {{ day.type === 'reguliere' ? 'Chaque semaine' : 'Date spécifique' }}
                  </span>
                </div>
                <div class="flex items-center gap-3">
                  <div 
                    class="px-3 py-1 rounded-full text-xs font-bold"
                    :style="{ 
                      backgroundColor: day.available ? primaryColor + '15' : '#F3F4F6',
                      color: day.available ? primaryColor : '#9CA3AF'
                    }"
                  >
                    {{ day.available ? 'Ouvert' : 'Fermé' }}
                  </div>
                  <span
                    class="text-lg font-bold"
                    :style="{ color: day.available ? '#374151' : '#9CA3AF' }"
                  >
                    {{ day.available ? day.hours : 'Non disponible' }}
                  </span>
                </div>
              </div>
            </div>
            <button
              class="w-full px-8 py-4 rounded-lg text-white text-lg transition-all hover:opacity-90"
              :style="{ backgroundColor: buttonColor }"
            >
              Demander une disponibilité spécifique
            </button>
          </div>
        </div>
      </div>

      <!-- Bottom CTA -->
      <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
        <h3 class="text-2xl mb-4">Réserver maintenant</h3>
        <p class="text-gray-600 mb-6">
          Contactez {{ intervenant.name.split(' ')[0] }} pour discuter de vos besoins et planifier une intervention
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button
            @click="handleBookingClick()"
            class="px-10 py-4 rounded-lg text-white text-lg transition-all hover:opacity-90"
            :style="{ backgroundColor: primaryColor }"
          >
            Réserver
          </button>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <BookingModal
      v-if="showBookingModal"
      :intervenant="{
        id: intervenant.id,
        name: intervenant.name,
        image: intervenant.profileImage,
        averageRating: intervenant.rating,
        hourlyRate: intervenant.services[0]?.price || 0
      }"
      :clientId="effectiveClientId"
      :preselectedService="selectedServiceForBooking"
      :preselectedTask="selectedTaskForBooking"
      @close="showBookingModal = false"
      @success="handleBookingSuccess"
    />

    <!-- Image Modal -->
    <div
      v-if="selectedImage"
      class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4"
      @click="selectedImage = null"
    >
      <button
        class="absolute top-4 right-4 p-2 bg-white rounded-full hover:bg-gray-100 transition-all"
        @click="selectedImage = null"
      >
        <XIcon :size="24" />
      </button>
      <img
        :src="selectedImage"
        alt="Réalisation"
        class="max-w-full max-h-full object-contain rounded-lg"
      />
    </div>
  </div>
</template>

<script>
import { 
  ArrowLeft as ArrowLeftIcon, 
  Star as StarIcon, 
  MapPin as MapPinIcon, 
  Heart as HeartIcon, 
  CheckCircle as CheckCircleIcon, 
  Clock as ClockIcon,
  Camera as CameraIcon, 
  X as XIcon 
} from 'lucide-vue-next';
import ImageWithFallback from './figma/ImageWithFallback.vue';
import BookingModal from './BookingModal.vue';
import intervenantService from '../services/intervenantService';
import authService from '../services/authService';
import { formatExperience } from '@/utils/experienceFormatter';

export default {
  name: 'IntervenantProfilePage',
  components: {
    ArrowLeftIcon,
    StarIcon,
    MapPinIcon,
    HeartIcon,
    CheckCircleIcon,
    ClockIcon,
    CameraIcon,
    XIcon,
    ImageWithFallback,
    BookingModal
  },
  props: {
    intervenantId: {
      type: Number,
      required: false
    },
    intervenantData: {
      type: Object,
      required: false
    },
    service: {
      type: String,
      required: true,
      validator: (value) => ['jardinage', 'menage'].includes(value)
    },
    clientId: {
      type: Number,
      required: false
    }
  },
  emits: ['back', 'login-required'],
  data() {
    return {
      selectedImage: null,
      activeTab: 'apropos',
      isFavorite: false,
      loading: false,
      error: null,
      showBookingModal: false,
      selectedServiceForBooking: null,
      selectedTaskForBooking: null,
      tabs: [
        { id: 'apropos', label: 'À propos' },
        { id: 'services', label: 'Taches' },
        { id: 'avis', label: 'Avis' },
        { id: 'photos', label: 'Photos' },
        { id: 'disponibilites', label: 'Disponibilités' }
      ],
      intervenant: {
        id: null,
        name: '',
        profileImage: '',
        rating: 0,
        reviewCount: 0,
        location: '',
        experience: 'N/A',
        verified: false,
        memberSince: '',
        completedJobs: 0,
        bio: [],
        services: [],
        reviews: [],
        photos: [],
        availability: []
      }
    };
  },
  computed: {
    primaryColor() {
      return this.service === 'jardinage' ? '#92B08B' : '#4682B4';
    },
    buttonColor() {
      return '#609B41';
    },
    effectiveClientId() {
      if (this.clientId) return this.clientId;
      const user = authService.getUserSync();
      // Try multiple possible locations for the client ID
      return user?.client?.id || user?.client_id || user?.id || 1;
    },
    ratingDistribution() {
      const distribution = [
        { stars: 5, count: 0 },
        { stars: 4, count: 0 },
        { stars: 3, count: 0 },
        { stars: 2, count: 0 },
        { stars: 1, count: 0 }
      ];

      const reviews = this.intervenant.reviews || [];
      const total = reviews.length;

      if (total === 0) {
        return distribution.map(d => ({ ...d, percentage: 0 }));
      }

      reviews.forEach(review => {
        let rating = Math.round(Number(review.rating));
        if (rating < 1) rating = 1;
        if (rating > 5) rating = 5;
        
        const item = distribution.find(d => d.stars === rating);
        if (item) item.count++;
      });

      return distribution.map(d => ({
        ...d,
        percentage: Math.round((d.count / total) * 100)
      }));
    }
  },
  async created() {
    if (this.intervenantData) {
      this.loadFromProvidedData();
    } 
    
    const idToFetch = this.intervenantId || (this.intervenantData ? this.intervenantData.id : null);
    
    if (idToFetch) {
      await this.fetchIntervenantData(idToFetch);
    } else {
      if (!this.intervenantData) {
         this.error = "Aucune donnée d'intervenant fournie.";
      }
    }
  },
  methods: {
    loadFromProvidedData() {
      const data = this.intervenantData;
      
      this.intervenant = {
        id: data.id,
        name: data.name,
        profileImage: data.image,
        rating: data.rating || 0,
        reviewCount: data.reviewCount || 0,
        location: data.location,
        experience: data.experience,
        verified: data.verified,
        memberSince: 'Membre récent',
        completedJobs: 0,
        bio: [],
        services: this.mapServices(data.taches || []),
        reviews: [],
        photos: [data.image].filter(Boolean),
        availability: []
      };
    },
    
    async fetchIntervenantData(optionalId = null) {
      try {
        const id = optionalId || this.intervenantId;
        if (!id) return;
        
        const data = await intervenantService.getById(id);

        if (!data) {
          throw new Error("Données de l'intervenant introuvables");
        }
        
        const mappedPhotos = [];
        if (data.interventions) {
             data.interventions.forEach(intervention => {
                 if (intervention.photos) {
                     intervention.photos.forEach(photo => {
                         if (photo.photo_path) mappedPhotos.push(photo.photo_path);
                     });
                 }
             });
        }
        
        const reviews = this.mapReviews(data.interventions);

        this.intervenant = {
          id: data.id,
          name: data.utilisateur ? `${data.utilisateur.prenom || ''} ${data.utilisateur.nom || ''}`.trim() : 'Intervenant',
          profileImage: data.utilisateur && data.utilisateur.url ? data.utilisateur.url : 'https://ui-avatars.com/api/?name=Intervenant&background=random',
          rating: data.average_rating || 0,
          reviewCount: data.review_count || 0,
          location: data.ville || data.address || 'Localisation non spécifiée',
          experience: this.getExperienceForService(data) || 'N/A',
          verified: data.is_active ?? true,
          memberSince: data.created_at ? `Membre depuis ${new Date(data.created_at).getFullYear()}` : 'N/A',
          completedJobs: data.interv_count || 0,
          bio: data.bio ? [data.bio] : ["Professionnel expérimenté à votre service."],
          services: this.mapServices(data.taches),
          reviews: reviews, 
          photos: mappedPhotos,
          availability: this.mapAvailability(data.disponibilites)
        };
      } catch (err) {
        console.error("Erreur lors du chargement de l'intervenant:", err);
        if (!this.intervenant.id) {
             this.error = "Impossible de charger les informations de l'intervenant.";
        }
      } finally {
        this.loading = false;
      }
    },
    
    mapServices(taches) {
      if (!taches || !Array.isArray(taches)) return [];
      
      const currentServiceLabel = this.service === 'jardinage' ? 'jardin' : 'ménage';
      
      return taches
        .filter(t => {
           const serviceName = (t.service?.nom_service || '').toLowerCase();
           return serviceName.includes(currentServiceLabel);
        })
        .map(t => ({
          id: t.id,
          name: t.nom_tache || 'Service',
          description: t.description || 'Prestation de qualité réalisée avec soin et professionnalisme.',
          duration: 'Sur mesure (2h min)',
          price: t.pivot?.prix_tache || t.prix || 25
        }));
    },
    
    mapReviews(interventions) {
      if (!interventions || !Array.isArray(interventions)) return [];
      
      const reviews = [];
      
      interventions.forEach(intervention => {
          if (intervention.evaluations && intervention.evaluations.length > 0) {
              // Calculer la moyenne des notes pour cette intervention
              const totalNote = intervention.evaluations.reduce((sum, e) => sum + Number(e.note), 0);
              const avgNote = (totalNote / intervention.evaluations.length).toFixed(1);
              
              const comment = intervention.commentaires && intervention.commentaires.length > 0 
                 ? intervention.commentaires[0].commentaire 
                 : 'Prestation satisfaisante, client n\'a pas laissé de commentaire détaillé.';
              
              let clientName = 'Client';
              if (intervention.client && intervention.client.utilisateur) {
                  clientName = `${intervention.client.utilisateur.prenom || ''} ${intervention.client.utilisateur.nom || ''}`.trim();
              }

              const clientAvatar = intervention.client?.utilisateur?.url || `https://ui-avatars.com/api/?name=${clientName}&background=random`;
              
              reviews.push({
                  id: intervention.id,
                  clientName: clientName || 'Client anonyme',
                  clientAvatar: clientAvatar,
                  date: this.formatDate(intervention.date_intervention),
                  rating: Number(avgNote),
                  comment: comment
              });
          }
      });
      
      return reviews;
    },
    
    mapAvailability(disponibilites) {
      if (!disponibilites || !Array.isArray(disponibilites) || disponibilites.length === 0) return [];
      
      // Trier par jour de la semaine ou date
      return disponibilites.map(d => {
        let dayLabel = '';
        if (d.type === 'reguliere' && d.jours_semaine) {
          dayLabel = d.jours_semaine.charAt(0).toUpperCase() + d.jours_semaine.slice(1);
        } else if (d.date_specific) {
          dayLabel = this.formatDate(d.date_specific);
        } else {
          dayLabel = 'Disponible';
        }
        
        return {
          day: dayLabel,
          available: true,
          hours: `${this.formatTime(d.heure_debut)} - ${this.formatTime(d.heure_fin)}`,
          type: d.type
        };
      });
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    },
    
    getDayName(dateString) {
      return new Date(dateString).toLocaleDateString('fr-FR', { weekday: 'long' });
    },
    
    formatTime(timeString) {
      if (!timeString) return '00:00';
      if (timeString instanceof Date || timeString.includes('T') || timeString.includes('-')) {
        return new Date(timeString).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
      }
      const [hours, minutes] = timeString.split(':');
      return `${hours}:${minutes}`;
    },
    getExperienceForService(data) {
      if (!data || !data.services) return null;
      const targetService = this.service ? this.service.toLowerCase() : '';
      const found = data.services.find(s => 
        (s.nom_service || s.name || '').toLowerCase().includes(targetService)
      );
      if (found && found.pivot && found.pivot.experience) {
        return found.pivot.experience;
      }
      const anyExp = data.services.find(s => s.pivot && s.pivot.experience);
      return anyExp ? anyExp.pivot.experience : null;
    },
    handleFavoriteClick() {
      if (!authService.isAuthenticated()) {
        this.$emit('login-required');
        return;
      }
      this.isFavorite = !this.isFavorite;
    },
    handleBookingClick(service = null, task = null) {
      if (!authService.isAuthenticated()) {
        this.$emit('login-required');
        return;
      }
      this.selectedServiceForBooking = service;
      this.selectedTaskForBooking = task;
      this.showBookingModal = true;
    },
    handleBookingSuccess() {
      this.showBookingModal = false;
    },
    formatExperience
  }
};
</script>

<style scoped>
</style>