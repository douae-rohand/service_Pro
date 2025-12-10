<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-lg sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center gap-4">
          <button
            @click="$emit('back')"
            @mouseenter="hoverBackButton = true"
            @mouseleave="hoverBackButton = false"
            class="flex items-center gap-2 text-gray-600 hover:text-white transition-all mb-4 px-4 py-2 rounded-lg"
            :style="{ backgroundColor: hoverBackButton ? currentService.color : 'transparent' }"
          >
            <ArrowLeft :size="20" />
          </button>
          <div>
            <h1 class="text-5xl" :style="{ color: currentService.color }">
              Service de {{ currentService.name }}
            </h1>
            <p class="text-gray-700 mt-2 text-lg">
              Découvrez toutes nos tâches et nos meilleurs intervenants
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Tasks Section -->
      <section class="mb-16">
        <div class="text-center mb-12">
          <h2 class="text-5xl mb-4" :style="{ color: currentService.color }">
            Sous-service disponibles
          </h2>
          <p class="text-gray-600 text-lg">
            Choisissez parmi notre large gamme de services professionnels
          </p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(task, index) in currentService.tasks"
            :key="task.id"
            class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all transform hover:scale-105 cursor-pointer group"
            :style="{ animation: `fadeInUp 0.6s ease-out ${index * 0.1}s both` }"
          >
            <!-- Image -->
            <div class="relative h-56 overflow-hidden">
              <img
                :src="task.image"
                :alt="task.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            </div>
            
            <!-- Content -->
            <div class="p-6">
              <h3 class="text-xl mb-2" :style="{ color: currentService.color }">
                {{ task.name }}
              </h3>
              <p class="text-gray-600 text-sm mb-4">{{ task.description }}</p>
              
              <button 
                class="w-full py-3 rounded-lg text-white transition-all hover:opacity-90 flex items-center justify-center gap-2"
                :style="{ backgroundColor: currentService.color }"
                @click="$emit('task-click', task.name)"
              >
                <CheckCircle :size="18" />
                Réserver
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Intervenants Section -->
      <section class="bg-white rounded-2xl p-8 shadow-lg">
        <div class="text-center mb-10">
          <h2 class="text-4xl mb-3" :style="{ color: currentService.color }">
            Nos meilleurs intervenants
          </h2>
          <p class="text-gray-600">
            Des professionnels vérifiés et expérimentés à votre service
          </p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="intervenant in currentService.intervenants"
            :key="intervenant.id"
            class="bg-gray-50 rounded-xl shadow-md hover:shadow-lg transition-all overflow-hidden"
          >
            <div class="p-6">
              <div class="flex items-start gap-4 mb-4">
                <img
                  :src="intervenant.image"
                  :alt="intervenant.name"
                  class="w-16 h-16 rounded-full object-cover border-3"
                  :style="{ borderColor: currentService.color }"
                />
                <div class="flex-1">
                  <h3 class="text-lg">{{ intervenant.name }}</h3>
                  <span
                    v-if="intervenant.verified"
                    class="text-xs px-2 py-1 rounded-full inline-block mt-1"
                    :style="{
                      backgroundColor: currentService.color,
                      color: 'white',
                    }"
                  >
                    ✓ Vérifié
                  </span>
                  <div class="flex items-center gap-2 mt-2">
                    <Star :size="16" class="fill-yellow-400 text-yellow-400" />
                    <span>{{ intervenant.rating }}</span>
                    <span class="text-gray-500 text-sm">({{ intervenant.reviewCount }} avis)</span>
                  </div>
                </div>
              </div>

              <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-gray-700 text-sm">
                  <MapPin :size="16" :style="{ color: currentService.color }" />
                  <span>{{ intervenant.location }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-700">
                  <Coins :size="16" :style="{ color: currentService.color }" />
                  <span>{{ intervenant.hourlyRate }}DH/heure</span>
                </div>
              </div>

              <div class="mb-4">
                <p class="text-xs text-gray-500 mb-2">Spécialités :</p>
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(specialty, index) in intervenant.specialties"
                    :key="index"
                    class="text-xs px-2 py-1 rounded-md"
                    :style="{
                      backgroundColor: currentService.color + '20',
                      color: currentService.color
                    }"
                  >
                    {{ specialty }}
                  </span>
                </div>
              </div>

              <button
                @click="$emit('view-profile', intervenant.id)"
                class="w-full text-white py-2.5 rounded-lg transition-all hover:opacity-90"
                :style="{ backgroundColor: currentService.color }"
              >
                Voir le profil
              </button>
            </div>
          </div>
        </div>
        
        <!-- Button to view all intervenants -->
        <div class="text-center mt-12">
          <button
            @click="$emit('view-all-intervenants')"
            @mouseenter="hoverViewAll = true"
            @mouseleave="hoverViewAll = false"
            class="px-10 py-4 rounded-xl text-white text-lg transition-all transform hover:scale-105 hover:shadow-xl inline-flex items-center gap-3"
            :style="{ 
              backgroundColor: currentService.color,
              boxShadow: hoverViewAll 
                ? `0 15px 35px ${currentService.color}60`
                : `0 10px 25px ${currentService.color}40`
            }"
          >
            {{ service === 'menage' ? 'Voir tous les intervenants ménage' : 'Voir tous les jardiniers' }}
            <ArrowLeft :size="20" class="rotate-180" />
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { ArrowLeft, Star, Coins, MapPin, CheckCircle } from 'lucide-vue-next';

export default {
  name: 'ServiceDetailPage',
  components: {
    ArrowLeft,
    Star,
    Coins,
    MapPin,
    CheckCircle
  },
  props: {
    service: {
      type: String,
      required: true,
      validator: (value) => ['jardinage', 'menage'].includes(value)
    }
  },
  emits: ['back', 'view-all-intervenants', 'view-profile', 'task-click'],
  data() {
    return {
      hoverBackButton: false,
      hoverViewAll: false,
      serviceInfo: {
        jardinage: {
          name: 'Jardinage',
          color: '#92B08B',
          tasks: [
            { 
              id: 1, 
              name: 'Tonte de pelouse', 
              description: 'Entretien régulier de votre gazon',
              image: 'https://images.unsplash.com/photo-1723811898182-aff0c2eca53f?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxsYXduJTIwbW93aW5nJTIwZ2FyZGVufGVufDF8fHx8MTc2NDY2MTk1OXww&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#A8D08D',
              emoji: '🌿'
            },
            { 
              id: 2, 
              name: 'Taille de haies', 
              description: 'Façonnage et entretien de vos haies',
              image: 'https://images.unsplash.com/photo-1558904541-efa843a96f01?w=1080',
              accent: '#7FB069',
              emoji: '✂️'
            },
            { 
              id: 3, 
              name: 'Plantation de fleurs', 
              description: 'Création et aménagement de massifs',
              image: 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=1080',
              accent: '#C8E6A0',
              emoji: '🌸'
            },
            { 
              id: 4, 
              name: 'Élagage d\'arbres', 
              description: 'Taille et soin des arbres',
              image: 'https://images.unsplash.com/photo-1626828476637-5bd713ef9f22?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0cmVlJTIwcHJ1bmluZ3xlbnwxfHx8fDE3NjQ1ODY0NTJ8MA&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#6A9955',
              emoji: '🌳'
            },
            { 
              id: 5, 
              name: 'Désherbage', 
              description: 'Élimination des mauvaises herbes',
              image: 'https://images.unsplash.com/photo-1706033844083-91d7a6fdab12?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx3ZWVkaW5nJTIwZ2FyZGVufGVufDF8fHx8MTc2NDY2MTk2MXww&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#92B08B',
              emoji: '🌱'
            },
            { 
              id: 6, 
              name: 'Entretien de potager', 
              description: 'Soin et maintenance de votre potager',
              image: 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1080',
              accent: '#B5D99C',
              emoji: '🥬'
            },
          ],
          intervenants: [
            {
              id: 1,
              name: 'Youssef Benali',
              rating: 4.9,
              reviewCount: 127,
              hourlyRate: 25,
              location: 'Tetouan Centre',
              image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Tonte de pelouse', 'Taille de haies', 'Plantation'],
            },
            {
              id: 2,
              name: 'Hassan Alami',
              rating: 4.8,
              reviewCount: 156,
              hourlyRate: 28,
              location: 'Tetouan Medina',
              image: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Élagage', 'Désherbage', 'Entretien potager'],
            },
            {
              id: 3,
              name: 'Omar El Amrani',
              rating: 4.7,
              reviewCount: 94,
              hourlyRate: 26,
              location: 'Tetouan Martil',
              image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Tonte de pelouse', 'Taille de haies', 'Plantation de fleurs'],
            },
          ],
        },
        menage: {
          name: 'Ménage',
          color: '#4682B4',
          tasks: [
            { 
              id: 1, 
              name: 'Ménage résidentiel & régulier', 
              description: 'Nettoyage complet, entretien régulier, dépoussiérage, sols, cuisine, salle de bain',
              image: 'https://images.unsplash.com/photo-1758273238415-01ec03d9ef27?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxyZXNpZGVudGlhbCUyMGhvdXNlJTIwY2xlYW5pbmd8ZW58MXx8fHwxNzY0Njk5ODU4fDA&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#8AC4D0',
              emoji: '🏠'
            },
            { 
              id: 2, 
              name: 'Nettoyage en profondeur (Deep Cleaning)', 
              description: 'Désinfection totale, nettoyage sous/derrière meubles, détartrage, plinthes, lavage des murs',
              image: 'https://images.unsplash.com/photo-1737372805905-be0b91ec86fb?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkZWVwJTIwY2xlYW5pbmclMjBob21lfGVufDF8fHx8MTc2NDY5OTg1OHww&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#6DB3C8',
              emoji: '✨'
            },
            { 
              id: 3, 
              name: 'Nettoyage spécial : déménagement & post-travaux', 
              description: 'Avant/après déménagement, après rénovation, élimination poussières fines et résidus',
              image: 'https://images.unsplash.com/photo-1631304137068-16117132ee8b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb3ZpbmclMjBob3VzZSUyMGNsZWFuaW5nfGVufDF8fHx8MTc2NDYyMzY3M3ww&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#7EC8E3',
              emoji: '📦'
            },
            { 
              id: 4, 
              name: 'Lavage vitres & surfaces spécialisées', 
              description: 'Vitres intérieur/extérieur, marbre, bois nobles, soins textiles, moquettes & tapis',
              image: 'https://images.unsplash.com/photo-1761689502577-0013be84f1bf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx3aW5kb3clMjBjbGVhbmluZyUyMHByb2Zlc3Npb25hbHxlbnwxfHx8fDE3NjQ2NzUyMDl8MA&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#B8E2F0',
              emoji: '🪟'
            },
            { 
              id: 5, 
              name: 'Nettoyage mobilier & textiles', 
              description: 'Shampooing canapé, rénovation tissus/cuir, fauteuils, matelas, blanchisserie & repassage',
              image: 'https://images.unsplash.com/photo-1654511830326-63a577771a7e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxmdXJuaXR1cmUlMjB1cGhvbHN0ZXJ5JTIwY2xlYW5pbmd8ZW58MXx8fHwxNzY0Njk5ODY0fDA&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#A5D4E6',
              emoji: '🛋️'
            },
            { 
              id: 6, 
              name: 'Nettoyage professionnel (bureaux & commerces)', 
              description: 'Entretien quotidien bureaux, désinfection postes de travail, vidage poubelles, salles de réunion',
              image: 'https://images.unsplash.com/photo-1762235634143-6d350fe349e8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxvZmZpY2UlMjBjb21tZXJjaWFsJTIwY2xlYW5pbmd8ZW58MXx8fHwxNzY0Njk5ODYwfDA&ixlib=rb-4.1.0&q=80&w=1080',
              accent: '#C1E7F4',
              emoji: '🏢'
            },
          ],
          intervenants: [
            {
              id: 1,
              name: 'Fatima Tazi',
              rating: 5.0,
              reviewCount: 89,
              hourlyRate: 22,
              location: 'Tetouan M\'diq',
              image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Ménage résidentiel & régulier', 'Nettoyage mobilier & textiles', 'Lavage vitres & surfaces spécialisées'],
            },
            {
              id: 2,
              name: 'Amina Chakir',
              rating: 4.9,
              reviewCount: 203,
              hourlyRate: 24,
              location: 'Tetouan Centre',
              image: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Nettoyage en profondeur (Deep Cleaning)', 'Nettoyage professionnel (bureaux & commerces)', 'Ménage résidentiel & régulier'],
            },
            {
              id: 3,
              name: 'Salma Moussaoui',
              rating: 5.0,
              reviewCount: 145,
              hourlyRate: 23,
              location: 'Tetouan Medina',
              image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop',
              verified: true,
              specialties: ['Nettoyage spécial : déménagement & post-travaux', 'Lavage vitres & surfaces spécialisées', 'Nettoyage mobilier & textiles'],
            },
          ],
        },
      }
    };
  },
  computed: {
    currentService() {
      return this.serviceInfo[this.service];
    }
  }
};
</script>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>