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
            <!-- Badge commenté comme demandé
            <div
              v-if="intervenant.verified"
              class="absolute bottom-1 right-1 w-8 h-8 rounded-full flex items-center justify-center shadow-md"
              :style="{ backgroundColor: buttonColor }"
            >
              <CheckCircleIcon :size="20" class="text-white" />
            </div>
            -->
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
                  <div>
                    <h4 class="text-lg mb-1">{{ review.clientName }}</h4>
                    <p class="text-sm text-gray-500">
                      {{ formatDate(review.date) }}
                    </p>
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
                <span
                  class="text-lg"
                  :style="{ color: day.available ? primaryColor : '#9CA3AF' }"
                >
                  {{ day.day }}
                </span>
                <span
                  class="text-lg"
                  :style="{ color: day.available ? primaryColor : '#9CA3AF' }"
                >
                  {{ day.available ? day.hours : 'Non disponible' }}
                </span>
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
            class="px-10 py-4 rounded-lg text-white text-lg transition-all hover:opacity-90"
            :style="{ backgroundColor: primaryColor }"
          >
            Réserver
          </button>
        </div>
      </div>
    </div>

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
    ImageWithFallback
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
        expert: false,
        memberSince: '',
        responseTime: '~2h',
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
        // Round rating to nearest integer (1-5)
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
    // Si les données sont déjà passées en prop, les utiliser directement pour l'affichage initial
    if (this.intervenantData) {
      this.loadFromProvidedData();
    } 
    
    // TOUJOURS charger les données complètes depuis l'API pour avoir la bio, les avis, et les disponibilités réelles
    const idToFetch = this.intervenantId || (this.intervenantData ? this.intervenantData.id : null);
    
    if (idToFetch) {
      // Si l'ID n'était pas dans les props mais dans les data, on s'assure qu'on l'a pour le fetch
      if (!this.intervenantId && this.intervenantData) {
          // On passe l'ID à la méthode de fetch
          await this.fetchIntervenantData(idToFetch);
      } else {
          await this.fetchIntervenantData();
      }
    } else {
      if (!this.intervenantData) {
         this.error = "Aucune donnée d'intervenant fournie.";
      }
    }
  },
  methods: {
    // NOUVELLE MÉTHODE: Charger depuis les données fournies
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
        expert: true,
        memberSince: 'Membre récent',
        responseTime: '~2h',
        completedJobs: 0,
        bio: [], // Pas de bio par défaut, on attend l'API
        services: this.mapServices(data.taches || []),
        reviews: [], // Pas d'avis par défaut, on attend l'API
        photos: [data.image].filter(Boolean), // Juste l'image de profil en attendant
        availability: [] // Pas de dispo par défaut, on attend l'API
      };
    },
    
    // Méthode existante pour charger depuis l'API
    // Méthode existante pour charger depuis l'API
    async fetchIntervenantData(optionalId = null) {
      // this.loading = true; // Désactivé pour affichage immédiat
      try {
        const id = optionalId || this.intervenantId;
        if (!id) return;
        
        const response = await intervenantService.getById(id);
        const data = response.data || response;

        if (!data) {
          throw new Error("Données de l'intervenant introuvables");
        }
        
        // --- DATA MAPPING LOGIC ---
        
        // 1. Photos: Collect all photos from all interventions
        // Note: photos is now an array of objects inside interventions
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
        // Fallback images if no real photos found - KEEP THIS or REMOVE?
        // User wants REAL data. Let's keep mappedPhotos empty if empty.
        // Or keep distinct fallback only if absolutely NO photos exist? 
        // Let's remove the fallback images to match user request "pas depuis la bd".
        
        const reviews = this.mapReviews(data.interventions);
        
        // Calculate average rating and count from reviews to ensure consistency
        let calculatedRating = 0;
        let calculatedCount = reviews.length;
        
        if (calculatedCount > 0) {
            const sum = reviews.reduce((acc, review) => acc + Number(review.rating), 0);
            calculatedRating = (sum / calculatedCount).toFixed(1); // Keep 1 decimal
        } else {
            // Fallback to backend value if no specific reviews found
            calculatedRating = Number(data.average_rating) || 0;
            calculatedCount = Number(data.review_count) || 0;
        }

        this.intervenant = {
          id: data.id,
          name: data.utilisateur ? `${data.utilisateur.prenom || ''} ${data.utilisateur.nom || ''}`.trim() : 'Intervenant',
          profileImage: data.utilisateur && data.utilisateur.url ? data.utilisateur.url : 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400&h=400&fit=crop',
          rating: calculatedRating,
          reviewCount: calculatedCount,
          location: data.ville || data.address || 'Localisation non spécifiée',
          experience: this.getExperienceForService(data) || 'N/A',
          verified: !!data.is_active,
          expert: true,
          memberSince: data.created_at ? `Membre depuis ${new Date(data.created_at).getFullYear()}` : 'Membre récent',
          responseTime: '~2h',
          completedJobs: data.interventions ? data.interventions.length : 0,
          bio: data.bio ? [data.bio] : [], // Empty if no bio
          services: this.mapServices(data.taches),
          reviews: reviews, 
          photos: mappedPhotos,
          availability: this.mapAvailability(data.disponibilites)
        };
      } catch (err) {
        console.error("Erreur lors du chargement de l'intervenant:", err);
        // Ne pas écraser l'erreur si on a déjà chargé des données via props
        if (!this.intervenant.id) {
             this.error = "Impossible de charger les informations de l'intervenant.";
        }
      } finally {
        this.loading = false;
      }
    },
    
    mapServices(taches) {
      if (!taches || !Array.isArray(taches)) return [];
      
      return taches.map(t => ({
        id: t.id,
        name: t.nom_tache || t.service?.nom_service || 'Service',
        description: t.description || 'Description du service...',
        duration: '2-3 heures recommandées',
        price: t.pivot?.prix_tache || t.prix || 25
      }));
    },
    
    mapReviews(interventions) {
      if (!interventions || !Array.isArray(interventions)) return [];
      
      const reviews = [];
      
      interventions.forEach(intervention => {
          // Check for evaluations first
          if (intervention.evaluations && intervention.evaluations.length > 0) {
              intervention.evaluations.forEach(evaluation => {
                 // Try to find a matching comment
                 const comment = intervention.commentaires && intervention.commentaires.length > 0 
                    ? intervention.commentaires[0].commentaire 
                    : 'Pas de commentaire écrit.';
                 
                 // Get client name
                 let clientName = 'Client';
                 if (intervention.client && intervention.client.utilisateur) {
                     clientName = `${intervention.client.utilisateur.prenom || ''} ${intervention.client.utilisateur.nom || ''}`.trim();
                 }
                 
                 reviews.push({
                     id: evaluation.id,
                     clientName: clientName || 'Client',
                     date: this.formatDate(evaluation.created_at || intervention.date_intervention),
                     rating: evaluation.note,
                     comment: comment
                 });
              });
          }
      });
      
      return reviews; // Return pure reviews array, empty if none
    },
    
    mapAvailability(disponibilites) {
      if (!disponibilites || disponibilites.length === 0) return []; // Retour simple si vide
      
      
      return disponibilites.map(d => ({
        day: d.jours_semaine ? d.jours_semaine.charAt(0).toUpperCase() + d.jours_semaine.slice(1) : this.getDayName(d.date_specific), 
        available: true,
        hours: `${this.formatTime(d.heure_debut)} - ${this.formatTime(d.heure_fin)}`
      }));
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
      // Si c'est déjà un objet Date ou une chaine de date complète
      if (timeString instanceof Date || timeString.includes('T') || timeString.includes('-')) {
        return new Date(timeString).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
      }
      // Sinon on suppose que c'est une heure "HH:mm:ss" ou "HH:mm"
      const [hours, minutes] = timeString.split(':');
      return `${hours}:${minutes}`;
    },
    getExperienceForService(data) {
      if (!data || !data.services) return null;
      // Find service matching current context (jardinage/menage)
      // This requires the service names to be normalized or checked broadly
      // For now we check if any service name contains our prop
      // Or we just take the max experience?
      // Let's try to match by name or return the first with experience
      
      const targetService = this.service ? this.service.toLowerCase() : '';
      
      const found = data.services.find(s => 
        (s.nom_service || s.name || '').toLowerCase().includes(targetService)
      );
      
      if (found && found.pivot && found.pivot.experience) {
        return found.pivot.experience;
      }
      
      // Fallback: take first non-null experience
      const anyExp = data.services.find(s => s.pivot && s.pivot.experience);
      return anyExp ? anyExp.pivot.experience : null;
    },
    handleFavoriteClick() {
      const isAuth = authService.isAuthenticated();
      console.log('IntervenantProfile: Clic favori. Authentifié ?', isAuth);
      
      if (!isAuth) {
        console.log('IntervenantProfile: Redirection vers login demandée');
        this.$emit('login-required');
        return;
      }
      this.isFavorite = !this.isFavorite;
      // TODO: Call API to save favorite
    },
    formatExperience
  }
};
</script>

<style scoped>
/* Styles spécifiques si nécessaire */
</style>