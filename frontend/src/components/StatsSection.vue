<template>
  <section class="py-16" style="background-color: #92B08B">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="(stat, index) in stats"
          :key="index"
          class="text-center text-white transform transition-all duration-500 hover:scale-110 animate-fade-in-up-stat"
          :style="{ animationDelay: `${index * 0.2}s` }"
        >
          <div class="flex justify-center mb-4">
            <div class="bg-white/20 p-4 rounded-full transform transition-all duration-500 hover:rotate-360 hover:bg-white/30">
              <component :is="stat.icon" class="w-8 h-8" />
            </div>
          </div>
          <div class="text-4xl mb-2 font-bold animate-count-up">{{ stat.number }}</div>
          <div class="text-blue-100 font-medium">{{ stat.label }}</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { h, ref, onMounted } from 'vue'
import statsService from '@/services/statsService'

const UsersIcon = () =>
  h('svg', { class: 'w-8 h-8', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      'stroke-width': '2',
      d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    }),
  ])

const UserCheckIcon = () =>
  h('svg', { class: 'w-8 h-8', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      'stroke-width': '2',
      d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    }),
  ])

const BriefcaseIcon = () =>
  h('svg', { class: 'w-8 h-8', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', {
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
      'stroke-width': '2',
      d: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    }),
  ])

// Statistiques réactives
const stats = ref([
  {
    icon: UsersIcon,
    number: '0',
    label: 'Clients Satisfaits',
  },
  {
    icon: UserCheckIcon,
    number: '0',
    label: 'Intervenants Qualifiés',
  },
  {
    icon: BriefcaseIcon,
    number: '0',
    label: 'Services Complétés',
  },
])

// Fonction pour formater les nombres avec virgules et le signe +
const formatNumber = (num) => {
  return num.toLocaleString('fr-FR') + '+'
}

// Charger les statistiques depuis l'API
onMounted(async () => {
  try {
    const response = await statsService.getAll()
    console.log('Réponse API stats:', response.data)
    const data = response.data
    
    // Vérifier que les données sont présentes
    if (!data) {
      console.warn('Aucune donnée reçue de l\'API stats')
      return
    }
    
    // Mettre à jour les statistiques avec les données de l'API
    stats.value = [
      {
        icon: UsersIcon,
        number: formatNumber(data.satisfied_clients || 0),
        label: 'Clients Satisfaits',
      },
      {
        icon: UserCheckIcon,
        number: formatNumber(data.qualified_intervenants || 0),
        label: 'Intervenants Qualifiés',
      },
      {
        icon: BriefcaseIcon,
        number: formatNumber(data.completed_services || 0),
        label: 'Services Complétés',
      },
    ]
    
    console.log('Statistiques mises à jour:', stats.value)
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error)
    console.error('Détails de l\'erreur:', error.response || error.message)
    // En cas d'erreur, garder les valeurs par défaut (0)
  }
})
</script>

<style scoped>
@keyframes fadeInUpStat {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes countUp {
  from {
    opacity: 0;
    transform: scale(0.5);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fade-in-up-stat {
  animation: fadeInUpStat 0.8s ease-out both;
}

.animate-count-up {
  animation: countUp 1s ease-out;
}
</style>

