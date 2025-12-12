<template>
  <div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-8">
      <div 
        v-for="stat in statsCards" 
        :key="stat.key"
        class="bg-white rounded-2xl p-6 border border-gray-100 transition-all hover:scale-105 cursor-pointer"
        :style="{ boxShadow: `0 4px 15px ${stat.shadowColor}` }"
        @mouseenter="(e) => e.currentTarget.style.boxShadow = `0 8px 30px ${stat.shadowColorHover}`"
        @mouseleave="(e) => e.currentTarget.style.boxShadow = `0 4px 15px ${stat.shadowColor}`"
      >
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center" :style="{ backgroundColor: stat.bgColor }">
            <component :is="stat.icon" :size="20" :style="{ color: stat.iconColor }" />
          </div>
        </div>
        <h3 class="text-3xl mb-1" style="color: #2F4F4F">{{ stat.value }}</h3>
        <p class="text-sm text-gray-500 mb-2">{{ stat.label }}</p>
        <p v-if="stat.growth" class="text-xs" style="color: #4CAF50">{{ stat.growth }}</p>
      </div>
    </div>

    <!-- Navigation rapide -->
    <div class="mb-8">
      <h3 class="text-xl mb-5" style="color: #2F4F4F">Navigation rapide</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5">
        <button
          v-for="nav in navigationItems"
          :key="nav.section"
          @click="$emit('navigate', nav.section)"
          class="bg-white rounded-2xl p-6 border border-gray-100 text-center transition-all group hover:scale-105 relative"
          :style="{ boxShadow: `0 4px 20px ${nav.shadowColor}` }"
          @mouseenter="(e) => e.currentTarget.style.boxShadow = `0 8px 35px ${nav.shadowColorHover}`"
          @mouseleave="(e) => e.currentTarget.style.boxShadow = `0 4px 20px ${nav.shadowColor}`"
        >
          <!-- Badge for pending items -->
          <div 
            v-if="nav.badge && nav.badge > 0"
            class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs text-white font-bold"
            :style="{ backgroundColor: nav.badgeColor }"
          >
            {{ nav.badge }}
          </div>
          
          <div 
            class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform" 
            :style="{ backgroundColor: nav.bgColor }"
          >
            <component :is="nav.icon" :size="24" :style="{ color: nav.iconColor }" />
          </div>
          <h4 class="mb-1" :style="{ color: nav.titleColor }">{{ nav.title }}</h4>
          <p class="text-xs text-gray-500">{{ nav.description }}</p>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  Users, 
  UserCheck, 
  Calendar, 
  Award, 
  TrendingUp,
  ClipboardList,
  Package,
  History,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  loading: Boolean
})

const emit = defineEmits(['navigate'])

const statsCards = computed(() => [
  {
    key: 'clients',
    icon: Users,
    value: props.stats.totalClients,
    label: "Total d'utilisateurs",
    growth: props.stats.clientsGrowth + ' ce mois dernier',
    bgColor: '#E8F5E9',
    iconColor: '#92B08B',
    shadowColor: 'rgba(146, 176, 139, 0.15)',
    shadowColorHover: 'rgba(146, 176, 139, 0.25)'
  },
  {
    key: 'intervenants',
    icon: UserCheck,
    value: props.stats.totalIntervenants,
    label: 'Intervenants actifs',
    growth: props.stats.intervenantsGrowth,
    bgColor: '#E3F2FD',
    iconColor: '#1A5FA3',
    shadowColor: 'rgba(26, 95, 163, 0.15)',
    shadowColorHover: 'rgba(26, 95, 163, 0.25)'
  },
  {
    key: 'interventions',
    icon: Calendar,
    value: props.stats.interventionsMois,
    label: 'Interventions ce mois',
    growth: props.stats.interventionsGrowth,
    bgColor: '#F3E5F5',
    iconColor: '#9C27B0',
    shadowColor: 'rgba(156, 39, 176, 0.15)',
    shadowColorHover: 'rgba(156, 39, 176, 0.25)'
  },
  {
    key: 'satisfaction',
    icon: Award,
    value: props.stats.satisfaction + '%',
    label: `Satisfaction (${props.stats.satisfactionLabel})`,
    bgColor: '#FFFBEA',
    iconColor: '#FEE347',
    shadowColor: 'rgba(254, 227, 71, 0.25)',
    shadowColorHover: 'rgba(254, 227, 71, 0.35)'
  },
  {
    key: 'heures',
    icon: TrendingUp,
    value: props.stats.heuresTotal + 'h',
    label: 'Heures totales',
    growth: props.stats.heuresGrowth,
    bgColor: '#E1F5FE',
    iconColor: '#A5D4E6',
    shadowColor: 'rgba(165, 212, 230, 0.25)',
    shadowColorHover: 'rgba(165, 212, 230, 0.35)'
  }
])

const navigationItems = computed(() => [
  {
    section: 'clients',
    icon: Users,
    title: 'Clients',
    description: 'Gérer les comptes',
    bgColor: '#E8F5E9',
    iconColor: '#92B08B',
    titleColor: '#92B08B',
    shadowColor: 'rgba(146, 176, 139, 0.2)',
    shadowColorHover: 'rgba(146, 176, 139, 0.35)',
    badge: null,
    badgeColor: null
  },
  {
    section: 'intervenants',
    icon: UserCheck,
    title: 'Intervenants',
    description: 'Gérer les profils',
    bgColor: '#E3F2FD',
    iconColor: '#1A5FA3',
    titleColor: '#1A5FA3',
    shadowColor: 'rgba(26, 95, 163, 0.2)',
    shadowColorHover: 'rgba(26, 95, 163, 0.35)',
    badge: null,
    badgeColor: null
  },
  {
    section: 'demandes',
    icon: ClipboardList,
    title: 'Demandes',
    description: 'Valider les intervenants',
    bgColor: '#FFF3E0',
    iconColor: '#FF9800',
    titleColor: '#FF9800',
    shadowColor: 'rgba(255, 152, 0, 0.2)',
    shadowColorHover: 'rgba(255, 152, 0, 0.35)',
    badge: props.stats.demandesEnAttente,
    badgeColor: '#F44336'
  },
  {
    section: 'services',
    icon: Package,
    title: 'Services',
    description: 'Gérer les services',
    bgColor: '#F3E5F5',
    iconColor: '#9C27B0',
    titleColor: '#9C27B0',
    shadowColor: 'rgba(156, 39, 176, 0.2)',
    shadowColorHover: 'rgba(156, 39, 176, 0.35)',
    badge: null,
    badgeColor: null
  },
  {
    section: 'reclamations',
    icon: AlertCircle,
    title: 'Réclamations',
    description: 'Traiter les signalements',
    bgColor: '#FFEBEE',
    iconColor: '#F44336',
    titleColor: '#F44336',
    shadowColor: 'rgba(244, 67, 54, 0.2)',
    shadowColorHover: 'rgba(244, 67, 54, 0.35)',
    badge: props.stats.reclamationsNouvelles,
    badgeColor: '#FF9800'
  },
  {
    section: 'historique',
    icon: History,
    title: 'Historique',
    description: 'Consulter l\'activité',
    bgColor: '#E1F5FE',
    iconColor: '#03A9F4',
    titleColor: '#03A9F4',
    shadowColor: 'rgba(3, 169, 244, 0.2)',
    shadowColorHover: 'rgba(3, 169, 244, 0.35)',
    badge: null,
    badgeColor: null
  }
])
</script>

