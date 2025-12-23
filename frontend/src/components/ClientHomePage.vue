<template>
  <div class="min-h-screen bg-white animate-fade-in flex flex-col overflow-x-hidden">

    <!-- 1. Ultra-Premium Hero / Navigation -->
    <div class="relative bg-white border-b border-gray-100 overflow-hidden">
      <!-- Decorative background elements -->
      <div class="absolute top-0 right-0 w-96 h-96 bg-[#4682B4]/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#E8F5E9] rounded-full -ml-32 -mb-32 blur-3xl"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 relative z-10">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl md:text-3xl font-bold text-[#305C7D] tracking-tight">
              Bienvenue, <span class="text-[#4682B4]">{{ currentUser?.prenom || 'Client' }}</span> 
            </h1>
        </div>
      </div>
    </div>

    <!-- 2. Services Grid (Standard Grid) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mb-8">
       <div class="mb-6 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-[#305C7D]">Nos Services</h2>
            <div class="w-12 h-1 bg-[#F3E293] rounded-full mt-1"></div>
          </div>
       </div>

       <div class="grid md:grid-cols-2 gap-6">
          <!-- Jardinage Card -->
          <div 
            @click="$emit('service-click', 1)"
            class="group relative bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden border border-gray-100 flex flex-col md:flex-row items-center gap-8 hover:-translate-y-1"
          >
             <!-- Background -->
             <div class="absolute top-0 right-0 w-48 h-48 bg-[#E8F5E9] rounded-full opacity-50 -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
             
             <div class="w-full md:w-1/3 relative z-10 flex justify-center h-48">
                <img 
                  :src="jardinageImg" 
                  alt="Jardinage" 
                  class="h-full w-auto object-contain transform transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-3"
                />
             </div>
             
             <div class="w-full md:w-2/3 relative z-10 text-center md:text-left">
                <h3 class="text-2xl font-bold text-[#305C7D] mb-3 group-hover:text-[#4682B4] transition-colors">Jardinage</h3>
                <p class="text-slate-500 text-base leading-relaxed mb-6">Entretien de jardins, taille et aménagement paysager par nos experts.</p>
                
                <div class="flex items-center gap-3 justify-center md:justify-start">
                  <span class="text-base font-bold text-[#305C7D] group-hover:underline">Réserver</span>
                  <div class="w-10 h-10 rounded-full bg-[#E8F5E9] text-[#305C7D] flex items-center justify-center group-hover:translate-x-1 transition-transform">
                    <ArrowRight :size="20" />
                  </div>
                </div>
             </div>
          </div>

          <!-- Ménage Card -->
          <div 
            @click="$emit('service-click', 2)"
            class="group relative bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden border border-gray-100 flex flex-col md:flex-row items-center gap-8 hover:-translate-y-1"
          >
             <!-- Background -->
             <div class="absolute top-0 right-0 w-48 h-48 bg-[#E1EFFE] rounded-full opacity-50 -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
             
             <div class="w-full md:w-1/3 relative z-10 flex justify-center h-48">
                <img 
                  :src="menageImg" 
                  alt="Ménage" 
                  class="h-full w-auto object-contain transform transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                />
             </div>
             
             <div class="w-full md:w-2/3 relative z-10 text-center md:text-left">
                <h3 class="text-2xl font-bold text-[#305C7D] mb-3 group-hover:text-[#4682B4] transition-colors">Ménage</h3>
                <p class="text-slate-500 text-base leading-relaxed mb-6">Nettoyage résidentiel et entretien régulier de votre domicile.</p>
                
                <div class="flex items-center gap-3 justify-center md:justify-start">
                  <span class="text-base font-bold text-[#305C7D] group-hover:underline">Réserver</span>
                  <div class="w-10 h-10 rounded-full bg-[#E1EFFE] text-[#305C7D] flex items-center justify-center group-hover:translate-x-1 transition-transform">
                    <ArrowRight :size="20" />
                  </div>
                </div>
             </div>
          </div>
       </div>
    </div>

    <!-- 3. Combined Info Grid (How it Works + Agenda) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
       <div class="grid lg:grid-cols-2 gap-8">
          
          <!-- How It Works (Left Column) -->
          <div class="bg-[#305C7D] rounded-3xl p-8 md:p-10 text-white relative overflow-hidden shadow-xl flex flex-col">
             <!-- Decorative elements -->
             <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-20 -mt-20"></div>
             <div class="absolute bottom-0 left-0 w-40 h-40 bg-[#F3E293]/10 rounded-full blur-2xl -ml-10 -mb-10"></div>
             
             <div class="relative z-10 mb-8">
                <h2 class="text-2xl font-bold mb-2">Comment ça marche ?</h2>
                <div class="w-12 h-1 bg-[#F3E293] rounded-full"></div>
             </div>
             
             <div class="flex-1 space-y-8 relative z-10">
                <!-- Vertical connecting line -->
                <div class="absolute left-[27px] top-4 bottom-4 w-0.5 border-l border-dashed border-white/20"></div>

                <!-- Step 1 -->
                <div class="relative flex items-start gap-6 group">
                   <div class="w-14 h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 shadow-inner group-hover:scale-110 transition-transform shrink-0 z-10">
                      <Search :size="24" class="text-[#F3E293]" />
                   </div>
                   <div class="pt-1">
                      <h3 class="text-lg font-bold mb-1 group-hover:text-[#F3E293] transition-colors">1. Choisissez</h3>
                      <p class="text-slate-200 text-sm leading-relaxed">Trouvez le service idéal parmi notre large gamme d'experts.</p>
                   </div>
                </div>

                <!-- Step 2 -->
                <div class="relative flex items-start gap-6 group">
                   <div class="w-14 h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 shadow-inner group-hover:scale-110 transition-transform shrink-0 z-10">
                      <Users :size="24" class="text-[#F3E293]" />
                   </div>
                   <div class="pt-1">
                      <h3 class="text-lg font-bold mb-1 group-hover:text-[#F3E293] transition-colors">2. Réservez</h3>
                      <p class="text-slate-200 text-sm leading-relaxed">Sélectionnez un créneau et un artisan qualifié en quelques clics.</p>
                   </div>
                </div>

                <!-- Step 3 -->
                <div class="relative flex items-start gap-6 group">
                   <div class="w-14 h-14 bg-[#F3E293] text-[#305C7D] rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform shrink-0 z-10">
                      <Smile :size="24" />
                   </div>
                   <div class="pt-1">
                      <h3 class="text-lg font-bold mb-1 text-white">3. Sérénité</h3>
                      <p class="text-slate-200 text-sm leading-relaxed">Profitez de votre temps libre, nous nous occupons de tout.</p>
                   </div>
                </div>
             </div>
          </div>

          <!-- Agenda (Right Column) -->
          <div class="bg-white rounded-3xl p-8 md:p-10 shadow-xl border border-gray-100 flex flex-col h-full">
             <div class="flex items-center justify-between mb-8">
               <div>
                  <h2 class="text-2xl font-bold text-[#305C7D] flex items-center gap-3">
                    Vos Rendez-vous
                  </h2>
               </div>
               <button 
                 @click="$emit('navigate-reservations')"
                 class="group flex items-center gap-2 text-sm font-bold text-[#4682B4] hover:text-[#305C7D] bg-[#E1EFFE] px-4 py-2.5 rounded-xl hover:bg-[#D1E9FF] transition-all"
               >
                 Tout voir
                 <ArrowRight :size="16" class="group-hover:translate-x-1 transition-transform" />
               </button>
             </div>
             
             <!-- Loading State -->
              <div v-if="loading" class="flex-1 flex flex-col items-center justify-center min-h-[200px]">
                 <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#305C7D]"></div>
              </div>

             <!-- Empty State -->
             <div v-else-if="upcomingInterventions.length === 0" class="flex-1 flex flex-col items-center justify-center text-center min-h-[200px]">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center mb-4">
                   <Calendar :size="32" />
                </div>
                <h3 class="text-lg font-bold text-[#305C7D] mb-1">Agenda libre</h3>
                <p class="text-slate-400 text-sm">Aucune intervention prévue pour le moment.</p>
             </div>

             <!-- Timeline -->
             <div v-else class="space-y-4">
                <div 
                  v-for="intervention in upcomingInterventions" 
                  :key="intervention.id"
                  class="flex items-center gap-5 p-4 rounded-2xl border transition-all duration-300 hover:shadow-md cursor-pointer group bg-white border-gray-100 hover:border-[#E1EFFE]"
                >
                   <!-- Date Badge -->
                   <div class="flex flex-col items-center justify-center w-16 h-16 rounded-2xl bg-[#F8FAFC] text-[#305C7D] border border-gray-100 shrink-0 group-hover:bg-[#305C7D] group-hover:text-white transition-colors duration-300 shadow-sm">
                      <span class="text-[10px] uppercase font-bold opacity-60">{{ formatDate(intervention.date).split(' ')[0] }}</span>
                      <span class="text-xl font-black leading-none my-0.5">{{ formatDate(intervention.date).split(' ')[1] }}</span>
                      <span class="text-[10px] uppercase font-bold opacity-60">{{ formatDate(intervention.date).split(' ')[2] }}</span>
                   </div>
                   
                   <div class="flex-1 min-w-0">
                      <div class="flex justify-between items-start mb-2">
                         <div>
                           <h4 class="font-bold text-[#305C7D] text-base truncate">{{ intervention.service }}</h4>
                           <p class="text-xs text-slate-400 font-medium">{{ intervention.task }}</p>
                         </div>
                         <span class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide border" :class="getStatusColor(intervention.status)">
                           {{ getStatusLabel(intervention.status) }}
                         </span>
                      </div>
                      
                      <div class="flex items-center gap-4 text-xs text-slate-500 font-medium">
                         <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-md">
                           <Clock :size="14" class="text-[#4682B4]" />
                           <span>{{ intervention.time || '--:--' }}</span>
                         </div>
                         <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-md">
                           <MapPin :size="14" class="text-[#4682B4]" />
                           <span class="truncate max-w-[120px]">{{ intervention.quartier || 'Domicile' }}</span>
                         </div>
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
  Clock, Calendar, ArrowRight, MapPin, Sun, Search, Users, Smile, 
  ChevronLeft, ChevronRight 
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
const servicesContainer = ref(null)

const scrollServices = (direction) => {
  if (!servicesContainer.value) return;
  const scrollAmount = servicesContainer.value.offsetWidth * 0.8;
  servicesContainer.value.scrollBy({
    left: direction === 'left' ? -scrollAmount : scrollAmount,
    behavior: 'smooth'
  });
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'Date inconnue';
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat('fr-FR', { 
    weekday: 'short', 
    day: 'numeric', 
    month: 'short' 
  }).format(date);
}

const getStatusLabel = (status) => {
  const map = {
    'pending': 'En attente',
    'accepted': 'Confirmée',
    'in-progress': 'En cours',
    'completed': 'Exécutée',
    'cancelled': 'Annulée'
  };
  return map[status] || status;
}

const getStatusColor = (status) => {
  const map = {
    'pending': 'bg-[#F3E293]/20 text-[#305C7D] border-[#F3E293]/40',
    'accepted': 'bg-[#E1EFFE] text-[#305C7D] border-[#4682B4]/20',
    'in-progress': 'bg-blue-600 text-white border-blue-700 shadow-blue-600/20',
    'completed': 'bg-[#E8F5E9] text-[#305C7D] border-[#D4EDDA]'
  };
  return map[status] || 'bg-gray-100 text-gray-700';
}

onMounted(async () => {
  if (props.currentUser?.id) {
    try {
      loading.value = true;
      const response = await interventionService.getByClientId(props.currentUser.id);
      
      let allInterventions = [];
      if (response.data?.data && Array.isArray(response.data.data)) {
        allInterventions = response.data.data;
      } else if (Array.isArray(response.data)) {
        allInterventions = response.data;
      }
      
      const active = allInterventions.filter(i => 
        ['pending', 'accepted', 'in-progress'].includes(i.status)
      );
      
      active.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        if (dateA.getTime() !== dateB.getTime()) return dateA - dateB;
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
.animate-fade-in { animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

[style*="scroll-snap-type"] { scroll-padding-left: 2rem; }
@media (min-width: 1024px) { [style*="scroll-snap-type"] { scroll-padding-left: 3rem; } }
</style>
