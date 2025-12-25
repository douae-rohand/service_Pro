<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <div class="flex items-center justify-between">
        <h2 class="text-3xl font-bold" style="color: #2f4f4f">Mes Favoris</h2>
        <div class="bg-blue-50 border-2 rounded-lg px-4 py-2" style="border-color: #4682B4">
          <span class="font-bold" style="color: #4682B4">{{ favorites.length }} Favoris</span>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <!-- <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement des favoris...</p>
    </div> -->

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600 mb-6">
      <AlertCircle :size="20" class="inline mr-2" />
      {{ error }}
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && !error && favorites.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
      <Heart :size="64" class="mx-auto mb-4 text-gray-400" />
      <h3 class="text-xl font-bold mb-2" style="color: #2f4f4f">
        Aucun favori pour le moment
      </h3>
      <p class="text-gray-600 mb-4">
        Ajoutez des intervenants à vos favoris pour les retrouver facilement.
      </p>
    </div>

    <!-- Favorites Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="favorite in favorites"
        :key="`${favorite.id}-${favorite.service_id}`"
        class="bg-white rounded-xl shadow-md p-6 border-2 hover:shadow-xl transition-all duration-300 relative transform hover:-translate-y-1"
        style="border-color: #4682B4"
      >
        <!-- Favorite Icon -->
        <button
          @click="removeFavorite(favorite)"
          class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors p-2 hover:bg-red-50 rounded-full"
          title="Retirer des favoris"
        >
          <Heart :size="22" :fill="'currentColor'" />
        </button>

        <!-- Intervenant Info -->
        <div class="flex items-start gap-4 mb-5">
          <div class="relative">
            <img
              :src="favorite.image"
              :alt="favorite.name"
              @error="handleImageError"
              class="w-24 h-24 rounded-full object-cover border-3 shadow-md"
              style="border-color: #4682B4"
            />
          </div>
          <div class="flex-1 pt-1">
            <h3 class="text-xl font-bold mb-2" style="color: #2f4f4f">{{ favorite.name }}</h3>
            <div class="flex items-center gap-2 mb-2">
              <Star :size="18" fill="#FEE347" color="#FEE347" />
              <span class="font-semibold text-gray-800">{{ favorite.averageRating || 'N/A' }}</span>
              <span class="text-gray-500 text-sm">({{ favorite.totalReviews }} avis)</span>
            </div>
            <div class="flex items-center gap-1 text-gray-600 text-sm">
              <MapPin :size="14" />
              <span>{{ favorite.location }}</span>
            </div>
          </div>
        </div>

        <!-- Service Tags -->
        <div class="flex flex-wrap gap-2 mb-5">
          <span
            v-for="service in favorite.services"
            :key="service"
            class="px-3 py-1.5 rounded-full text-sm font-medium shadow-sm"
            :class="getServiceColor(service)"
          >
            {{ service }}
          </span>
        </div>

        <!-- Statistics -->
        <div class="bg-blue-50 rounded-lg p-4 mb-5" style="background-color: #E8F4FD">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="flex flex-col">
              <span class="text-gray-600 text-xs mb-1">Services avec vous</span>
              <span class="text-lg font-bold" style="color: #4682B4">
                {{ favorite.servicesWithClient }}
              </span>
            </div>
            <div class="flex flex-col">
              <span class="text-gray-600 text-xs mb-1">Total missions</span>
              <span class="text-lg font-bold text-gray-800">{{ favorite.totalMissions }}</span>
            </div>
          </div>
        </div></div>
    </div>

  </div>
</template>

<script>
import { Heart, Star, MapPin, Calendar, AlertCircle } from 'lucide-vue-next';
import favoriteService from '@/services/favoriteService';
import api from '@/services/api';

// Base URL of backend (without the `/api` suffix used for API routes)
const API_BASE_URL = (api.defaults.baseURL || '').replace(/\/+$/, '');
const BACKEND_BASE_URL = API_BASE_URL.replace(/\/api$/, '');

export default {
  name: 'MyFavoritesTab',
  components: {
    Heart,
    Star,
    MapPin,
    Calendar,
    AlertCircle
  },
  props: {
    clientId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      favorites: [],
      loading: false,
      error: null
    };
  },
  mounted() {
    this.loadFavorites();
  },
  methods: {
    buildImageUrl(photoPath) {
      // If nothing is provided, return a placeholder
      if (!photoPath) {
        return 'https://via.placeholder.com/150';
      }

      // Absolute URL: return as-is
      if (/^https?:\/\//i.test(photoPath)) {
        return photoPath;
      }

      // Relative path from Laravel storage/public
      const base = BACKEND_BASE_URL || '';
      const normalizedPath = photoPath.startsWith('/') ? photoPath : `/${photoPath}`;
      return `${base}${normalizedPath}`;
    },
    async loadFavorites() {
      this.loading = true;
      this.error = null;
      try {
        const response = await favoriteService.getFavorites(this.clientId);
        const rawFavorites = response.data.data || [];
        
        // Map backend data to frontend expected format
        this.favorites = rawFavorites.map(fav => ({
          ...fav,
          name: `${fav.prenom || ''} ${fav.nom || ''}`.trim(),
          image: this.getIntervenantImage(fav),
          services: [fav.nom_service],
          location: fav.ville || fav.adresse || 'Non spécifiée',
          averageRating: fav.note_globale || 0,
          totalReviews: fav.nombre_avis || 0,
          servicesWithClient: fav.services_avec_client || 0,
          totalMissions: fav.total_missions || 0,
          hourlyRate: fav.tarif_horaire || null,
          lastServiceDate: fav.dernier_service || null
        }));
      } catch (error) {
        console.error('Error loading favorites:', error);
        this.error = error.response?.data?.message || 'Erreur lors du chargement des favoris';
      } finally {
        this.loading = false;
      }
    },
    getIntervenantImage(fav) {
      // Logic from ClientProfile.vue adapted for favorites objects
      // Favorites object from FavoritesController usually has: photo_url, profile_photo, url
      const img = fav.photo_url || fav.profile_photo || fav.url || fav.avatar;
      const name = `${fav.prenom || ''} ${fav.nom || ''}`.trim() || 'Intervenant';
      
      if (!img || img === 'https://via.placeholder.com/150' || img.includes('unsplash')) {
         const encodedName = encodeURIComponent(name);
         return `https://ui-avatars.com/api/?name=${encodedName}&background=E5E7EB&color=6B7280`;
      }
      
      if (img.startsWith('http')) return img;
      
      // Handle relative paths
      const basePath = 'http://127.0.0.1:8000';
      const cleanPath = img.replace(/^\/+/, '').replace(/^storage\//, '');
      return `${basePath}/storage/${cleanPath}`;
    },
    async removeFavorite(favorite) {
      if (!confirm(`Êtes-vous sûr de vouloir retirer ${favorite.name} de vos favoris pour le service ${favorite.nom_service} ?`)) {
        return;
      }

      try {
        await favoriteService.removeFavorite(this.clientId, favorite.id, favorite.service_id);
        await this.loadFavorites();
      } catch (error) {
        console.error('Error removing favorite:', error);
        alert('Erreur lors de la suppression du favori');
      }
    },

    getServiceColor(service) {
      const colors = {
        'Jardinage': 'bg-green-100 text-green-700',
        'Ménage': 'bg-blue-100 text-blue-700',
        'Plomberie': 'bg-blue-100 text-blue-700',
        'Électricité': 'bg-blue-100 text-blue-700',
      };
      return colors[service] || 'bg-blue-100 text-blue-700';
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR');
    },
    handleImageError(event) {
      event.target.src = 'https://ui-avatars.com/api/?name=User&background=random';
    }
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>


