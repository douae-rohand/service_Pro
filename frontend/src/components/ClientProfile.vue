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
            v-if="currentUser"
            :src="previewImage || currentUser.avatar || currentUser.profilePhoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentUser.name) + '&background=4682B4&color=fff'"
            :alt="currentUser.name"
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
          <h2 v-if="currentUser" class="text-2xl font-bold mb-4" style="color: #2f4f4f">{{ currentUser.name }}</h2>
          <div v-if="currentUser" class="space-y-2 text-gray-600">
            <div class="flex items-center gap-2">
              <Mail :size="18" />
              <span>{{ currentUser.email }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Phone :size="18" />
              <span>{{ currentUser.phone || 'Non renseigné' }}</span>
            </div>
            <div class="flex items-center gap-2">
              <MapPin :size="18" />
              <span>{{ currentUser.location || 'Non renseigné' }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Calendar :size="18" />
              <span>Membre depuis {{ currentUser.memberSince }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Profile Tabs -->
    <div v-if="currentUser" class="bg-white rounded-lg shadow-md mb-6">
      <div class="flex border-b border-gray-200 overflow-x-auto">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-6 py-4 font-medium transition-all flex items-center gap-2 whitespace-nowrap"
          :class="activeTab === tab.id ? 'border-b-2' : 'text-gray-500 hover:text-gray-700'"
          :style="activeTab === tab.id ? { borderColor: getTabColor(tab.id), color: getTabColor(tab.id) } : {}"
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
          <MyFavoritesTab v-if="clientId" :client-id="clientId" />
        </div>

        <!-- Mes Réclamations Tab -->
        <div v-if="activeTab === 'reclamations'">
          <ClientReclamationsTab />
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
  AlertTriangle
} from 'lucide-vue-next';
import profileService from '@/services/profileService';
import authService from '@/services/authService';
import MyFavoritesTab from './MyFavoritesTab.vue';
import ClientReclamationsTab from './client/ClientReclamationsTab.vue';

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
    AlertTriangle,
    MyFavoritesTab,
    ClientReclamationsTab
  },
  props: {
    clientId: {
      type: Number,
      required: false
    },
    user: {
      type: Object,
      required: false,
      default: null
    }
  },
  data() {
    return {
      activeTab: 'informations',
      tabs: [
        { id: 'informations', label: 'Informations', icon: 'User' },
        { id: 'historique', label: 'Historique', icon: 'Clock' },
        { id: 'evaluations', label: 'Mes Évaluations', icon: 'Star' },
        { id: 'favorites', label: 'Mes Favoris', icon: 'Heart' },
        { id: 'reclamations', label: 'Mes Réclamations', icon: 'AlertTriangle' }
      ],
      currentUser: null,
      loading: false,
      clientId: null,
      history: [],
      evaluations: [],
      evalStatistics: {
        averageRating: 0,
        satisfactionRate: 0,
        clientStatus: 'N/A',
        totalEvaluations: 0
      },
      loadingHistory: false,
      loadingEvaluations: false,
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
  watch: {
    activeTab(newTab) {
      if (newTab === 'historique' && this.history.length === 0) {
        this.loadHistory();
      } else if (newTab === 'evaluations' && this.evaluations.length === 0) {
        this.loadEvaluations();
      }
    }
  },
  async mounted() {
    await this.loadUserData();
    if (this.clientId) {
      this.loadHistory();
      this.loadEvaluations();
    }
  },
  methods: {
    async loadUserData() {
      this.loading = true;
      try {
        const response = await authService.getCurrentUser();
        const userData = response.data;
        
        if (!userData) {
          console.error('No user data received');
          return;
        }
        
        // Construire l'objet currentUser avec les données de la base de données
        this.currentUser = {
          id: userData.id,
          name: `${userData.prenom || ''} ${userData.nom || ''}`.trim() || userData.email,
          email: userData.email || '',
          phone: userData.telephone || '',
          location: userData.client?.ville || userData.client?.address || userData.address || '',
          avatar: userData.url || userData.profile_photo || userData.avatar || null,
          profilePhoto: userData.profile_photo || userData.url || null,
          memberSince: userData.created_at ? new Date(userData.created_at).getFullYear().toString() : '2023',
          address: userData.client?.address || userData.address || '',
          ville: userData.client?.ville || userData.ville || '',
          nom: userData.nom || '',
          prenom: userData.prenom || ''
        };
        
        // Définir clientId si disponible
        if (userData.client?.id) {
          this.clientId = userData.client.id;
        } else if (this.clientId) {
          // Utiliser le prop si disponible
        } else {
          this.clientId = userData.id;
        }
        
        // Charger le formulaire avec les données de l'utilisateur
        this.loadProfileForm();
        
      } catch (error) {
        console.error('Error loading user data:', error);
        alert('Erreur lors du chargement des données utilisateur');
      } finally {
        this.loading = false;
      }
    },
    getTabColor(tabId) {
      if (tabId === 'evaluations') return '#E8793F';
      if (tabId === 'reclamations') return '#DC2626';
      return '#1a5fa3';
    },
    async loadHistory() {
      if (!this.clientId) return;
      
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
      if (!this.clientId) return;
      
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
    loadProfileForm() {
      if (this.currentUser) {
        this.profileForm = {
          name: this.currentUser.name,
          email: this.currentUser.email,
          phone: this.currentUser.phone,
          location: this.currentUser.location || this.currentUser.ville || ''
        };
      }
    },
    async saveProfile() {
      try {
        // Séparer le nom complet en nom et prénom
        const nameParts = this.profileForm.name.trim().split(/\s+/);
        const nom = nameParts.length > 0 ? nameParts[0] : this.currentUser?.nom || '';
        const prenom = nameParts.length > 1 ? nameParts.slice(1).join(' ') : this.currentUser?.prenom || '';
        
        const profileData = {
          nom: nom,
          prenom: prenom,
          email: String(this.profileForm.email || ''),
          telephone: String(this.profileForm.phone || ''),
          ville: String(this.profileForm.location || ''),
          address: String(this.profileForm.location || '') // Certains systèmes utilisent address au lieu de ville
        };
        
        const response = await authService.updateProfile(profileData);
        
        // Mettre à jour currentUser avec les nouvelles données
        if (response.data?.user) {
          const updatedUser = response.data.user;
          this.currentUser.name = `${updatedUser.prenom || ''} ${updatedUser.nom || ''}`.trim();
          this.currentUser.email = updatedUser.email || '';
          this.currentUser.phone = updatedUser.telephone || '';
          this.currentUser.location = updatedUser.client?.ville || updatedUser.client?.address || updatedUser.address || '';
          this.currentUser.ville = updatedUser.client?.ville || updatedUser.ville || '';
          this.currentUser.address = updatedUser.client?.address || updatedUser.address || '';
          this.currentUser.nom = updatedUser.nom || '';
          this.currentUser.prenom = updatedUser.prenom || '';
        }
        
        this.isEditing = false;
        this.loadProfileForm(); // Recharger le formulaire avec les données mises à jour
        this.$emit('profile-updated');
        alert('Profil mis à jour avec succès !');
      } catch (error) {
        console.error('Error saving profile:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de la sauvegarde';
        alert(errorMessage);
      }
    },
    async handleAvatarChange(event) {
      const file = event.target.files[0];
      
      if (!file) return;

      const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
      if (!validTypes.includes(file.type)) {
        alert('Veuillez sélectionner une image valide (JPEG, PNG, JPG ou GIF)');
        return;
      }

      if (file.size > 2 * 1024 * 1024) {
        alert('L\'image ne doit pas dépasser 2MB');
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        this.previewImage = e.target.result;
      };
      reader.readAsDataURL(file);

      this.uploadingAvatar = true;
      try {
        const response = await authService.updateAvatar(file);
        
        // Mettre à jour currentUser avec la nouvelle URL de l'avatar
        if (response.data?.url) {
          this.currentUser.avatar = response.data.url;
          this.currentUser.profilePhoto = response.data.url;
          this.currentUser.url = response.data.url;
        }
        
        // Recharger les données utilisateur depuis la base de données pour être sûr
        await this.loadUserData();
        
        this.previewImage = null;
        this.$emit('profile-updated');
        alert('Photo de profil mise à jour avec succès !');
      } catch (error) {
        console.error('Error uploading avatar:', error);
        this.previewImage = null;
        const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de l\'upload de la photo';
        alert(errorMessage);
      } finally {
        this.uploadingAvatar = false;
        event.target.value = '';
      }
    }
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
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
