<template>
  <div class="space-y-6">
    <!-- Stats -->
    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color: #92B08B">
        <p class="text-sm text-gray-600 mb-1">Revenus totaux</p>
        <p class="text-3xl" style="color: #92B08B">{{ totalAmount }} DH</p>
      </div>
      <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color: #1A5FA3">
        <p class="text-sm text-gray-600 mb-1">Missions complétées</p>
        <p class="text-3xl" style="color: #1A5FA3">{{ totalMissions }}</p>
      </div>
      <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" style="border-left-color: #FEE347">
        <p class="text-sm text-gray-600 mb-1">Note moyenne</p>
        <p class="text-3xl" style="color: #E8793F">{{ averageRating.toFixed(1) }}</p>
      </div>
    </div>

    <!-- Filters & History List -->
    <div class="bg-white rounded-lg shadow-sm p-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl" style="color: #2F4F4F">Historique des missions</h1>
        
        <!-- Filters -->
        <div class="flex gap-3">
          <select
            v-model="filterYear"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 outline-none"
            style="--tw-ring-color: #92B08B"
          >
            <option value="all">Toutes les années</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
          </select>
          <select
            v-model="filterMonth"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 outline-none"
          >
            <option value="all">Tous les mois</option>
            <option value="1">Janvier</option>
            <option value="2">Février</option>
            <option value="3">Mars</option>
            <option value="4">Avril</option>
            <option value="5">Mai</option>
            <option value="6">Juin</option>
            <option value="7">Juillet</option>
            <option value="8">Août</option>
            <option value="9">Septembre</option>
            <option value="10">Octobre</option>
            <option value="11">Novembre</option>
            <option value="12">Décembre</option>
          </select>
        </div>
      </div>

      <!-- History Items -->
      <div class="space-y-4">
        <div
          v-for="item in filteredHistory"
          :key="item.id"
          class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start gap-6">
            <!-- Client Image -->
            <img
              :src="item.clientImage"
              :alt="item.clientName"
              class="w-16 h-16 rounded-full object-cover border-2"
              style="border-color: #92B08B"
            />

            <div class="flex-1">
              <!-- Header -->
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="text-xl mb-1" style="color: #2F4F4F">{{ item.clientName }}</h3>
                  <p class="text-gray-600">{{ item.task }}</p>
                </div>
                <div class="text-right">
                  <p class="text-2xl mb-1" style="color: #92B08B">{{ item.amount }} DH</p>
                  <p class="text-sm text-gray-600">{{ item.duration }}</p>
                </div>
              </div>

              <!-- Details -->
              <div class="flex items-center gap-6 text-sm text-gray-600 mb-3">
                <span class="flex items-center gap-1">
                  <Calendar :size="16" style="color: #1A5FA3" />
                  {{ new Date(item.date).toLocaleDateString('fr-FR', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                  }) }}
                </span>
                <span class="flex items-center gap-1">
                  <MapPin :size="16" style="color: #E8793F" />
                  {{ item.location }}
                </span>
              </div>

              <!-- Rating & Review -->
              <div v-if="item.rating" class="p-3 rounded-lg" style="background-color: #FFF9E5">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-sm text-gray-600">Évaluation du client :</span>
                  <div class="flex items-center gap-1">
                    <Star
                      v-for="star in 5"
                      :key="star"
                      :size="16"
                      :class="star <= item.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                    />
                    <span class="text-sm ml-1">({{ item.rating }}/5)</span>
                  </div>
                </div>
                <p v-if="item.review" class="text-sm text-gray-700 italic">"{{ item.review }}"</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredHistory.length === 0" class="text-center py-16 text-gray-500">
        <p>Aucune mission trouvée pour cette période</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Calendar, MapPin, User, Coins, Star } from 'lucide-vue-next';

const filterYear = ref('2024');
const filterMonth = ref('all');

const history = [
  {
    id: 1,
    clientName: 'Karim Alaoui',
    clientImage: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop',
    service: 'Ménage',
    task: 'Nettoyage en profondeur',
    date: '2024-11-28',
    duration: '4 heures',
    amount: 96,
    location: 'Tetouan Centre',
    rating: 5,
    review: 'Excellent travail, très professionnel !',
  },
  {
    id: 2,
    clientName: 'Meryem Benali',
    clientImage: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop',
    service: 'Ménage',
    task: 'Ménage résidentiel régulier',
    date: '2024-11-25',
    duration: '3 heures',
    amount: 60,
    location: 'Tetouan Medina',
    rating: 5,
    review: 'Toujours un plaisir de travailler avec ce client.',
  },
  {
    id: 3,
    clientName: 'Samira Idrissi',
    clientImage: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop',
    service: 'Ménage',
    task: 'Lavage vitres',
    date: '2024-11-20',
    duration: '2 heures',
    amount: 44,
    location: 'Tetouan Saniat Rmel',
    rating: 4,
  },
  {
    id: 4,
    clientName: 'Omar Tazi',
    clientImage: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop',
    service: 'Ménage',
    task: 'Nettoyage mobilier',
    date: '2024-11-15',
    duration: '3 heures',
    amount: 69,
    location: 'Tetouan Centre',
    rating: 5,
  },
  {
    id: 5,
    clientName: 'Fatima Moussaoui',
    clientImage: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop',
    service: 'Ménage',
    task: 'Nettoyage post-travaux',
    date: '2024-11-10',
    duration: '5 heures',
    amount: 125,
    location: 'Tetouan Centre',
    rating: 5,
    review: 'Impeccable ! Très satisfaite du résultat.',
  },
  {
    id: 6,
    clientName: 'Ahmed Chakir',
    clientImage: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop',
    service: 'Ménage',
    task: 'Nettoyage bureaux',
    date: '2024-10-28',
    duration: '6 heures',
    amount: 126,
    location: 'Tetouan Martil',
  },
];

const filteredHistory = computed(() => {
  return history.filter((item) => {
    const itemDate = new Date(item.date);
    const itemYear = itemDate.getFullYear().toString();
    const itemMonth = (itemDate.getMonth() + 1).toString();

    if (filterYear.value !== 'all' && itemYear !== filterYear.value) return false;
    if (filterMonth.value !== 'all' && itemMonth !== filterMonth.value) return false;
    return true;
  });
});

const totalAmount = computed(() => {
  return filteredHistory.value.reduce((sum, item) => sum + item.amount, 0);
});

const totalMissions = computed(() => {
  return filteredHistory.value.length;
});

const averageRating = computed(() => {
  const ratedItems = filteredHistory.value.filter(i => i.rating);
  if (ratedItems.length === 0) return 0;
  return ratedItems.reduce((sum, item) => sum + (item.rating || 0), 0) / ratedItems.length;
});
</script>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
