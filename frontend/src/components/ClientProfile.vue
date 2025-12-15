<template>
  <div class="max-w-7xl mx-auto">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold mb-6" style="color: #2f4f4f">Mon Profil</h1>

    <!-- Profile Summary Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <div class="flex items-start gap-6">
        <!-- Profile Picture -->
        <div class="relative">
          <img
            :src="previewImage || user.avatar"
            :alt="user.name"
            class="w-32 h-32 rounded-full object-cover border-4"
            style="border-color: #92b08b"
          />
          <label
            for="avatar-upload"
            class="absolute bottom-0 right-0 bg-blue-500 text-white rounded-full p-2 hover:bg-blue-600 transition-colors cursor-pointer"
            title="Modifier la photo"
          >
            <Pencil :size="16" />
          </label>
          <input
            id="avatar-upload"
            type="file"
            accept="image/jpeg,image/png,image/jpg,image/gif"
            class="hidden"
            @change="handleAvatarChange"
          />
          <div v-if="uploadingAvatar" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
          </div>
        </div>

        <!-- User Info -->
        <div class="flex-1">
          <h2 class="text-2xl font-bold mb-4" style="color: #2f4f4f">{{ user.name }}</h2>
          <div class="space-y-2 text-gray-600">
            <div class="flex items-center gap-2">
              <Mail :size="18" />
              <span>{{ user.email }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Phone :size="18" />
              <span>{{ user.phone }}</span>
            </div>
            <div class="flex items-center gap-2">
              <MapPin :size="18" />
              <span>{{ user.location }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Calendar :size="18" />
              <span>Membre depuis {{ user.memberSince }}</span>
            </div>
          </div>
        </div>

        <!-- Statistics Boxes -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-blue-50 rounded-lg p-4 border-2" style="border-color: #1a5fa3">
            <p class="text-3xl font-bold mb-1" style="color: #1a5fa3">{{ statistics.averageRating || '0' }}</p>
            <p class="text-sm text-gray-600">Note Moyenne</p>
          </div>
          <div class="bg-green-50 rounded-lg p-4 border-2" style="border-color: #92b08b">
            <p class="text-3xl font-bold mb-1" style="color: #92b08b">{{ statistics.servicesCount || 0 }}</p>
            <p class="text-sm text-gray-600">Services</p>
          </div>
          <div class="bg-orange-50 rounded-lg p-4 border-2" style="border-color: #E8793F">
            <p class="text-3xl font-bold mb-1" style="color: #E8793F">{{ statistics.totalDH || 0 }}</p>
            <p class="text-sm text-gray-600">Total DH</p>
          </div>
          <div class="bg-yellow-50 rounded-lg p-4 border-2" style="border-color: #FEE347">
            <div class="flex items-center gap-2 mb-1">
              <Heart :size="20" fill="#FF6B9D" color="#FF6B9D" />
              <p class="text-3xl font-bold" style="color: #E8793F">{{ statistics.favoritesCount || 0 }}</p>
            </div>
            <p class="text-sm text-gray-600">Favoris</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Tabs -->
    <div class="bg-white rounded-lg shadow-md mb-6">
      <div class="flex border-b border-gray-200">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-6 py-4 font-medium transition-all flex items-center gap-2"
          :class="activeTab === tab.id ? 'border-b-2' : 'text-gray-500 hover:text-gray-700'"
          :style="activeTab === tab.id ? { borderColor: tab.id === 'evaluations' ? '#E8793F' : '#1a5fa3', color: tab.id === 'evaluations' ? '#E8793F' : '#1a5fa3' } : {}"
        >
          <component :is="tab.icon" :size="18" />
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <!-- Informations Tab -->
        <div v-if="activeTab === 'informations'" class="space-y-6">
          <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">Informations personnelles</h3>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Nom complet</label>
              <input
                v-model="profileForm.name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="!isEditing"
              />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
              <input
                v-model="profileForm.email"
                type="email"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="!isEditing"
              />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Téléphone</label>
              <input
                v-model="profileForm.phone"
                type="tel"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="!isEditing"
              />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Localisation</label>
              <input
                v-model="profileForm.location"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="!isEditing"
              />
            </div>
          </div>
          <button
            @click="isEditing ? saveProfile() : (isEditing = true)"
            class="px-6 py-3 rounded-lg text-white font-semibold hover:shadow-md transition-all flex items-center gap-2"
            style="background-color: #1a5fa3"
          >
            <Pencil :size="18" />
            <span>{{ isEditing ? 'Enregistrer' : 'Modifier mes informations' }}</span>
          </button>
        </div>

        <!-- Historique Tab -->
        <div v-if="activeTab === 'historique'">
          <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">
            Historique des services ({{ history.length }})
          </h3>
          <div v-if="loadingHistory" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Chargement...</p>
          </div>
          <div v-else-if="history.length === 0" class="text-center py-12 text-gray-500">
            <p>Aucun service terminé pour le moment</p>
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="item in history"
              :key="item.id"
              class="bg-gray-50 rounded-lg p-4 flex items-center justify-between"
            >
              <div class="flex-1">
                <h4 class="font-bold mb-1" style="color: #2f4f4f">{{ item.serviceName }}</h4>
                <p class="text-sm text-gray-600">Avec {{ item.providerName }}</p>
                <p class="text-sm text-gray-500">{{ item.date }}</p>
                <div v-if="item.rating" class="flex items-center gap-1 mt-2">
                  <span class="text-sm text-gray-600">Votre note:</span>
                  <div class="flex">
                    <Star
                      v-for="i in 5"
                      :key="i"
                      :size="16"
                      :fill="i <= Math.round(item.rating) ? '#FEE347' : 'none'"
                      color="#FEE347"
                    />
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="px-4 py-2 rounded-lg text-white font-semibold mb-2" style="background-color: #1a5fa3">
                  {{ item.price }} DH
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mes Évaluations Tab -->
        <div v-if="activeTab === 'evaluations'">
          <div v-if="loadingEvaluations" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Chargement...</p>
          </div>
          <div v-else>
            <!-- Statistics Card -->
            <div class="bg-blue-600 rounded-lg p-6 mb-6 text-white">
              <h3 class="text-2xl font-bold mb-2">Vos évaluations reçues</h3>
              <p class="text-blue-100 mb-4">Ce que les intervenants disent de vous</p>
              <div class="grid grid-cols-4 gap-4">
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                  <div class="flex items-center gap-2 mb-2">
                    <Star :size="20" fill="#FEE347" color="#FEE347" />
                    <span class="text-2xl font-bold">{{ evalStatistics.averageRating || '0' }}</span>
                  </div>
                  <p class="text-sm text-blue-100">Note moyenne</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                  <div class="flex items-center gap-2 mb-2">
                    <TrendingUp :size="20" />
                    <span class="text-2xl font-bold">{{ evalStatistics.satisfactionRate || 0 }}%</span>
                  </div>
                  <p class="text-sm text-blue-100">Taux de satisfaction</p>
                  <p class="text-xs text-blue-200 mt-1">Intervenants prêts à retravailler avec vous</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                  <div class="flex items-center gap-2 mb-2">
                    <Award :size="20" />
                    <span class="text-2xl font-bold">{{ evalStatistics.clientStatus || 'N/A' }}</span>
                  </div>
                  <p class="text-sm text-blue-100">Statut client</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                  <span class="text-2xl font-bold">{{ evalStatistics.totalEvaluations || 0 }}</span>
                  <p class="text-sm text-blue-100 mt-2">Évaluations</p>
                </div>
              </div>
            </div>

            <!-- Criteria Details -->
            <div class="mb-6">
              <h4 class="text-lg font-bold mb-4" style="color: #2f4f4f">Détail des critères</h4>
              <div class="space-y-4">
                <div
                  v-for="(rating, criteria) in evalStatistics.criteriaAverages || {}"
                  :key="criteria"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center gap-3">
                    <MessageCircle :size="20" class="text-gray-500" />
                    <span class="font-medium" style="color: #2f4f4f">{{ formatCriteriaName(criteria) }}</span>
                  </div>
                  <div class="flex items-center gap-3">
                    <span class="font-semibold">{{ rating.toFixed(1) }} / 5</span>
                    <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                      <div
                        class="h-full rounded-full"
                        :style="{
                          width: `${(rating / 5) * 100}%`,
                          backgroundColor: rating >= 4 ? '#10B981' : rating >= 3 ? '#FEE347' : '#EF4444'
                        }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reviews Filter Tabs -->
            <div class="flex gap-2 mb-4 border-b border-gray-200">
              <button
                @click="reviewFilter = 'all'"
                class="px-4 py-2 font-medium transition-all"
                :class="reviewFilter === 'all' ? 'border-b-2 text-blue-600' : 'text-gray-500'"
                :style="reviewFilter === 'all' ? { borderColor: '#1a5fa3' } : {}"
              >
                Toutes ({{ evaluations.length }})
              </button>
              <button
                @click="reviewFilter = 'recent'"
                class="px-4 py-2 font-medium transition-all"
                :class="reviewFilter === 'recent' ? 'border-b-2 text-blue-600' : 'text-gray-500'"
                :style="reviewFilter === 'recent' ? { borderColor: '#1a5fa3' } : {}"
              >
                Récentes (30j)
              </button>
            </div>

            <!-- Reviews List -->
            <div v-if="filteredEvaluations.length === 0" class="text-center py-12 text-gray-500">
              <p>Aucune évaluation pour le moment</p>
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="evaluation in filteredEvaluations"
                :key="evaluation.interventionId"
                class="bg-white border border-gray-200 rounded-lg p-6"
              >
                <div class="flex items-start gap-4 mb-4">
                  <img
                    :src="evaluation.intervenantImage"
                    :alt="evaluation.intervenantName"
                    class="w-16 h-16 rounded-full object-cover"
                  />
                  <div class="flex-1">
                    <h4 class="font-bold mb-1" style="color: #2f4f4f">{{ evaluation.intervenantName }}</h4>
                    <p class="text-sm text-gray-600 mb-2">{{ evaluation.serviceName }}</p>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                      <Calendar :size="14" />
                      <span>{{ evaluation.date }}</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="flex items-center gap-1 mb-2">
                      <Star :size="20" fill="#FEE347" color="#FEE347" />
                      <span class="text-xl font-bold">{{ evaluation.overallRating }}</span>
                    </div>
                    <div
                      v-if="evaluation.recommends"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-medium"
                      style="background-color: #10B981; color: white"
                    >
                      <Check :size="14" />
                      Recommande
                    </div>
                  </div>
                </div>

                <!-- Criteria Ratings -->
                <div class="grid grid-cols-5 gap-2 mb-4">
                  <div
                    v-for="(rating, criteria) in evaluation.criteriaRatings"
                    :key="criteria"
                    class="flex items-center gap-1 text-sm"
                  >
                    <Star :size="14" fill="#FEE347" color="#FEE347" />
                    <span>{{ formatCriteriaName(criteria) }}: {{ rating }}</span>
                  </div>
                </div>

                <!-- Comment -->
                <div v-if="evaluation.comment" class="flex items-start gap-2 text-gray-700">
                  <MessageCircle :size="18" class="text-gray-400 mt-1" />
                  <p class="italic">"{{ evaluation.comment }}"</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mes Favoris Tab -->
        <div v-if="activeTab === 'favorites'">
          <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">
            Intervenants favoris ({{ favorites.length }})
          </h3>
          <div v-if="loadingFavorites" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Chargement...</p>
          </div>
          <div v-else-if="favorites.length === 0" class="text-center py-12 text-gray-500">
            <p>Aucun favori pour le moment</p>
          </div>
          <div v-else class="grid grid-cols-2 gap-4">
            <div
              v-for="favorite in favorites"
              :key="favorite.id"
              class="bg-white border-2 rounded-lg p-4"
              style="border-color: #92b08b"
            >
              <div class="flex items-start gap-4">
                <img
                  :src="favorite.image"
                  :alt="favorite.name"
                  class="w-20 h-20 rounded-full object-cover"
                />
                <div class="flex-1">
                  <h4 class="font-bold mb-1" style="color: #2f4f4f">{{ favorite.name }}</h4>
                  <p class="text-sm text-gray-600 mb-2">
                    {{ favorite.services && favorite.services.length > 0 ? favorite.services[0] : 'Service' }}
                  </p>
                  <p class="text-sm text-gray-500 mb-3">
                    {{ favorite.servicesWithClient || 0 }} services ensemble
                  </p>
                  <div class="flex items-center gap-2">
                    <Heart :size="20" fill="#FF6B9D" color="#FF6B9D" />
                    <div class="flex items-center gap-1">
                      <Star :size="16" fill="#FEE347" color="#FEE347" />
                      <span class="font-semibold">{{ favorite.averageRating || 'N/A' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  User,
  Clock,
  Star,
  Heart,
  Mail,
  Phone,
  MapPin,
  Calendar,
  Pencil,
  MessageCircle,
  TrendingUp,
  Award,
  Check
} from 'lucide-vue-next';
import profileService from '@/services/profileService';
import authService from '@/services/authService';

export default {
  name: 'ClientProfile',
  components: {
    User,
    Clock,
    Star,
    Heart,
    Mail,
    Phone,
    MapPin,
    Calendar,
    Pencil,
    MessageCircle,
    TrendingUp,
    Award,
    Check
  },
  props: {
    clientId: {
      type: Number,
      required: true
    },
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      activeTab: 'informations',
      tabs: [
        { id: 'informations', label: 'Informations', icon: 'User' },
        { id: 'historique', label: 'Historique', icon: 'Clock' },
        { id: 'evaluations', label: 'Mes Évaluations', icon: 'Star' },
        { id: 'favorites', label: 'Mes Favoris', icon: 'Heart' }
      ],
      statistics: {
        averageRating: 0,
        servicesCount: 0,
        totalDH: 0,
        favoritesCount: 0
      },
      history: [],
      evaluations: [],
      evalStatistics: {
        averageRating: 0,
        satisfactionRate: 0,
        clientStatus: 'N/A',
        totalEvaluations: 0,
        criteriaAverages: {}
      },
      favorites: [],
      loadingHistory: false,
      loadingEvaluations: false,
      loadingFavorites: false,
      isEditing: false,
      profileForm: {
        name: '',
        email: '',
        phone: '',
        location: ''
      },
      reviewFilter: 'all',
      previewImage: null,
      uploadingAvatar: false
    };
  },
  computed: {
    filteredEvaluations() {
      if (this.reviewFilter === 'recent') {
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        return this.evaluations.filter(evaluation => {
          const evalDate = new Date(evaluation.date);
          return evalDate >= thirtyDaysAgo;
        });
      }
      return this.evaluations;
    }
  },
  watch: {
    activeTab(newTab) {
      if (newTab === 'historique' && this.history.length === 0) {
        this.loadHistory();
      } else if (newTab === 'evaluations' && this.evaluations.length === 0) {
        this.loadEvaluations();
      } else if (newTab === 'favorites' && this.favorites.length === 0) {
        this.loadFavorites();
      }
    }
  },
  mounted() {
    this.loadStatistics();
    this.loadProfileForm();
  },
  methods: {
    async loadStatistics() {
      try {
        const response = await profileService.getStatistics(this.clientId);
        this.statistics = response.data;
      } catch (error) {
        console.error('Error loading statistics:', error);
      }
    },
    async loadHistory() {
      this.loadingHistory = true;
      try {
        const response = await profileService.getHistory(this.clientId);
        this.history = response.data.data || [];
      } catch (error) {
        console.error('Error loading history:', error);
      } finally {
        this.loadingHistory = false;
      }
    },
    async loadEvaluations() {
      this.loadingEvaluations = true;
      try {
        const response = await profileService.getEvaluations(this.clientId);
        this.evaluations = response.data.data || [];
        this.evalStatistics = response.data.statistics || {};
      } catch (error) {
        console.error('Error loading evaluations:', error);
      } finally {
        this.loadingEvaluations = false;
      }
    },
    async loadFavorites() {
      this.loadingFavorites = true;
      try {
        const response = await profileService.getFavorites(this.clientId);
        this.favorites = response.data.data || [];
      } catch (error) {
        console.error('Error loading favorites:', error);
      } finally {
        this.loadingFavorites = false;
      }
    },
    loadProfileForm() {
      this.profileForm = {
        name: this.user.name,
        email: this.user.email,
        phone: this.user.phone,
        location: this.user.location
      };
    },
      async saveProfile() {
      try {
        // Ensure all values are strings
        const profileData = {
          nom: String(this.profileForm.name.split(' ')[0] || ''),
          prenom: String(this.profileForm.name.split(' ').slice(1).join(' ') || ''),
          email: String(this.profileForm.email || ''),
          telephone: String(this.profileForm.phone || ''),
          ville: String(this.profileForm.location || '') // This is the fix
        };
        
        console.log('📤 Sending profile data:', profileData); // Debug log
        
        await authService.updateProfile(profileData);
        this.isEditing = false;
        this.$emit('profile-updated');
      } catch (error) {
        console.error('Error saving profile:', error);
        
        // Show more detailed error message
        if (error.response?.data?.errors?.ville) {
          alert(`Erreur ville: ${error.response.data.errors.ville[0]}`);
        } else {
          alert('Erreur lors de la sauvegarde');
        }
      }
    },
    async handleAvatarChange(event) {
      const file = event.target.files[0];
      
      if (!file) {
        return;
      }

      // Validate file type
      const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
      if (!validTypes.includes(file.type)) {
        alert('Veuillez sélectionner une image valide (JPEG, PNG, JPG ou GIF)');
        return;
      }

      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert('L\'image ne doit pas dépasser 2MB');
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = (e) => {
        this.previewImage = e.target.result;
      };
      reader.readAsDataURL(file);

      // Upload to server
      this.uploadingAvatar = true;
      try {
        const response = await authService.updateAvatar(file);
        
        // Update user avatar with the new URL
        this.user.avatar = response.data.url;
        this.previewImage = null; // Clear preview since we have the new URL
        
        // Emit event to update parent component
        this.$emit('profile-updated');
        
        alert('Photo de profil mise à jour avec succès !');
      } catch (error) {
        console.error('Error uploading avatar:', error);
        this.previewImage = null; // Clear preview on error
        alert(error.response?.data?.message || 'Erreur lors de l\'upload de la photo');
      } finally {
        this.uploadingAvatar = false;
        // Reset file input
        event.target.value = '';
      }
    },
    formatCriteriaName(criteria) {
      const names = {
        'communication': 'Communication',
        'ponctualité': 'Ponctualité',
        'préparation_du_jardin': 'Préparation du jardin',
        'respect_et_courtoisie': 'Respect et courtoisie',
        'paiement': 'Paiement',
        'qualité_du_travail': 'Qualité du travail',
        'professionnalisme': 'Professionnalisme'
      };
      return names[criteria] || criteria;
    }
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>