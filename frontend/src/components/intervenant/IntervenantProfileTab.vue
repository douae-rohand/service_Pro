<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Modern Header (Non-sticky) -->
    <div class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <button
          @click="$emit('back')"
          @mouseenter="hoverBackButton = true"
          @mouseleave="hoverBackButton = false"
          class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300 hover:scale-110 shadow-sm"
          :style="{ 
            backgroundColor: hoverBackButton ? primaryColor : 'white',
            color: hoverBackButton ? 'white' : '#4B5563',
            border: `1px solid ${hoverBackButton ? primaryColor : '#E5E7EB'}`
          }"
        >
          <ArrowLeftIcon :size="20" />
        </button>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
    <div v-if="!error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Hero Section - Horizontal Layout -->
      <div class="bg-white rounded-3xl shadow-lg p-8 mb-8">
        <div class="flex flex-col md:flex-row gap-8">
          <!-- Left: Profile Image and Info -->
          <div class="flex gap-6 items-start">
            <!-- Profile Image with Verified Badge -->
            <div class="flex-shrink-0 relative">
              <div class="w-28 h-28 rounded-full overflow-hidden shadow-lg border-4 border-white" :style="{ borderColor: primaryColor + '20' }">
                <ImageWithFallback
                  :src="intervenant.profileImage"
                  :alt="intervenant.name"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>

            <!-- Name, Location, Rating -->
            <div class="flex-1">
              <h1 class="text-3xl font-bold mb-2" :style="{ color: '#305C7D' }">
                {{ intervenant.name }}
              </h1>
              
              <div class="flex items-center gap-2 text-gray-600 mb-3">
                <MapPinIcon :size="16" class="text-gray-400" />
                <span class="text-sm">{{ intervenant.location }}</span>
              </div>

              <div class="flex items-center gap-2 text-gray-600 mb-4">
                <span class="text-sm">{{ formatExperience(intervenant.experience) }} d'expérience</span>
              </div>
              
              <!-- Rating Display -->
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg">
                  <StarIcon :size="16" class="fill-yellow-400 text-yellow-400" />
                  <span class="font-bold text-gray-800">{{ intervenant.rating }}</span>
                </div>
                <span class="text-sm text-gray-500">({{ intervenant.reviewCount }} avis)</span>
              </div>
            </div>
          </div>

          <!-- Right: Stats in Horizontal Row -->
          <div class="flex-1 flex items-center justify-center gap-8">
            <!-- Missions -->
            <div class="text-center">
              <div class="text-4xl font-bold mb-1" :style="{ color: primaryColor }">{{ intervenant.completedJobs }}</div>
              <div class="text-xs text-gray-500 uppercase tracking-wide">Missions</div>
            </div>

            <!-- Divider -->
            <div class="h-12 w-px bg-gray-200"></div>

            <!-- Note Moyenne -->
            <div class="text-center">
              <div class="text-4xl font-bold mb-1" :style="{ color: primaryColor }">{{ intervenant.rating }}</div>
              <div class="text-xs text-gray-500 uppercase tracking-wide">Note Moyenne</div>
            </div>

            <!-- Divider -->
            <div class="h-12 w-px bg-gray-200"></div>

            <!-- Avis -->
            <div class="text-center">
              <div class="text-4xl font-bold mb-1" :style="{ color: primaryColor }">{{ intervenant.reviewCount }}</div>
              <div class="text-xs text-gray-500 uppercase tracking-wide">Avis</div>
            </div>

            <!-- Divider -->
            <div class="h-12 w-px bg-gray-200"></div>

            <!-- Membre Depuis -->
            <div class="text-center">
              <div class="text-sm font-bold mb-1 text-gray-800">Depuis {{ new Date(intervenant.memberSince).getFullYear() || '2025' }}</div>
              <div class="text-xs text-gray-500 uppercase tracking-wide">Membre</div>
            </div>
          </div>
        </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button
              @click="handleFavoriteClick"
              class="px-5 py-2.5 rounded-lg border-2 transition-all flex items-center gap-2 hover:shadow-md"
              :style="{ 
                borderColor: isFavorite ? '#4682B4' : primaryColor, 
                color: isFavorite ? '#4682B4' : primaryColor,
                backgroundColor: isFavorite ? '#E8F4FD' : 'transparent'
              }"
            >
              <HeartIcon :size="18" :class="{ 'fill-current': isFavorite }" :style="{ color: isFavorite ? '#4682B4' : 'currentColor' }" />
              {{ isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
            </button>
          </div>
        </div>
      

      <!-- Tabs Navigation -->
      <div class="bg-white rounded-3xl shadow-lg mb-8 overflow-hidden">
        <div class="border-b border-gray-100">
          <div class="flex gap-2 px-6 overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'px-6 py-4 transition-all relative whitespace-nowrap font-semibold',
                activeTab !== tab.id ? 'text-gray-600 hover:text-gray-900' : ''
              ]"
              :style="activeTab === tab.id ? { color: primaryColor } : {}"
            >
              {{ tab.label }}
              <div
                v-if="activeTab === tab.id"
                class="absolute bottom-0 left-0 right-0 h-1 rounded-t-full transition-all"
                :style="{ backgroundColor: primaryColor }"
              ></div>
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="p-8">
          <!-- À propos Tab -->
          <div v-if="activeTab === 'apropos'" class="animate-fade-in">
            <h2 class="text-3xl font-bold mb-6" :style="{ color: primaryColor }">
              À propos de {{ intervenant.name.split(' ')[0] }}
            </h2>
            <div class="space-y-4 text-gray-700 leading-relaxed text-lg">
              <p v-for="(paragraph, index) in intervenant.bio" :key="index">
                {{ paragraph }}
              </p>
            </div>
          </div>

          <!-- Services Tab -->
          <div v-if="activeTab === 'services'" class="animate-fade-in">
            <h2 class="text-3xl font-bold mb-6" :style="{ color: primaryColor }">Services proposés</h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div
                v-for="serviceItem in intervenant.services"
                :key="serviceItem.id"
                class="bg-gray-50 border border-gray-200 rounded-2xl p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
              >
                <h3 class="text-2xl font-bold mb-3" :style="{ color: primaryColor }">
                  {{ serviceItem.name }}
                </h3>
                
                <div class="flex items-center justify-between mb-4">
                  
                  <span class="text-2xl font-bold text-gray-800">
                    {{ serviceItem.price }}DH/h
                  </span>
                </div>
                <button
                  @click="handleBookingClick(null, serviceItem)"
                  class="w-full px-6 py-3 rounded-xl text-white font-semibold transition-all hover:shadow-lg transform hover:scale-105"
                  :style="{ backgroundColor: primaryColor }"
                >
                  Réserver ce service
                </button>
              </div>
            </div>
          </div>

          <!-- Avis Tab -->
          <div v-if="activeTab === 'avis'" class="animate-fade-in">
            <h2 class="text-3xl font-bold mb-6" :style="{ color: primaryColor }">Avis & Évaluations</h2>
            
            <!-- Rating Summary -->
            <div class="grid md:grid-cols-2 gap-8 mb-8 pb-8 border-b border-gray-200">
              <div class="text-center bg-gray-50 rounded-2xl p-8">
                <div class="text-7xl font-bold mb-3" :style="{ color: primaryColor }">{{ intervenant.rating }}</div>
                <div class="flex items-center justify-center gap-1 mb-3">
                  <StarIcon
                    v-for="star in 5"
                    :key="star"
                    :size="24"
                    class="fill-yellow-400 text-yellow-400"
                  />
                </div>
                <p class="text-gray-600 font-medium">Basé sur {{ intervenant.reviewCount }} avis</p>
              </div>

              <!-- Rating Distribution -->
              <div class="space-y-3">
                <div
                  v-for="item in ratingDistribution"
                  :key="item.stars"
                  class="flex items-center gap-3"
                >
                  <span class="text-sm font-semibold w-12">{{ item.stars }} ★</span>
                  <div class="flex-1 h-4 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :style="{ 
                        width: item.percentage + '%', 
                        backgroundColor: primaryColor 
                      }"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-600 w-16 text-right font-medium">{{ item.percentage }}%</span>
                </div>
              </div>
            </div>

            <!-- Reviews List -->
            <div class="space-y-6">
              <div
                v-for="review in intervenant.reviews"
                :key="review.id"
                class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-lg transition-all"
              >
                <div class="flex items-start justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <img 
                      :src="review.clientAvatar" 
                      class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-md"
                    />
                    <div>
                      <h4 class="text-lg font-bold text-gray-900">{{ review.clientName }}</h4>
                      <p class="text-sm text-gray-500">
                        {{ formatDate(review.date) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1 bg-yellow-50 px-3 py-1 rounded-lg">
                    <StarIcon :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span class="font-bold text-gray-800">{{ review.rating }}</span>
                  </div>
                </div>
                <p v-if="review.comment" class="text-gray-700 leading-relaxed">{{ review.comment }}</p>
                <p v-else class="text-gray-400 italic">Aucun commentaire laissé</p>
              </div>
            </div>
          </div>

          <!-- Photos Tab -->
          <div v-if="activeTab === 'photos'" class="animate-fade-in">
            <h2 class="text-3xl font-bold mb-6" :style="{ color: primaryColor }">Photos des réalisations</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <div
                v-for="(photo, index) in intervenant.photos"
                :key="index"
                class="relative aspect-square rounded-2xl overflow-hidden cursor-pointer group shadow-md hover:shadow-2xl transition-all"
                @click="selectedImage = photo"
              >
                <ImageWithFallback
                  :src="photo"
                  :alt="`Réalisation ${index + 1}`"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center">
                  <CameraIcon class="text-white opacity-0 group-hover:opacity-100 transition-opacity" :size="40" />
                </div>
              </div>
            </div>
          </div>

          <!-- Disponibilités Tab -->
          <div v-if="activeTab === 'disponibilites'" class="animate-fade-in">
            <h2 class="text-3xl font-bold mb-6" :style="{ color: primaryColor }">Disponibilités</h2>
            
            <!-- Section Hebdomadaire -->
            <div v-if="regularAvailabilities.length > 0" class="mb-10">
              <div class="flex items-center gap-3 mb-6 p-3 bg-gray-50 rounded-xl inline-flex border border-gray-100">
                <CalendarIcon :size="20" class="text-gray-500" />
                <h3 class="text-lg font-bold text-gray-700 uppercase tracking-wider">Horaires Hebdomadaires</h3>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="(day, index) in regularAvailabilities"
                  :key="'reg-' + index"
                  class="flex items-center justify-between py-4 px-6 bg-white rounded-2xl border-2 transition-all hover:shadow-md"
                  :style="{ borderColor: day.available ? primaryColor + '20' : '#F3F4F6' }"
                >
                  <div class="flex flex-col">
                    <span class="text-lg font-bold" :style="{ color: day.available ? primaryColor : '#9CA3AF' }">
                      {{ day.day }}
                    </span>
                    <span class="text-[10px] uppercase font-bold text-gray-400">Régulier</span>
                  </div>
                  <div class="flex items-center gap-4">
                    <span class="text-sm font-bold" :style="{ color: day.available ? '#374151' : '#9CA3AF' }">
                      {{ day.available ? day.hours : 'Non disponible' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section Ponctuelle / Exceptions -->
            <div v-if="punctualAvailabilities.length > 0">
              <div class="flex items-center gap-3 mb-6 p-3 bg-gray-50 rounded-xl inline-flex border border-gray-100">
                <CalendarClockIcon :size="20" class="text-gray-500" />
                <h3 class="text-lg font-bold text-gray-700 uppercase tracking-wider">Modifications Exceptionnelles</h3>
              </div>
              
              <div class="space-y-4">
                <div
                  v-for="(day, index) in punctualAvailabilities"
                  :key="'ponc-' + index"
                  class="flex items-center justify-between py-5 px-8 rounded-2xl border-l-8 transition-all hover:shadow-lg bg-white shadow-sm"
                  :style="{ 
                    borderLeftColor: day.available ? primaryColor : '#F87171',
                    backgroundColor: day.available ? '#FFFFFF' : '#FEF2F2'
                  }"
                >
                  <div class="flex items-center gap-6">
                    <div 
                      class="w-12 h-12 rounded-full flex items-center justify-center" 
                      :style="{ backgroundColor: day.available ? primaryColor + '15' : '#FEE2E2' }"
                    >
                      <CheckCircleIcon v-if="day.available" :size="24" :style="{ color: primaryColor }" />
                      <XIcon v-else :size="24" class="text-red-600" />
                    </div>
                    <div class="flex flex-col">
                      <span class="text-xl font-bold" :class="day.available ? 'text-gray-900' : 'text-red-900'">
                        {{ day.day }}
                      </span>
                      <span 
                        class="text-xs font-semibold uppercase tracking-widest" 
                        :style="{ color: day.available ? primaryColor : '#EF4444' }"
                      >
                        {{ day.available ? 'Disponibilité Ajoutée' : 'Absence Prévue' }}
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center gap-6">
                    <div class="text-right">
                      <p 
                        class="text-lg font-black" 
                        :style="{ color: day.available ? '#374151' : '#991B1B' }"
                      >
                        {{ day.available ? day.hours : 'Non disponible' }}
                      </p>
                      <p v-if="!day.available" class="text-xs font-semibold text-red-500 italic">{{ day.reason || 'Intervenant absent ce jour' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- État Vide -->
            <div v-if="regularAvailabilities.length === 0 && punctualAvailabilities.length === 0" class="text-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
               <CalendarIcon :size="48" class="mx-auto text-gray-300 mb-4" />
               <p class="text-gray-500 font-medium">Aucune disponibilité renseignée pour le moment.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom CTA with Gradient -->
      <div class="relative bg-white rounded-3xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 opacity-5" :style="{ background: `linear-gradient(135deg, ${primaryColor} 0%, white 100%)` }"></div>
        <div class="relative p-12 text-center">
          <h3 class="text-3xl font-bold mb-4" :style="{ color: primaryColor }">Prêt à réserver ?</h3>
          <p class="text-gray-600 mb-8 text-lg max-w-2xl mx-auto">
            Contactez {{ intervenant.name.split(' ')[0] }} pour discuter de vos besoins et planifier une intervention
          </p>
          <button
            @click="handleBookingClick()"
            class="px-12 py-4 rounded-xl text-white text-lg font-bold transition-all hover:shadow-2xl transform hover:scale-105"
            :style="{ backgroundColor: primaryColor }"
          >
            Réserver maintenant
          </button>
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
      class="fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center p-4"
      @click="selectedImage = null"
    >
      <button
        class="absolute top-4 right-4 p-3 bg-white rounded-full hover:bg-gray-100 transition-all shadow-lg"
        @click="selectedImage = null"
      >
        <XIcon :size="24" />
      </button>
      <img
        :src="selectedImage"
        alt="Réalisation"
        class="max-w-full max-h-full object-contain rounded-2xl shadow-2xl"
      />
    </div>
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
  X as XIcon,
  Calendar as CalendarIcon,
  CalendarClock as CalendarClockIcon
} from 'lucide-vue-next';
import ImageWithFallback from './figma/ImageWithFallback.vue';
import BookingModal from './BookingModal.vue';
import intervenantService from '../services/intervenantService';
import authService from '../services/authService';
import favoriteService from '../services/favoriteService';
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
    CalendarIcon,
    CalendarClockIcon,
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
    emits: ['back', 'login-required', 'navigate-booking'],
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
      hoverBackButton: false,
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
        availability: [],
        serviceId: null // Store the primary service ID
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
        let rating = Math.floor(Number(review.rating));
        if (rating < 1) rating = 1;
        if (rating > 5) rating = 5;
        
        const item = distribution.find(d => d.stars === rating);
        if (item) item.count++;
      });

      return distribution.map(d => ({
        ...d,
        percentage: Math.round((d.count / total) * 100)
      }));
    },
    regularAvailabilities() {
      return (this.intervenant.availability || []).filter(a => a.type === 'reguliere');
    },
    punctualAvailabilities() {
      return (this.intervenant.availability || []).filter(a => a.type === 'ponctuelle');
    }
  },
  async created() {
    console.log('🚀 IntervenantProfile created with:', {
      intervenantId: this.intervenantId,
      intervenantData: this.intervenantData,
      service: this.service
    });
    
    // Déterminer l'ID à utiliser (priorité à intervenantId, puis intervenantData.id)
    const idToFetch = this.intervenantId || (this.intervenantData ? this.intervenantData.id : null);
    
    if (idToFetch) {
      // Toujours charger depuis le backend pour avoir les données complètes
      console.log('📡 Fetching full data from backend for ID:', idToFetch);
      await this.fetchIntervenantData(idToFetch);
    } else if (this.intervenantData) {
      // Fallback: utiliser les données fournies si pas d'ID disponible
      console.log('📦 Using provided intervenantData (no ID available)');
      await this.loadFromProvidedData();
    } else {
      this.error = "Aucune donnée d'intervenant fournie.";
      console.error('❌ No intervenant data or ID provided');
    }
  },
  methods: {
    async loadFromProvidedData() {
      const data = this.intervenantData;
      
      // Obtenir le service_id depuis les données fournies
      let serviceId = null;
      if (data.taches && data.taches.length > 0 && data.taches[0].service_id) {
        serviceId = data.taches[0].service_id;
      } else if (data.serviceId) {
        serviceId = data.serviceId;
      } else {
        // Fallback: utiliser 1 pour Jardinage, 2 pour Ménage
        serviceId = this.service === 'jardinage' ? 1 : 2;
      }
      
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
        availability: [],
        serviceId: serviceId
      };
      
      // Vérifier le statut favori
      await this.checkFavoriteStatus();
    },
    
    async fetchIntervenantData(optionalId = null) {
      try {
        this.loading = true;
        const id = optionalId || this.intervenantId;
        if (!id) {
          this.error = "ID de l'intervenant manquant";
          return;
        }
        
        console.log('🔍 Fetching intervenant data for ID:', id);
        const response = await intervenantService.getById(id);
        console.log('📦 Response from getById:', response);
        
        // Le service getById retourne déjà res.data, donc response est directement les données
        // Mais il peut aussi y avoir un wrapper, donc on vérifie les deux
        const data = response?.data || response;

        if (!data) {
          throw new Error("Données de l'intervenant introuvables");
        }
        
        console.log('✅ Parsed data:', data);
        
        const getImageUrl = (path) => {
          if (!path) return '';
          if (path.startsWith('http')) return path;
          // Use hardcoded URL to match InterventionDetailsModal.vue logic
          return `http://127.0.0.1:8000/storage/${path.replace(/^\/+/, '')}`;
        };
        
        // Expose helper to template via this (if needed in template, but here we Map)
        // Better: Map correctly here.

        const mappedPhotos = [];
        if (data.interventions) {
             data.interventions.forEach(intervention => {
                 if (intervention.photos) {
                     intervention.photos.forEach(photo => {
                         // Check for object or string structure
                         const path = typeof photo === 'string' ? photo : photo.photo_path;
                         if (path) mappedPhotos.push(getImageUrl(path));
                     });
                 }
             });
        }

        // Add Portfolio images
        if (data.portfolio) {
            data.portfolio.forEach(item => {
                 if (item.image_path) mappedPhotos.push(getImageUrl(item.image_path));
            });
        }
        
        const reviews = this.mapReviews(data.interventions);
        const fullName = data.utilisateur ? `${data.utilisateur.prenom || ''} ${data.utilisateur.nom || ''}`.trim() : 'Intervenant';

        // Obtenir le service_id depuis les taches ou services
        let serviceId = null;
        if (data.taches && data.taches.length > 0 && data.taches[0].service_id) {
          serviceId = data.taches[0].service_id;
        } else if (data.taches && data.taches.length > 0 && data.taches[0].service?.id) {
          serviceId = data.taches[0].service.id;
        } else if (data.services && data.services.length > 0) {
          // Chercher le service correspondant au prop 'service'
          const targetServiceName = this.service === 'jardinage' ? 'Jardinage' : 'Ménage';
          const matchingService = data.services.find(s => 
            (s.nom_service || s.name || '').toLowerCase().includes(this.service)
          );
          if (matchingService) {
            serviceId = matchingService.id || matchingService.service_id;
          } else if (data.services[0]) {
            serviceId = data.services[0].id || data.services[0].service_id;
          }
        }
        
        // Fallback: utiliser 1 pour Jardinage, 2 pour Ménage
        if (!serviceId) {
          serviceId = this.service === 'jardinage' ? 1 : 2;
        }

        this.intervenant = {
          id: data.id,
          name: fullName,
          profileImage: (data.utilisateur && data.utilisateur.profile_photo) || (data.utilisateur && data.utilisateur.url) || `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=${this.service === 'jardinage' ? 'DCFCE7' : 'EBF4FF'}&color=${this.service === 'jardinage' ? '92B08B' : '4682B4'}&bold=true&length=1&size=128`,
          rating: data.average_rating || 0,
          reviewCount: data.review_count || 0,
          location: data.ville || data.address || 'Localisation non spécifiée',
          experience: this.getExperienceForService(data) || 'N/A',
          verified: data.is_active ?? true,
          memberSince: data.created_at ? `Membre depuis ${new Date(data.created_at).getFullYear()}` : 'N/A',
          completedJobs: data.interv_count || 0,
          bio: data.bio ? [data.bio] : [],
          services: this.mapServices(data.taches),
          reviews: reviews, 
          photos: mappedPhotos,
          availability: this.mapAvailability(data.disponibilites),
          serviceId: serviceId
        };
        
        // Vérifier le statut favori après le chargement des données
        await this.checkFavoriteStatus();
      } catch (err) {
        console.error("❌ Erreur lors du chargement de l'intervenant:", err);
        console.error("Error details:", err.response || err.message);
        this.error = err.response?.data?.message || err.message || "Impossible de charger les informations de l'intervenant.";
        this.loading = false;
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
          description: t.description || '',
          duration: t.duree || '',
          price: t.pivot?.prix_tache || t.prix || 0,
          serviceId: t.service_id || t.service?.id // Provide serviceId
        }));
    },
    
    mapReviews(interventions) {
      if (!interventions || !Array.isArray(interventions)) return [];
      
      const reviews = [];
      
      interventions.forEach(intervention => {
          // Vérifier s'il y a des évaluations OU des commentaires
          const hasEvaluations = intervention.evaluations && intervention.evaluations.length > 0;
          const hasComments = intervention.commentaires && intervention.commentaires.length > 0;
          
          if (hasEvaluations || hasComments) {
              // Calculer la note moyenne si des évaluations existent
              let avgNote = 0;
              if (hasEvaluations) {
                  const totalNote = intervention.evaluations.reduce((sum, e) => sum + Number(e.note), 0);
                  avgNote = (totalNote / intervention.evaluations.length).toFixed(1);
              }
              
              // Récupérer le commentaire s'il existe
              const comment = hasComments ? intervention.commentaires[0].commentaire : '';
              
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
      if (!disponibilites) return [];
      
      // Handle both array and object formats
      const rawList = Array.isArray(disponibilites) 
        ? disponibilites 
        : Object.values(disponibilites);
        
      if (rawList.length === 0) return [];

      const dayOrder = {
        'lundi': 1, 'mardi': 2, 'mercredi': 3, 'jeudi': 4, 
        'vendredi': 5, 'samedi': 6, 'dimanche': 7
      };

      const mapped = rawList.map(d => {
        let dayLabel = '';
        const isReguliere = String(d.type).toLowerCase() === 'reguliere';
        
        if (isReguliere && d.jours_semaine) {
          dayLabel = d.jours_semaine.charAt(0).toUpperCase() + d.jours_semaine.slice(1);
        } else if (d.date_specific) {
          dayLabel = this.formatDate(d.date_specific);
        } else {
          dayLabel = 'Disponible';
        }
        
        // Punctual might be an "unavailability" (off day) if hours are missing
        const isAvailable = !!(d.heure_debut && d.heure_fin);
        
        return {
          id: d.id,
          day: dayLabel,
          available: isAvailable,
          hours: isAvailable ? `${this.formatTime(d.heure_debut)} - ${this.formatTime(d.heure_fin)}` : 'Indisponible',
          type: String(d.type).toLowerCase(),
          reason: d.reason,
          sortOrder: isReguliere ? dayOrder[String(d.jours_semaine).toLowerCase()] || 99 : 100,
          dateValue: d.date_specific ? new Date(d.date_specific).getTime() : Infinity
        };
      });

      // Sort: Regular days by week order, then Punctual days by date
      return mapped.sort((a, b) => {
        if (a.type !== b.type) {
          return a.type === 'reguliere' ? -1 : 1;
        }
        if (a.type === 'reguliere') {
          return a.sortOrder - b.sortOrder;
        }
        return a.dateValue - b.dateValue;
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
    async handleFavoriteClick() {
      // Vérifier l'authentification
      if (!authService.isAuthenticated()) {
        this.$emit('login-required');
        return;
      }

      // Obtenir le service_id depuis l'intervenant ou utiliser le fallback
      const serviceId = this.intervenant.serviceId || (this.service === 'jardinage' ? 1 : 2);

      if (!serviceId || !this.intervenant.id) {
        alert('Impossible de déterminer le service. Veuillez réessayer.');
        return;
      }

      try {
        // Appel API pour ajouter/retirer des favoris
        const response = await favoriteService.toggleFavorite(
          this.effectiveClientId,
          this.intervenant.id,
          serviceId
        );

        // Mettre à jour l'état local basé sur la réponse de l'API
        this.isFavorite = response.data?.is_favorite ?? !this.isFavorite;
        
        // Afficher un message de succès
        const message = this.isFavorite 
          ? `${this.intervenant.name.split(' ')[0]} a été ajouté à vos favoris`
          : `${this.intervenant.name.split(' ')[0]} a été retiré de vos favoris`;
        
        // Afficher une notification
        alert(message);
      } catch (error) {
        console.error('Error toggling favorite:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de la mise à jour des favoris';
        alert(errorMessage);
        // Ne pas changer l'état en cas d'erreur (il restera dans son état précédent)
      }
    },

    handleBookingClick(service = null, task = null) {
      // Vérifier si l'utilisateur est connecté
      if (!authService.isAuthenticated()) {
        // Stocker les paramètres pour après connexion
        localStorage.setItem('booking_intervenantId', this.intervenant.id);
        
        // Use task.serviceId if available
        const effectiveServiceId = service?.id || task?.serviceId || this.intervenant.serviceId;
        if (effectiveServiceId) {
          localStorage.setItem('booking_serviceId', effectiveServiceId);
        }
        
        if (task) {
          localStorage.setItem('booking_taskId', task.id);
        }
        localStorage.setItem('redirect_after_login', 'booking');
        this.$emit('login-required');
        return;
      }
      
      const effectiveServiceId = service?.id || task?.serviceId || this.intervenant.serviceId;

      // Si connecté, rediriger vers la page de réservation
      this.$emit('navigate-booking', {
        intervenantId: this.intervenant.id,
        serviceId: effectiveServiceId,
        taskId: task?.id || null
      });
      
      this.selectedServiceForBooking = service;
      this.selectedTaskForBooking = task;
      // Note: BookingModal might not be needed if navigation happens
      this.showBookingModal = true;
    },
    handleBookingSuccess() {
      this.showBookingModal = false;
    },
    formatExperience,
    async checkFavoriteStatus() {
      // Vérifier si l'utilisateur est connecté
      if (!authService.isAuthenticated()) {
        this.isFavorite = false;
        return;
      }

      // Obtenir le service_id depuis l'intervenant ou utiliser le fallback
      const serviceId = this.intervenant.serviceId || (this.service === 'jardinage' ? 1 : 2);

      if (!serviceId || !this.intervenant.id) {
        this.isFavorite = false;
        return;
      }

      try {
        const response = await favoriteService.checkStatus(
          this.effectiveClientId,
          this.intervenant.id,
          serviceId
        );
        this.isFavorite = response.data?.is_favorite || false;
      } catch (error) {
        console.error('Error checking favorite status:', error);
        this.isFavorite = false;
      }
    },

    handleImageError(event) {
      const name = event.target.alt || 'User';
      const isJardinage = this.service === 'jardinage';
      const bg = isJardinage ? 'DCFCE7' : 'EBF4FF';
      const color = isJardinage ? '92B08B' : '4682B4';
      event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${bg}&color=${color}&bold=true&length=1&size=128`;
    }
  }
};
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out;
}
</style>