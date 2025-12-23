<template>
  <div class="min-h-screen bg-gray-50/50 animate-fade-in pb-12">


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
      <!-- Profile Summary Card -->
      <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/40 p-6 mb-8 relative overflow-hidden text-center">
        <!-- Floating shapes background - Reduced size -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-[#4682B4]/5 rounded-full blur-3xl -mr-24 -mt-24 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#92B08B]/5 rounded-full blur-3xl -ml-24 -mb-24 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center max-w-3xl mx-auto">
          <!-- Avatar Section - Reduced Size -->
          <div class="relative mb-4 group">
             <div class="w-28 h-28 rounded-full p-1 bg-gradient-to-tr from-[#4682B4] to-[#92B08B] shadow-lg">
               <div class="w-full h-full rounded-full border-4 border-white bg-white overflow-hidden relative">
                  <img
                    v-if="currentUser"
                    :src="previewImage || currentUser.avatar || currentUser.profilePhoto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentUser.name) + '&background=4682B4&color=fff'"
                    :alt="currentUser.name"
                    class="w-full h-full object-cover"
                  />
                  <!-- Upload Overlay -->
                  <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" @click="$refs.fileInput.click()">
                    <Camera :size="20" class="text-white" />
                  </div>
               </div>
             </div>
             
             <!-- Edit Button (Pencil) -->
             <button 
               @click="$refs.fileInput.click()"
               class="absolute bottom-1 right-1 bg-white text-[#4682B4] rounded-full p-2 shadow-md border border-gray-100 hover:bg-[#4682B4] hover:text-white hover:scale-110 transition-all duration-300 group-hover:shadow-blue-200"
               title="Modifier la photo"
             >
               <Pencil :size="14" stroke-width="2.5" />
             </button>

             <input
               ref="fileInput"
               type="file"
               accept="image/jpeg,image/png,image/jpg,image/gif"
               class="hidden"
               @change="handleAvatarChange"
             />
             
             <div v-if="uploadingAvatar" class="absolute inset-0 rounded-full flex items-center justify-center z-20">
               <div class="absolute inset-0 bg-white/80 rounded-full backdrop-blur-[2px]"></div>
               <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-[#4682B4] relative z-10"></div>
             </div>
          </div>

          <!-- Name & Verification -->
          <h2 v-if="currentUser" class="text-2xl font-black text-[#305C7D] mb-6 tracking-tight">{{ currentUser.name }}</h2>
          <!-- "Client Vérifié" badge removed as requested -->
            
          <!-- Info Grid -->
          <div v-if="currentUser" class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
            <!-- Email Card -->
            <div class="group flex items-center gap-4 p-4 bg-gray-50/80 rounded-xl border border-gray-100 hover:bg-white hover:shadow-lg hover:shadow-blue-900/5 hover:-translate-y-0.5 transition-all duration-300">
               <div class="w-10 h-10 rounded-xl bg-[#E1EFFE] flex items-center justify-center text-[#4682B4] group-hover:scale-105 transition-transform duration-300 shadow-inner">
                 <Mail :size="18" stroke-width="2" />
               </div>
               <div class="text-left overflow-hidden">
                 <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Email</p>
                 <p class="font-bold text-gray-800 text-sm truncate" :title="currentUser.email">{{ currentUser.email }}</p>
               </div>
            </div>

            <!-- Phone Card -->
            <div class="group flex items-center gap-4 p-4 bg-gray-50/80 rounded-xl border border-gray-100 hover:bg-white hover:shadow-lg hover:shadow-green-900/5 hover:-translate-y-0.5 transition-all duration-300">
               <div class="w-10 h-10 rounded-xl bg-[#E8F5E9] flex items-center justify-center text-[#92B08B] group-hover:scale-105 transition-transform duration-300 shadow-inner">
                 <Phone :size="18" stroke-width="2" />
               </div>
               <div class="text-left">
                 <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Téléphone</p>
                 <p class="font-bold text-gray-800 text-sm">{{ currentUser.phone || 'Non renseigné' }}</p>
               </div>
            </div>

            <!-- Location Card -->
            <div class="group flex items-center gap-4 p-4 bg-gray-50/80 rounded-xl border border-gray-100 hover:bg-white hover:shadow-lg hover:shadow-purple-900/5 hover:-translate-y-0.5 transition-all duration-300">
               <div class="w-10 h-10 rounded-xl bg-[#F3E8FF] flex items-center justify-center text-purple-500 group-hover:scale-105 transition-transform duration-300 shadow-inner">
                  <MapPin :size="18" stroke-width="2" />
               </div>
               <div class="text-left">
                 <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Localisation</p>
                 <p class="font-bold text-gray-800 text-sm">{{ currentUser.location || 'Non renseigné' }}</p>
               </div>
            </div>

            <!-- Member Since Card -->
            <div class="group flex items-center gap-4 p-4 bg-gray-50/80 rounded-xl border border-gray-100 hover:bg-white hover:shadow-lg hover:shadow-yellow-900/5 hover:-translate-y-0.5 transition-all duration-300">
               <div class="w-10 h-10 rounded-xl bg-[#FEF9C3] flex items-center justify-center text-yellow-600 group-hover:scale-105 transition-transform duration-300 shadow-inner">
                  <Calendar :size="18" stroke-width="2" />
               </div>
               <div class="text-left">
                 <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Membre depuis</p>
                 <p class="font-bold text-gray-800 text-sm">{{ currentUser.memberSince }}</p>
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


          <!-- Mes Évaluations Tab -->
          <div v-if="activeTab === 'evaluations'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
              <div>
                 <h3 class="text-xl font-bold text-gray-800 mb-1">Mes Évaluations</h3>
                 <p class="text-gray-500 text-sm">Gérez et consultez les avis de la communauté.</p>
              </div>

              <!-- Sub-tab Switcher -->
              <div class="flex p-1 bg-gray-100 rounded-xl">
                <button 
                  @click="evaluationSubTab = 'received'"
                  class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                  :class="evaluationSubTab === 'received' ? 'bg-white text-[#4682B4] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                >
                  Avis reçus
                </button>
                <button 
                  @click="evaluationSubTab = 'given'"
                  class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                  :class="evaluationSubTab === 'given' ? 'bg-white text-[#4682B4] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                >
                  Avis donnés
                </button>
              </div>
            </div>

            <div v-if="loadingEvaluations || loadingHistory" class="flex flex-col items-center justify-center py-12">
              <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#4682B4]"></div>
              <p class="mt-4 text-gray-500 font-medium">Chargement des avis...</p>
            </div>
            
            <div v-else>
              <!-- Avis Reçus Content -->
              <div v-if="evaluationSubTab === 'received'">
                <div v-if="evaluations.length > 0" class="mb-6 flex items-center gap-2 bg-blue-50/50 px-4 py-2.5 rounded-xl border border-blue-100">
                   <div class="flex items-center gap-1.5">
                      <Star :size="18" class="fill-yellow-400 text-yellow-400" />
                      <span class="text-xl font-black text-gray-800">{{ evalStatistics.averageRating || '0.0' }}</span>
                   </div>
                   <div class="h-4 w-px bg-gray-300 mx-2"></div>
                   <span class="text-xs text-gray-600 font-bold uppercase tracking-wider">{{ evaluations.length }} avis reçus</span>
                </div>

                <div v-if="filteredEvaluations.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/30">
                  <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 mb-4 transform rotate-12">
                     <Star :size="32" />
                  </div>
                  <p class="text-lg font-bold text-gray-700">Aucun avis reçu</p>
                  <p class="text-gray-500 max-w-sm mt-1">Les avis laissés par les intervenants apparaîtront ici.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-6">
                  <div
                    v-for="evaluation in filteredEvaluations"
                    :key="evaluation.interventionId"
                    class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group"
                  >
                    <div class="flex items-start gap-4">
                      <img
                        :src="evaluation.intervenantImage || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(evaluation.intervenantName) + '&background=F0F4F8&color=4682B4'"
                        class="w-12 h-12 rounded-xl object-cover shadow-sm"
                      />
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                          <div>
                            <h4 class="font-bold text-gray-900 group-hover:text-[#4682B4] transition-colors">{{ evaluation.intervenantName }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">{{ evaluation.serviceName }} • {{ evaluation.date }}</p>
                          </div>
                          <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                            <Star :size="14" class="fill-yellow-400 text-yellow-400" />
                            <span class="font-black text-sm text-gray-800">{{ evaluation.overallRating }}</span>
                          </div>
                        </div>
                        <div v-if="evaluation.comment" class="mt-4 p-3 bg-gray-50 rounded-xl relative">
                          <MessageSquareText :size="14" class="absolute -top-2 -left-2 text-gray-300 fill-white" />
                          <p class="text-sm text-gray-600 italic leading-relaxed">"{{ evaluation.comment }}"</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Avis Donnés Content -->
              <div v-else>
                <div v-if="givenEvaluations.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/30">
                  <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 mb-4 transform -rotate-12">
                     <MessageSquareText :size="32" />
                  </div>
                  <p class="text-lg font-bold text-gray-700">Aucun avis donné</p>
                  <p class="text-gray-500 max-w-sm mt-1">Vous n'avez pas encore laissé d'avis sur vos prestations passées.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-6">
                  <div
                    v-for="item in givenEvaluations"
                    :key="item.id"
                    class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group"
                  >
                    <div class="flex items-start gap-4">
                      <img
                        :src="item.providerImage || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(item.providerName) + '&background=F0F4F8&color=92B08B'"
                        class="w-12 h-12 rounded-xl object-cover shadow-sm"
                      />
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                          <div>
                            <h4 class="font-bold text-gray-900 group-hover:text-[#92B08B] transition-colors">{{ item.providerName }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">{{ item.serviceName }} • {{ item.date }}</p>
                          </div>
                          <div class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-lg border border-green-100">
                            <Star :size="14" class="fill-[#92B08B] text-[#92B08B]" />
                            <span class="font-black text-sm text-gray-800">{{ item.rating }}</span>
                          </div>
                        </div>
                        <div v-if="item.comment" class="mt-4 p-3 bg-[#92B08B]/5 rounded-xl border border-[#92B08B]/10 relative">
                          <MessageSquareText :size="14" class="absolute -top-2 -left-2 text-[#92B08B]/40 fill-white" />
                          <p class="text-sm text-gray-700 italic leading-relaxed">"{{ item.comment }}"</p>
                        </div>
                        <div v-else class="mt-3">
                           <span class="text-xs font-medium text-gray-400 italic">Évaluation sans commentaire</span>
                        </div>
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
  MessageSquareText,
  AlertTriangle,
  Check,
  Camera,
  CheckCircle
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
    Camera,
    CheckCircle,
    MyFavoritesTab,
    ClientReclamationsTab,
    MessageSquareText
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
      evaluationSubTab: 'received',
      tabs: [
        { id: 'informations', label: 'Informations', icon: 'User' },
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
      if (newTab === 'evaluations') {
        if (this.evaluations.length === 0) this.loadEvaluations();
        if (this.history.length === 0) this.loadHistory();
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
    },
    givenEvaluations() {
      // Return history items that have a rating
      return this.history.filter(item => item.rating !== null);
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
