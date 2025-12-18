<template>
  <div class="max-w-5xl">
    <!-- Stats Cards -->
    <div class="grid md:grid-cols-4 gap-4 mb-6">
      <div 
        class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
        style="border-left-color: #FEE347"
      >
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600">Note Moyenne</p>
          <Star :size="18" style="color: #FEE347" fill="#FEE347" />
        </div>
        <p class="text-3xl" style="color: #2F4F4F">{{ stats.averageRating }}</p>
        <p class="text-xs text-gray-500 mt-1">sur 5.0</p>
      </div>

      <div 
        class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
        style="border-left-color: #92B08B"
      >
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600">Total Avis</p>
          <ThumbsUp :size="18" style="color: #92B08B" />
        </div>
        <p class="text-3xl" style="color: #92B08B">{{ stats.totalReviews }}</p>
        <p class="text-xs text-gray-500 mt-1">avis clients</p>
      </div>

      <div 
        class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
        style="border-left-color: #1A5FA3"
      >
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600">Missions</p>
          <Award :size="18" style="color: #1A5FA3" />
        </div>
        <p class="text-3xl" style="color: #1A5FA3">{{ stats.completedMissions }}</p>
        <p class="text-xs text-gray-500 mt-1">complétées</p>
      </div>

      <div 
        class="bg-white rounded-lg shadow-sm p-4 border-l-4" 
        style="border-left-color: #E8793F"
      >
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600">Taux Réponse</p>
          <TrendingUp :size="18" style="color: #E8793F" />
        </div>
        <p class="text-3xl" style="color: #E8793F">{{ stats.responseRate }}%</p>
        <p class="text-xs text-gray-500 mt-1">réactivité</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Rating Distribution -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-lg mb-4" style="color: #2F4F4F">Distribution des notes</h2>
          <div class="space-y-3">
            <div 
              v-for="item in distribution" 
              :key="item.stars" 
              class="flex items-center gap-3"
            >
              <span class="text-sm text-gray-600 w-8">{{ item.stars }} ★</span>
              <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all"
                  :style="{
                    width: `${calculatePercentage(item.count)}%`,
                    backgroundColor: '#FEE347',
                  }"
                />
              </div>
              <span class="text-xs text-gray-500 w-12 text-right">
                {{ calculatePercentage(item.count) }}%
              </span>
            </div>
          </div>

          <!-- Excellence Badge -->
          <div 
            class="mt-6 p-4 rounded-lg text-center" 
            style="background-color: #E8F5E9"
          >
            <Award :size="32" style="color: #92B08B" class="mx-auto mb-2" />
            <p class="text-sm" style="color: #92B08B">Excellence Client</p>
            <p class="text-xs text-gray-600 mt-1">
              {{ calculatePercentage(stats.fiveStars + stats.fourStars) }}% d'avis positifs
            </p>
          </div>
        </div>
      </div>

      <!-- Reviews List -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h2 class="text-lg mb-4" style="color: #2F4F4F">
            Avis récents ({{ reviews.length }})
          </h2>
          <div class="space-y-4">
            <div
              v-for="review in reviews"
              :key="review.id"
              class="p-4 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow"
            >
              <div class="flex items-start gap-3 mb-3">
                <img
                  :src="review.clientImage"
                  :alt="review.clientName"
                  class="w-10 h-10 rounded-full object-cover"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm" style="color: #2F4F4F">
                      {{ review.clientName }}
                    </h3>
                    <!-- Render Stars -->
                    <div class="flex gap-0.5">
                      <Star
                        v-for="star in 5"
                        :key="star"
                        :size="16"
                        :fill="star <= review.rating ? '#FEE347' : 'none'"
                        :style="{ color: star <= review.rating ? '#FEE347' : '#D1D5DB' }"
                      />
                    </div>
                  </div>
                  <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>{{ review.service }}</span>
                    <span>•</span>
                    <span>
                      {{ new Date(review.date).toLocaleDateString('fr-FR', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                      }) }}
                    </span>
                  </div>
                </div>
              </div>
              <p class="text-sm text-gray-700">{{ review.comment }}</p>
            </div>
          </div>

          <!-- Load More -->
          <button
            class="w-full mt-4 py-2 rounded-lg text-sm transition-colors"
            style="background-color: #F3E293; color: #2F4F4F"
            @mouseenter="(e) => (e.currentTarget.style.backgroundColor = '#FEE347')"
            @mouseleave="(e) => (e.currentTarget.style.backgroundColor = '#F3E293')"
          >
            Voir plus d'avis
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Star, TrendingUp, Award, ThumbsUp } from 'lucide-vue-next';

const reviews = [
  {
    id: 1,
    clientName: 'Karim Alaoui',
    clientImage: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop',
    rating: 5,
    date: '2024-11-28',
    service: 'Nettoyage en profondeur',
    comment: 'Excellent travail ! Très professionnelle et ponctuelle. Mon appartement n\'a jamais été aussi propre. Je recommande vivement.',
  },
  {
    id: 2,
    clientName: 'Meryem Benali',
    clientImage: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop',
    rating: 5,
    date: '2024-11-25',
    service: 'Ménage régulier',
    comment: 'Service impeccable, je suis très satisfaite. Amina est attentive aux détails et très sympathique.',
  },
  {
    id: 3,
    clientName: 'Samira Idrissi',
    clientImage: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop',
    rating: 4,
    date: '2024-11-20',
    service: 'Lavage vitres',
    comment: 'Très bon travail, les vitres sont parfaitement propres. Peut-être un peu plus de temps sur les cadres.',
  },
  {
    id: 4,
    clientName: 'Omar Tazi',
    clientImage: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop',
    rating: 5,
    date: '2024-11-15',
    service: 'Nettoyage mobilier',
    comment: 'Parfait ! Les meubles et textiles sont comme neufs. Très professionnelle.',
  },
  {
    id: 5,
    clientName: 'Fatima Ziani',
    clientImage: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop',
    rating: 5,
    date: '2024-11-10',
    service: 'Ménage post-travaux',
    comment: 'Excellent ! Elle a réussi à tout nettoyer après les travaux. Très minutieuse et efficace.',
  },
];

const stats = {
  averageRating: 4.8,
  totalReviews: 72,
  completedMissions: 85,
  responseRate: 95,
  fiveStars: 62,
  fourStars: 8,
  threeStars: 2,
  twoStars: 0,
  oneStars: 0,
};

const distribution = [
  { stars: 5, count: stats.fiveStars },
  { stars: 4, count: stats.fourStars },
  { stars: 3, count: stats.threeStars },
  { stars: 2, count: stats.twoStars },
  { stars: 1, count: stats.oneStars },
];

const calculatePercentage = (count) => {
  return Math.round((count / stats.totalReviews) * 100);
};
</script>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
