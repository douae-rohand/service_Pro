<template>
  <div class="min-h-screen bg-gray-50/50 animate-fade-in pb-12">
    <!-- Header Page Title Background -->
    <div class="bg-white border-b border-gray-100 mb-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
         <h1 class="text-3xl font-bold bg-gradient-to-r from-[#2f4f4f] to-[#4682B4] bg-clip-text text-transparent">Mon Profil</h1>
         <p class="text-gray-500 mt-2">Gérez vos informations personnelles et consultez votre activité.</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Profile Summary Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 relative overflow-hidden group">
        <!-- Decorative background element -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#4682B4]/5 to-[#92B08B]/5 rounded-bl-full -mr-16 -mt-16 transition-transform duration-700 group-hover:scale-110"></div>

        <div class="flex flex-col md:flex-row items-center md:items-start gap-8 relative z-10">
          <!-- Profile Picture -->
          <div class="relative group-avatar">
            <div class="w-32 h-32 rounded-full p-1 bg-gradient-to-br from-[#4682B4] to-[#92B08B] shadow-lg">
              <img
                v-if="currentUser"
                :src="previewImage || currentUser.avatar || currentUser.profilePhoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentUser.name) + '&background=4682B4&color=fff'"
                :alt="currentUser.name"
                class="w-full h-full rounded-full object-cover border-4 border-white bg-white"
              />
            </div>
            <label
              for="avatar-upload"
              class="absolute bottom-1 right-1 bg-white text-[#4682B4] rounded-full p-2.5 shadow-md border border-gray-100 hover:bg-[#4682B4] hover:text-white transition-all cursor-pointer transform hover:scale-110 active:scale-95"
              title="Modifier la photo"
            >
              <Pencil :size="16" stroke-width="2.5" />
            </label>
            <input
              id="avatar-upload"
              type="file"
              accept="image/jpeg,image/png,image/jpg,image/gif"
              class="hidden"
              @change="handleAvatarChange"
            />
            <div v-if="uploadingAvatar" class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center backdrop-blur-sm">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            </div>
          </div>

          <!-- User Info -->
          <div class="flex-1 text-center md:text-left">
            <div class="mb-4">
              <h2 v-if="currentUser" class="text-3xl font-bold text-gray-800 mb-1">{{ currentUser.name }}</h2>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                Client Vérifié
              </span>
            </div>
            
            <div v-if="currentUser" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-600">
              <div class="flex items-center gap-3 justify-center md:justify-start bg-gray-50 p-3 rounded-lg border border-gray-100 hover:bg-blue-50/50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-[#4682B4]">
                  <Mail :size="16" />
                </div>
                <span class="font-medium text-sm">{{ currentUser.email }}</span>
              </div>
              <div class="flex items-center gap-3 justify-center md:justify-start bg-gray-50 p-3 rounded-lg border border-gray-100 hover:bg-green-50/50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-[#92B08B]">
                  <Phone :size="16" />
                </div>
                <span class="font-medium text-sm">{{ currentUser.phone || 'Non renseigné' }}</span>
              </div>
              <div class="flex items-center gap-3 justify-center md:justify-start bg-gray-50 p-3 rounded-lg border border-gray-100 hover:bg-purple-50/50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                   <MapPin :size="16" />
                </div>
                <span class="font-medium text-sm">{{ currentUser.location || 'Non renseigné' }}</span>
              </div>
              <div class="flex items-center gap-3 justify-center md:justify-start bg-gray-50 p-3 rounded-lg border border-gray-100 hover:bg-yellow-50/50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                   <Calendar :size="16" />
                </div>
                <span class="font-medium text-sm">Membre depuis {{ currentUser.memberSince }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Tabs -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100 bg-gray-50/30">
          <div class="flex overflow-x-auto no-scrollbar p-2 gap-2">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="px-5 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 whitespace-nowrap text-sm"
              :class="activeTab === tab.id ? 'bg-white text-[#4682B4] shadow-sm ring-1 ring-gray-100' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50/80'"
            >
              <component :is="tab.icon" :size="18" :class="activeTab === tab.id ? 'text-[#4682B4]' : 'text-gray-400'" />
              <span>{{ tab.label }}</span>
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="p-8 min-h-[400px]">
          <!-- Informations Tab -->
          <div v-if="activeTab === 'informations'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="flex items-center justify-between">
               <h3 class="text-xl font-bold text-gray-800">Informations personnelles</h3>
               <button
                  @click="isEditing ? saveProfile() : (isEditing = true)"
                  class="px-5 py-2.5 rounded-xl font-semibold transition-all flex items-center gap-2 shadow-sm border"
                  :class="isEditing ? 'bg-[#4682B4] text-white border-transparent hover:bg-[#36648B] hover:shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                >
                  <Pencil v-if="!isEditing" :size="16" />
                  <Check v-else :size="16" />
                  <span>{{ isEditing ? 'Enregistrer les modifications' : 'Modifier' }}</span>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-2 group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider group-focus-within:text-[#4682B4]">Nom complet</label>
                <div class="relative">
                   <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <User :size="18" class="text-gray-400" />
                   </div>
                   <input
                    v-model="profileForm.name"
                    type="text"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#4682B4]/10 focus:border-[#4682B4] focus:bg-white transition-all outline-none font-medium text-gray-800 disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="!isEditing"
                    placeholder="Votre nom"
                  />
                </div>
              </div>
              
              <div class="space-y-2 group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider group-focus-within:text-[#4682B4]">Email</label>
                <div class="relative">
                   <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <Mail :size="18" class="text-gray-400" />
                   </div>
                   <input
                    v-model="profileForm.email"
                    type="email"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#4682B4]/10 focus:border-[#4682B4] focus:bg-white transition-all outline-none font-medium text-gray-800 disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="!isEditing"
                    placeholder="Votre email"
                  />
                </div>
              </div>

              <div class="space-y-2 group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider group-focus-within:text-[#4682B4]">Téléphone</label>
                <div class="relative">
                   <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <Phone :size="18" class="text-gray-400" />
                   </div>
                   <input
                    v-model="profileForm.phone"
                    type="tel"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#4682B4]/10 focus:border-[#4682B4] focus:bg-white transition-all outline-none font-medium text-gray-800 disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="!isEditing"
                    placeholder="Votre numéro"
                  />
                </div>
              </div>

              <div class="space-y-2 group">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider group-focus-within:text-[#4682B4]">Localisation</label>
                <div class="relative">
                   <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <MapPin :size="18" class="text-gray-400" />
                   </div>
                   <input
                    v-model="profileForm.location"
                    type="text"
                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#4682B4]/10 focus:border-[#4682B4] focus:bg-white transition-all outline-none font-medium text-gray-800 disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="!isEditing"
                    placeholder="Votre ville ou adresse"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Historique Tab -->
          <div v-if="activeTab === 'historique'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <h3 class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
               <Clock :size="24" class="text-[#4682B4]" />
               Historique des services 
               <span class="text-sm font-normal text-gray-500 ml-2 bg-gray-100 px-2 py-0.5 rounded-full">{{ history.length }}</span>
            </h3>
            
            <div v-if="loadingHistory" class="flex flex-col items-center justify-center py-12">
              <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#4682B4]"></div>
              <p class="mt-4 text-gray-500 font-medium">Récupération de votre historique...</p>
            </div>
            
            <div v-else-if="history.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-gray-100 rounded-2xl bg-gray-50/50">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
                 <Clock :size="32" />
              </div>
              <p class="text-lg font-bold text-gray-700">Aucun service terminé</p>
              <p class="text-gray-500 max-w-sm mt-1">Vos futures interventions terminées apparaîtront ici.</p>
            </div>
            
            <div v-else class="space-y-4">
              <div
                v-for="item in history"
                :key="item.id"
                class="bg-white rounded-2xl p-5 border border-gray-100 hover:shadow-md hover:border-[#4682B4]/30 transition-all group"
              >
                <div class="flex items-start justify-between">
                  <div class="flex items-start gap-4">
                     <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-600 group-hover:bg-[#4682B4] group-hover:text-white transition-colors">
                        <Check :size="20" />
                     </div>
                     <div>
                        <h4 class="font-bold text-lg text-gray-800">{{ item.serviceName }}</h4>
                        <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                           <User :size="14" />
                           <span>Intervenant: <span class="font-medium text-gray-700">{{ item.providerName }}</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                           <Calendar :size="14" />
                           <span>{{ item.date }}</span>
                        </div>
                     </div>
                  </div>
                  
                  <div class="text-right">
                    <div class="inline-flex items-center px-3 py-1 rounded-lg bg-[#E8F5E9] text-[#6B8E6D] text-sm font-bold border border-[#6B8E6D]/20 mb-2">
                       {{ item.price }} DH
                    </div>
                  </div>
                </div>
                
                <div v-if="item.rating" class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-500">Votre évaluation</span>
                  <div class="flex gap-0.5">
                    <Star
                      v-for="i in 5"
                      :key="i"
                      :size="16"
                      :class="i <= Math.round(item.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200'"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mes Évaluations Tab -->
          <div v-if="activeTab === 'evaluations'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div v-if="loadingEvaluations" class="flex flex-col items-center justify-center py-12">
              <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#4682B4]"></div>
              <p class="mt-4 text-gray-500 font-medium">Chargement des avis...</p>
            </div>
            
            <div v-else>
              <div class="mb-8 flex items-end justify-between">
                <div>
                   <h3 class="text-xl font-bold text-gray-800 mb-1">Mes Évaluations</h3>
                   <p class="text-gray-500 text-sm">Ce que les intervenants pensent de vous.</p>
                </div>
                <div v-if="evaluations.length > 0" class="flex items-center gap-2 bg-yellow-50 px-4 py-2 rounded-xl border border-yellow-100">
                   <span class="text-2xl font-bold text-gray-800">{{ evalStatistics.averageRating || '0.0' }}</span>
                   <div class="flex flex-col items-start leading-none">
                      <div class="flex">
                         <Star :size="12" class="fill-yellow-400 text-yellow-400" />
                         <Star :size="12" class="fill-yellow-400 text-yellow-400" />
                         <Star :size="12" class="fill-yellow-400 text-yellow-400" />
                         <Star :size="12" class="fill-yellow-400 text-yellow-400" />
                         <Star :size="12" class="fill-yellow-400 text-yellow-400" />
                      </div>
                      <span class="text-[10px] text-gray-500 font-medium uppercase mt-0.5">{{ evaluations.length }} avis</span>
                   </div>
                </div>
              </div>

              <div v-if="filteredEvaluations.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-gray-100 rounded-2xl bg-gray-50/50">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
                   <Star :size="32" />
                </div>
                <p class="text-lg font-bold text-gray-700">Aucune évaluation</p>
                <p class="text-gray-500 max-w-sm mt-1">Les avis laissés par les intervenants apparaîtront ici.</p>
              </div>

              <div v-else class="grid grid-cols-1 gap-6">
                <div
                  v-for="evaluation in filteredEvaluations"
                  :key="evaluation.interventionId"
                  class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 group"
                >
                  <div class="flex items-start gap-5">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                      <div class="relative">
                        <img
                          :src="evaluation.intervenantImage || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(evaluation.intervenantName) + '&background=4682B4&color=fff'"
                          :alt="evaluation.intervenantName"
                          class="w-14 h-14 rounded-full object-cover border-2 border-gray-100 shadow-sm group-hover:border-[#4682B4] transition-colors"
                          @error="evaluation.intervenantImage = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(evaluation.intervenantName) + '&background=4682B4&color=fff'"
                        />
                      </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                          <h4 class="text-lg font-bold text-gray-900">{{ evaluation.intervenantName }}</h4>
                          <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1">
                             <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-[#4682B4]">
                                {{ evaluation.serviceName }}
                             </span>
                             <span class="flex items-center gap-1.5 text-xs text-gray-500">
                               <Calendar :size="12" />
                               {{ evaluation.date }}
                             </span>
                          </div>
                        </div>
                        
                        <div class="flex-shrink-0 flex items-center gap-1 bg-yellow-50 px-2.5 py-1 rounded-lg border border-yellow-100">
                          <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                          <span class="font-bold text-gray-900">{{ evaluation.overallRating }}</span>
                        </div>
                      </div>

                      <div v-if="evaluation.comment" class="relative mt-4 pl-4 border-l-2 border-gray-200">
                        <p class="text-gray-600 italic text-sm leading-relaxed">"{{ evaluation.comment }}"</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mes Favoris Tab -->
          <div v-if="activeTab === 'favorites'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <MyFavoritesTab 
              v-if="effectiveClientId" 
              :client-id="effectiveClientId" 
              @navigate-booking="$emit('navigate-booking', $event)"
            />
          </div>

          <!-- Mes Réclamations Tab -->
          <div v-if="activeTab === 'reclamations'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <ClientReclamationsTab />
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
  AlertTriangle,
  Check
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
    AlertTriangle,
    Check,
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
  emits: ['profile-updated', 'navigate-booking'],
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
      internalClientId: null,
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
    if (this.effectiveClientId) {
      this.loadHistory();
      this.loadEvaluations();
    }
  },
  computed: {
    effectiveClientId() {
      return this.clientId || this.internalClientId;
    },
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
        
        // Définir internalClientId si disponible
        if (userData.client?.id) {
          this.internalClientId = userData.client.id;
        } else if (this.clientId) {
          // Utiliser le prop si disponible
        } else {
          this.internalClientId = userData.id;
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
      if (!this.effectiveClientId) return;
      
      this.loadingHistory = true;
      try {
        const response = await profileService.getHistory(this.effectiveClientId);
        this.history = response.data.data || [];
      } catch (error) {
        console.error('Error loading history:', error);
      } finally {
        this.loadingHistory = false;
      }
    },
    async loadEvaluations() {
      if (!this.effectiveClientId) return;
      
      this.loadingEvaluations = true;
      try {
        const response = await profileService.getEvaluations(this.effectiveClientId);
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
  }
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
