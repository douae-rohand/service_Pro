<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-3xl font-bold mb-2" style="color: #2f4f4f">Mes Favoris</h2>
      <div class="flex items-center justify-between">
        <p class="text-gray-600">
          Retrouvez rapidement vos intervenants préférés pour vos prochains services
        </p>
        <div class="bg-green-50 border-2 rounded-lg px-4 py-2" style="border-color: #92b08b">
          <span class="font-bold" style="color: #2f4f4f">{{ favorites.length }} Favoris</span>
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
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div
        v-for="favorite in favorites"
        :key="favorite.id"
        class="bg-white rounded-lg shadow-md p-6 border-2 hover:shadow-lg transition-all relative"
        style="border-color: #92b08b"
      >
        <!-- Favorite Icon -->
        <button
          @click="removeFavorite(favorite.id)"
          class="absolute top-4 right-4 text-orange-500 hover:text-red-500 transition-colors"
        >
          <Heart :size="24" :fill="'currentColor'" />
        </button>

        <!-- Intervenant Info -->
        <div class="flex items-start gap-4 mb-4">
          <img
            :src="favorite.image"
            :alt="favorite.name"
            class="w-20 h-20 rounded-full object-cover border-2"
            style="border-color: #92b08b"
          />
          <div class="flex-1">
            <h3 class="text-xl font-bold mb-1" style="color: #2f4f4f">{{ favorite.name }}</h3>
            <div class="flex items-center gap-2 mb-2">
              <Star :size="16" fill="#FEE347" color="#FEE347" />
              <span class="font-semibold">{{ favorite.averageRating || 'N/A' }}</span>
              <span class="text-gray-500 text-sm">({{ favorite.totalReviews }} avis)</span>
            </div>
            <div class="flex items-center gap-1 text-gray-600">
              <MapPin :size="14" />
              <span>{{ favorite.location }}</span>
            </div>
          </div>
        </div>

        <!-- Service Tags -->
        <div class="flex flex-wrap gap-2 mb-4">
          <span
            v-for="service in favorite.services"
            :key="service"
            class="px-3 py-1 rounded-full text-sm"
            :class="getServiceColor(service)"
          >
            {{ service }}
          </span>
          <span
            class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700"
          >
            Disponible
          </span>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
          <div>
            <span class="text-gray-600">Services avec vous:</span>
            <span class="ml-2 px-2 py-1 rounded bg-green-100 text-green-700 font-semibold">
              {{ favorite.servicesWithClient }}
            </span>
          </div>
          <div>
            <span class="text-gray-600">Total missions:</span>
            <span class="ml-2 font-semibold">{{ favorite.totalMissions }}</span>
          </div>
          <div>
            <span class="text-gray-600">Dernier service:</span>
            <span class="ml-2 font-semibold">
              {{ favorite.lastServiceDate ? formatDate(favorite.lastServiceDate) : 'N/A' }}
            </span>
          </div>
          <div>
            <span class="text-gray-600">Tarif:</span>
            <span class="ml-2 px-2 py-1 rounded bg-blue-100 text-blue-700 font-semibold">
              {{ favorite.hourlyRate ? `${favorite.hourlyRate} DH/h` : 'N/A' }}
            </span>
          </div>
        </div>

        <!-- Book Button -->
        <button
          @click="openBookingModal(favorite)"
          class="w-full py-3 rounded-lg text-white font-semibold hover:shadow-md transition-all flex items-center justify-center gap-2"
          style="background-color: #92b08b"
        >
          <Calendar :size="20" />
          Réserver maintenant
        </button>
      </div>
    </div>

    <!-- Tip Box -->
    <div class="bg-green-50 border-l-4 rounded-lg p-6 mb-6" style="border-color: #92b08b">
      <div class="flex items-start gap-3">
        <div class="bg-yellow-100 rounded-full p-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="font-bold mb-2" style="color: #1a5fa3">Astuce</h4>
          <p class="text-gray-700">
            Vos intervenants favoris sont notifiés en priorité de vos nouvelles demandes de service. 
            Ils peuvent vous accorder des tarifs préférentiels et une meilleure disponibilité !
          </p>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <BookingModal
      v-if="selectedIntervenant"
      :intervenant="selectedIntervenant"
      :client-id="clientId"
      @close="closeBookingModal"
      @success="handleBookingSuccess"
    />
  </div>
</template>

<script>
import { Heart, Star, MapPin, Calendar, AlertCircle } from 'lucide-vue-next';
import favoriteService from '@/services/favoriteService';
import BookingModal from './BookingModal.vue';

export default {
  name: 'MyFavoritesTab',
  components: {
    Heart,
    Star,
    MapPin,
    Calendar,
    AlertCircle,
    BookingModal
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
      error: null,
      selectedIntervenant: null
    };
  },
  mounted() {
    this.loadFavorites();
  },
  methods: {
    async loadFavorites() {
      this.loading = true;
      this.error = null;
      try {
        const response = await favoriteService.getFavorites(this.clientId);
        this.favorites = response.data.data || [];
      } catch (error) {
        console.error('Error loading favorites:', error);
        this.error = error.response?.data?.message || 'Erreur lors du chargement des favoris';
      } finally {
        this.loading = false;
      }
    },
    async removeFavorite(intervenantId) {
      if (!confirm('Êtes-vous sûr de vouloir retirer cet intervenant de vos favoris ?')) {
        return;
      }

      try {
        await favoriteService.removeFavorite(this.clientId, intervenantId);
        await this.loadFavorites();
      } catch (error) {
        console.error('Error removing favorite:', error);
        alert('Erreur lors de la suppression du favori');
      }
    },
    openBookingModal(intervenant) {
      this.selectedIntervenant = intervenant;
    },
    closeBookingModal() {
      this.selectedIntervenant = null;
    },
    handleBookingSuccess() {
      this.closeBookingModal();
      this.loadFavorites(); // Refresh to update last service date
    },
    getServiceColor(service) {
      const colors = {
        'Jardinage': 'bg-green-100 text-green-700',
        'Ménage': 'bg-orange-100 text-orange-700',
        'Plomberie': 'bg-blue-100 text-blue-700',
        'Électricité': 'bg-yellow-100 text-yellow-700',
      };
      return colors[service] || 'bg-gray-100 text-gray-700';
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR');
    }
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>


