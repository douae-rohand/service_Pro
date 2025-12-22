<template>
  <div class="min-h-screen bg-gray-50/50 animate-fade-in flex flex-col">

    <!-- 1. Hero / Welcome Section -->
    <div class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 tracking-tight">
              Bonjour, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">{{ currentUser?.prenom || 'Client' }}</span> 
            </h1>
            <p class="text-slate-500 mt-2 text-lg">Prêt à simplifier votre quotidien ? Choisissez un service ci-dessous.</p>
          </div>
          
          <!-- 9. Widget Météo & Conseil Jardin -->
          
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-12">
       
       <!-- 2. Services Section (Main Action) -->
       <div class="grid md:grid-cols-2 gap-6 mb-12">
        <!-- Jardinage Card -->
        <div 
          @click="$emit('service-click', 1)"
          class="group relative bg-white rounded-[2rem] p-6 shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer overflow-hidden border border-gray-100 transform hover:-translate-y-1"
        >
          <div class="absolute top-0 right-0 w-64 h-64 bg-green-50 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-125 transition-transform duration-700"></div>
          
          <div class="relative z-10 flex flex-col h-full">
            <!-- Image Section -->
            <div class="flex justify-center items-center mb-4 h-48">
              <img 
                :src="jardinageImg" 
                alt="Jardinage" 
                class="h-full w-auto object-contain transform transition-transform duration-500 group-hover:scale-110 group-hover:rotate-2"
              />
            </div>
            
            <!-- Content Section -->
            <div class="flex flex-col justify-between flex-1">
              <div class="text-center mb-4">
                <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-green-700 transition-colors">Jardinage</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Entretien de jardins, taille, tonte et aménagement paysager.</p>
              </div>
              
              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-sm font-semibold text-green-600 group-hover:underline">Voir les services</span>
                <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors">
                  <ArrowRight :size="20" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ménage Card -->
        <div 
          @click="$emit('service-click', 2)"
          class="group relative bg-white rounded-[2rem] p-6 shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer overflow-hidden border border-gray-100 transform hover:-translate-y-1"
        >
          <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-125 transition-transform duration-700"></div>
          
          <div class="relative z-10 flex flex-col h-full">
            <!-- Image Section -->
            <div class="flex justify-center items-center mb-4 h-48">
              <img 
                :src="menageImg" 
                alt="Ménage" 
                class="h-full w-auto object-contain transform transition-transform duration-500 group-hover:scale-110 group-hover:rotate-2"
              />
            </div>
            
            <!-- Content Section -->
            <div class="flex flex-col justify-between flex-1">
              <div class="text-center mb-4">
                <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-blue-700 transition-colors">Ménage</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Services de nettoyage résidentiel et commercial, entretien régulier.</p>
              </div>
              
              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-sm font-semibold text-blue-600 group-hover:underline">Voir les services</span>
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                  <ArrowRight :size="20" />
                </div>
              </div>
            </div>
          </div>
        </div>
       </div>

       <div class="grid lg:grid-cols-3 gap-8">
          <!-- 1. Agenda / Timeline Section -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-[1.5rem] shadow-sm border border-gray-100 p-6 md:p-8 relative overflow-hidden h-full">
               <div class="flex items-center justify-between mb-6">
                 <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                   <Calendar :size="22" class="text-indigo-600" />
                   Vos prochains rendez-vous
                 </h2>
                 <button 
                   @click="$emit('navigate-reservations')"
                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors bg-indigo-50 px-3 py-1 rounded-full hover:bg-indigo-100 cursor-pointer"
                 >
                   Tout voir
                 </button>
               </div>
               
               <!-- Loading State -->
               <div v-if="loading" class="flex flex-col items-center justify-center py-12 h-[300px]">
                  <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-4"></div>
                  <p class="text-slate-400 text-sm">Chargement de votre agenda...</p>
               </div>

               <!-- Empty State -->
               <div v-else-if="upcomingInterventions.length === 0" class="flex flex-col items-center justify-center py-12 text-center h-[300px]">
                  <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4">
                     <Calendar :size="32" />
                  </div>
                  <h3 class="text-lg font-bold text-slate-700 mb-1">Aucune intervention prévue</h3>
                  <p class="text-slate-400 text-sm max-w-xs mx-auto mb-6">C'est calme par ici. Planifiez votre premier service dès maintenant !</p>
               </div>

               <!-- Timeline of Real Interventions -->
               <div v-else class="space-y-4">
                  <div 
                    v-for="intervention in upcomingInterventions" 
                    :key="intervention.id"
                    class="flex items-start gap-4 p-4 rounded-2xl border transition-all duration-300 hover:shadow-md cursor-pointer group bg-white border-gray-100 hover:border-indigo-100"
                    :class="{'bg-indigo-50/30': intervention.status === 'accepted'}"
                  >
                      <!-- Date Badge -->
                      <div class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl shadow-sm font-bold border shrink-0 bg-white"
                           :class="intervention.status === 'pending' ? 'text-yellow-600 border-yellow-100' : 'text-indigo-600 border-indigo-100'">
                         <span class="text-[10px] uppercase tracking-wide opacity-70">{{ formatDate(intervention.date).split(' ')[0] }}</span>
                         <span class="text-xl leading-none">{{ formatDate(intervention.date).split(' ')[1] }}</span>
                         <span class="text-[10px] lowercase text-slate-400 mt-0.5">{{ formatDate(intervention.date).split(' ')[2] }}</span>
                      </div>
                      
                      <!-- Content -->
                      <div class="flex-1 min-w-0">
                          <div class="flex justify-between items-start mb-1">
                             <div>
                               <h4 class="font-bold text-slate-800 text-base truncate pr-2 group-hover:text-indigo-700 transition-colors">
                                 {{ intervention.service }}
                               </h4>
                               <div class="text-xs text-slate-500 font-medium truncate">{{ intervention.task }}</div>
                             </div>
                             <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border shadow-sm uppercase tracking-wide whitespace-nowrap"
                               :class="getStatusColor(intervention.status)">
                               {{ getStatusLabel(intervention.status) }}
                             </span>
                          </div>
                          
                          <div class="flex items-center gap-3 mt-3 text-xs text-slate-500">
                             <div class="flex items-center gap-1.5 p-1 px-2 bg-slate-50 rounded-md border border-slate-100">
                               <Clock :size="14" class="text-slate-400" />
                               <span class="font-semibold text-slate-600">{{ intervention.time || 'À définir' }}</span>
                             </div>
                             
                             <div v-if="intervention.intervenant?.name && intervention.intervenant.name !== 'N/A'" class="flex items-center gap-1.5 p-1 px-2 bg-slate-50 rounded-md border border-slate-100">
                               <div class="w-4 h-4 rounded-full bg-slate-200 overflow-hidden">
                                  <img v-if="intervention.intervenant.image" :src="intervention.intervenant.image" class="w-full h-full object-cover" />
                                  <Users v-else :size="10" class="m-0.5 text-slate-500" />
                               </div>
                               <span class="font-medium text-slate-700 truncate max-w-[100px]">{{ intervention.intervenant.name }}</span>
                             </div>
                             
                             <div class="flex items-center gap-1.5 p-1 px-2 bg-slate-50 rounded-md border border-slate-100 ml-auto">
                               <MapPin :size="14" class="text-slate-400" />
                               <span class="truncate max-w-[80px]">{{ intervention.quartier || 'Domicile' }}</span>
                             </div>
                          </div>
                      </div>
                  </div>
               </div>
            </div>
          </div>

          <!-- 5. How It Works Section (Simpler Vertical Design) -->
          <div class="lg:col-span-1">
             <div class="bg-white rounded-[1.5rem] border border-gray-100 p-6 md:p-8 shadow-sm h-full relative overflow-hidden flex flex-col">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-400 via-indigo-500 to-purple-500"></div>
                
                <h2 class="text-xl font-bold text-slate-800 mb-6">Comment ça marche ?</h2>
                
                <div class="flex-1 space-y-8 relative">
                   <!-- Vertical Line -->
                   <div class="absolute top-4 bottom-4 left-[26px] w-0.5 border-l-2 border-dashed border-gray-100 -z-10"></div>

                   <!-- Step 1 -->
                   <div class="flex gap-4 relative">
                      <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-sm border border-blue-100 shrink-0 z-10 bg-white">
                         <Search :size="24" />
                      </div>
                      <div>
                         <h3 class="text-sm font-bold text-slate-800">1. Choisissez</h3>
                         <p class="text-slate-500 text-xs leading-relaxed mt-1">Sélectionnez votre service et décrivez votre besoin.</p>
                      </div>
                   </div>

                   <!-- Step 2 -->
                   <div class="flex gap-4 relative">
                      <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm border border-indigo-100 shrink-0 z-10 bg-white">
                         <Users :size="24" />
                      </div>
                      <div>
                         <h3 class="text-sm font-bold text-slate-800">2. Réservez</h3>
                         <p class="text-slate-500 text-xs leading-relaxed mt-1">Comparez les profils et choisissez votre expert.</p>
                      </div>
                   </div>

                   <!-- Step 3 -->
                   <div class="flex gap-4 relative">
                      <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center shadow-sm border border-green-100 shrink-0 z-10 bg-white">
                         <Smile :size="24" />
                      </div>
                      <div>
                         <h3 class="text-sm font-bold text-slate-800">3. Profitez</h3>
                         <p class="text-slate-500 text-xs leading-relaxed mt-1">Prestation réalisée et paiement sécurisé.</p>
                      </div>
                   </div>
                </div>
                
              
             </div>
          </div>
       </div>


    </div>
    
  <!-- Footer Section -->
  <Footer @navigate-home="$emit('navigate-home')" class="mt-auto" />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { 
  Clock, Calendar, ArrowRight, MapPin, 
  Sun, HelpCircle, FileText, Shield, Search, Users, Smile, Star, MessageCircle, AlertCircle
} from 'lucide-vue-next';
import Footer from './Footer.vue';
import interventionService from '@/services/interventionService';
import jardinageImg from '@/assets/jardinage.png';
import menageImg from '@/assets/menage.png';

const props = defineProps({
  currentUser: { type: Object, required: true },
  stats: { type: Object, default: () => ({ pending: 0, accepted: 0, inProgress: 0, completed: 0 }) }
})

const emit = defineEmits(['service-click', 'navigate-home', 'view-profile', 'navigate-reservations'])

const upcomingInterventions = ref([])
const loading = ref(true)

const formatDate = (dateStr) => {
  if (!dateStr) return 'Date inconnue';
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat('fr-FR', { 
    weekday: 'short', 
    day: 'numeric', 
    month: 'short' 
  }).format(date);
}

const formatTime = (timeStr) => {
  if (!timeStr) return '';
  return timeStr.substring(0, 5);
}

const getStatusLabel = (status) => {
  const map = {
    'pending': 'En attente',
    'accepted': 'Acceptée',
    'in-progress': 'En cours',
    'completed': 'Terminée',
    'cancelled': 'Annulée'
  };
  return map[status] || status;
}

const getStatusColor = (status) => {
  const map = {
    'pending': 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'accepted': 'bg-indigo-100 text-indigo-700 border-indigo-200',
    'in-progress': 'bg-purple-100 text-purple-700 border-purple-200',
    'completed': 'bg-green-100 text-green-700 border-green-200'
  };
  return map[status] || 'bg-gray-100 text-gray-700';
}

onMounted(async () => {
  console.log('ClientHomePage mounted')
  if (props.currentUser?.id) {
    try {
      loading.value = true;
      const response = await interventionService.getByClientId(props.currentUser.id);
      
      // The backend already returns transformed data via InterventionResource
      // No need to transform again
      let allInterventions = [];
      if (response.data?.data && Array.isArray(response.data.data)) {
        allInterventions = response.data.data;
      } else if (Array.isArray(response.data)) {
        allInterventions = response.data;
      }
      
      // Filter for active/upcoming (pending, accepted, in-progress)
      const active = allInterventions.filter(i => 
        ['pending', 'accepted', 'in-progress'].includes(i.status)
      );
      
      // Sort by date and time
      active.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        
        // If dates are different, sort by date
        if (dateA.getTime() !== dateB.getTime()) {
           return dateA - dateB;
        }
        
        // If dates are same, sort by time string
        const timeA = a.time || '00:00';
        const timeB = b.time || '00:00';
        return timeA.localeCompare(timeB);
      });
      
      upcomingInterventions.value = active.slice(0, 3);
    } catch (e) {
      console.error("Error fetching interventions:", e);
    } finally {
      loading.value = false;
    }
  }
})

</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
