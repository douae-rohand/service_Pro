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
    label: 'Clients',
  },
  {
    icon: UserCheckIcon,
    number: '0',
    label: 'Intervenants',
  },
  {
    icon: BriefcaseIcon,
    number: '0',
    label: 'Sous-Services',
  },
])

// Fonction pour l'animation de comptage
const animateValue = (obj, start, end, duration) => {
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    
    // Easing function (easeOutExpo)
    const easedProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
    
    const current = Math.floor(easedProgress * (end - start) + start);
    obj.number = formatNumber(current);
    
    if (progress < 1) {
      window.requestAnimationFrame(step);
    } else {
       obj.number = formatNumber(end); // Assurer la valeur finale exacte
    }
  };
  window.requestAnimationFrame(step);
}

// Fonction pour formater les nombres avec virgules et le signe +
const formatNumber = (num) => {
  return num.toLocaleString('fr-FR') + '+'
}

// Charger les statistiques depuis l'API
onMounted(async () => {
  try {
    const response = await statsService.getAll()
    const data = response.data
    
    if (!data) return

    // Préparer les données cibles
    const targets = [
      data.clients_count || data.satisfied_clients || 0,
      data.intervenants_count || data.qualified_intervenants || 0,
      data.subservices_count || data.completed_services || 0
    ];

    // Mettre à jour les labels (on garde '0+' au début)
    stats.value[0].number = '0+';
    stats.value[1].number = '0+';
    stats.value[2].number = '0+';

    // Lancer les animations
    targets.forEach((target, index) => {
      // Créer un objet réactif temporaire pour l'animation ou manipuler directement l'objet stat
      // Ici on modifie directement la propriété .number de l'objet dans le tableau réactif
      animateValue(stats.value[index], 0, target, 1000); // 1 secondes de durée
    });

  } catch (error) {
    console.error('Erreur stats:', error)
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

