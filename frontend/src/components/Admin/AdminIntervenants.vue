<template>
  <div>
    <!-- Back Button -->
    <button
      @click="$emit('back')"
      class="flex items-center gap-2 mb-6 text-sm transition-colors hover:underline"
      style="color: #5B7C99"
    >
    ← Retour au tableau de bord
    </button>

    <div class="mb-8">
      <h2 class="text-3xl font-medium mb-6" style="color: #2F4F4F">Gestion des Intervenants</h2>
      
      <!-- Search Bar -->
      <div class="mb-6">
        <div class="relative">
          <Search :size="18" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Rechercher par nom, prénom ou email..."
            v-model="searchTerm"
            class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-gray-300 text-sm"
            style="background-color: #FAFAFA"
          />
        </div>
      </div>

      <!-- Filters -->
      <div class="flex gap-3 flex-wrap">
        <!-- Service Filters -->
        <button
          v-for="filter in serviceFilters"
          :key="filter.value"
          @click="filterService = filter.value"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
          :class="filterService === filter.value ? 'shadow-sm' : ''"
          :style="{ 
            backgroundColor: filterService === filter.value ? filter.activeColor : '#FFFFFF',
            color: filterService === filter.value ? '#FFFFFF' : '#2F4F4F',
            border: `1px solid ${filterService === filter.value ? filter.activeColor : '#E5E7EB'}`
          }"
        >
          {{ filter.label }} ({{ filter.count }})
        </button>

        <!-- Status Filters -->
        <button
          v-for="filter in statusFilters"
          :key="filter.value"
          @click="filterStatus = filter.value"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
          :class="filterStatus === filter.value ? 'shadow-sm' : ''"
          :style="{ 
            backgroundColor: filterStatus === filter.value ? filter.activeColor : '#FFFFFF',
            color: filterStatus === filter.value ? '#FFFFFF' : '#2F4F4F',
            border: `1px solid ${filterStatus === filter.value ? filter.activeColor : '#E5E7EB'}`
          }"
        >
          {{ filter.label }} ({{ filter.count }})
        </button>
      </div>
    </div>

    <!-- Intervenants Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div 
        v-for="intervenant in filteredIntervenants" 
        :key="intervenant.id"
        class="bg-white rounded-2xl p-6 border transition-all hover:shadow-lg"
        :style="{ 
          borderColor: getServiceColor(intervenant),
          borderWidth: '2px'
        }"
      >
        <!-- Header -->
        <div class="flex items-start justify-between mb-5">
          <div class="flex items-center gap-4">
            <!-- Profile Photo Placeholder -->
            <div class="relative">
              <div 
                class="w-16 h-16 rounded-full flex items-center justify-center text-2xl text-gray-400 border-4"
                :style="{ 
                  backgroundColor: '#E5E7EB',
                  borderColor: getServiceColor(intervenant)
                }"
              >
                <!-- Placeholder for profile image - Replace this div with <img> when adding real photos -->
                <User :size="32" />
              </div>
              <!-- Status Badge -->
              <div 
                class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-4 border-white flex items-center justify-center"
                :style="{ backgroundColor: intervenant.statut === 'actif' ? '#4CAF50' : '#94A3B8' }"
              >
                <Check v-if="intervenant.statut === 'actif'" :size="12" color="white" />
              </div>
            </div>

            <!-- Name and Service -->
            <div>
              <h3 class="text-lg font-medium mb-1" style="color: #2F4F4F">
                {{ intervenant.prenom }} {{ intervenant.nom }}
              </h3>
              <div class="flex items-center gap-2 flex-wrap">
                <!-- Display all services if available, otherwise just the main service -->
                <template v-if="intervenant.allServices && intervenant.allServices.length > 0">
                  <span 
                    v-for="service in intervenant.allServices"
                    :key="service"
                    class="px-2.5 py-0.5 rounded text-xs font-medium text-white"
                    :style="{ backgroundColor: service === 'Jardinage' ? '#92B08B' : '#5B7C99' }"
                  >
                    {{ service }}
                  </span>
                </template>
                <span 
                  v-else
                  class="px-2.5 py-0.5 rounded text-xs font-medium text-white"
                  :style="{ backgroundColor: intervenant.service === 'Jardinage' ? '#92B08B' : '#5B7C99' }"
                >
                  {{ intervenant.service }}
                </span>
                <div class="flex items-center gap-1 text-sm">
                  <Star :size="14" fill="#FFC107" stroke="#FFC107" />
                  <span class="font-medium">{{ intervenant.note }}</span>
                  <span class="text-gray-400">({{ intervenant.nbAvis }})</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Status Badge Top Right -->
          <span 
            class="px-3 py-1 rounded-full text-xs font-medium text-white"
            :style="{ backgroundColor: intervenant.statut === 'actif' ? '#92B08B' : '#5B7C99' }"
          >
            {{ intervenant.statut }}
          </span>
        </div>

        <!-- Contact Info -->
        <div class="space-y-2 mb-5 text-sm text-gray-600">
          <div class="flex items-center gap-2">
            <Mail :size="14" />
            {{ intervenant.email }}
          </div>
          <div class="flex items-center gap-2">
            <Phone :size="14" />
            {{ intervenant.telephone }}
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-3 gap-4 mb-5">
          <div class="text-center p-4 rounded-xl" style="background-color: #F9FAFB">
            <p class="text-sm text-gray-500 mb-1">Missions</p>
            <p class="text-2xl font-semibold" :style="{ color: getServiceColor(intervenant) }">
              {{ intervenant.missions }}
            </p>
          </div>
          <div class="text-center p-4 rounded-xl" style="background-color: #F9FAFB">
            <p class="text-sm text-gray-500 mb-1">Tâches</p>
            <p class="text-2xl font-semibold" style="color: #2F4F4F">
              {{ intervenant.taches.length }}
            </p>
          </div>
          <div class="text-center p-4 rounded-xl" style="background-color: #F9FAFB">
            <p class="text-sm text-gray-500 mb-1">Note</p>
            <p class="text-2xl font-semibold" style="color: #FFC107">
              {{ intervenant.note }}
            </p>
          </div>
        </div>

        <!-- Action Button -->
        <button
          class="w-full py-3 rounded-lg text-sm font-medium transition-all text-white"
          :style="{ backgroundColor: getServiceColor(intervenant) }"
          @click="$emit('view-intervenant', intervenant)"
        >
          Voir le profil complet
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredIntervenants.length === 0" class="text-center py-16">
      <UserCheck :size="64" class="mx-auto mb-4 text-gray-300" />
      <p class="text-gray-500 text-lg">Aucun intervenant trouvé</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ArrowLeft, Search, User, Check, Star, Mail, Phone, UserCheck } from 'lucide-vue-next'

const props = defineProps({
  intervenants: {
    type: Array,
    required: true,
    default: () => []
  },
  loading: Boolean
})

const emit = defineEmits(['back', 'view-intervenant', 'suspend-intervenant'])

const searchTerm = ref('')
const filterService = ref('tous')
const filterStatus = ref('tous')

// Service Filters with counts
const serviceFilters = computed(() => {
  const jardinageCount = props.intervenants.filter(i => {
    if (i.allServices && Array.isArray(i.allServices)) {
      return i.allServices.includes('Jardinage')
    }
    return i.service === 'Jardinage'
  }).length
  
  const menageCount = props.intervenants.filter(i => {
    if (i.allServices && Array.isArray(i.allServices)) {
      return i.allServices.includes('Ménage')
    }
    return i.service === 'Ménage'
  }).length
  
  return [
    { 
      value: 'tous', 
      label: 'Tous services', 
      count: props.intervenants.length, 
      activeColor: '#FFFACD' 
    },
    { 
      value: 'Jardinage', 
      label: 'Jardinage', 
      count: jardinageCount, 
      activeColor: '#92B08B' 
    },
    { 
      value: 'Ménage', 
      label: 'Ménage', 
      count: menageCount, 
      activeColor: '#5B7C99' 
    }
  ]
})

// Status Filters with counts
const statusFilters = computed(() => {
  const actifsCount = props.intervenants.filter(i => i.statut === 'actif').length
  const suspendusCount = props.intervenants.filter(i => i.statut === 'suspendu').length
  
  return [
    { 
      value: 'tous', 
      label: 'Tous statuts', 
      count: props.intervenants.length,
      activeColor: '#FFFACD' 
    },
    { 
      value: 'actif', 
      label: 'Actifs', 
      count: actifsCount, 
      activeColor: '#92B08B' 
    },
    { 
      value: 'suspendu', 
      label: 'Suspendus', 
      count: suspendusCount, 
      activeColor: '#5B7C99' 
    }
  ]
})

// Filtered Intervenants
const filteredIntervenants = computed(() => {
  return props.intervenants.filter(intervenant => {
    // Search filter
    const matchesSearch = (intervenant.nom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (intervenant.prenom || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                         (intervenant.email || '').toLowerCase().includes(searchTerm.value.toLowerCase())
    
    // Service filter (handles multiple services)
    let matchesService = filterService.value === 'tous'
    if (!matchesService && intervenant.allServices && Array.isArray(intervenant.allServices)) {
      matchesService = intervenant.allServices.includes(filterService.value)
    } else if (!matchesService) {
      matchesService = intervenant.service === filterService.value
    }
    
    // Status filter
    const matchesStatus = filterStatus.value === 'tous' || intervenant.statut === filterStatus.value
    
    return matchesSearch && matchesService && matchesStatus
  })
})

// Get service color (use first service from allServices if available, otherwise main service)
const getServiceColor = (intervenant) => {
  let serviceToCheck = intervenant.service
  
  // If intervenant has multiple services, use the first one for the main color
  if (intervenant.allServices && intervenant.allServices.length > 0) {
    serviceToCheck = intervenant.allServices[0]
  }
  
  return serviceToCheck === 'Jardinage' ? '#92B08B' : '#5B7C99'
}
</script>

<style scoped>
/* Smooth transitions */
button {
  transition: all 0.2s ease;
}

button:hover {
  transform: translateY(-1px);
}

/* Custom scrollbar if needed */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
