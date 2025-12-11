<template>
  <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
      <!-- Header -->
      <div
        class="sticky top-0 p-6 text-white z-10 flex items-center justify-between"
        style="background-color: #1a5fa3"
      >
        <div class="flex items-center gap-4">
          <img
            :src="intervenant.image"
            :alt="intervenant.name"
            class="w-16 h-16 rounded-full object-cover border-4 border-white"
          />
          <div>
            <h2 class="text-2xl">Évaluer la prestation</h2>
            <p class="opacity-90">{{ intervenant.name }}</p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="p-2 hover:bg-white hover:bg-opacity-20 rounded-full transition-all"
        >
          <X :size="28" />
        </button>
      </div>

      <div class="p-6 space-y-6">
        <!-- Service Info -->
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-sm text-gray-600 mb-1">Service</p>
          <p class="font-bold text-lg" style="color: #2f4f4f">{{ service }} - {{ task }}</p>
        </div>

        <!-- Rating Criteria -->
        <div>
          <h3 class="font-bold text-lg mb-4" style="color: #2f4f4f">
            Évaluez les différents aspects
          </h3>
          <div class="space-y-4">
            <div
              v-for="(label, key) in ratingCriteria"
              :key="key"
              class="bg-gray-50 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-2">
                <label class="font-bold text-gray-700">{{ label }}</label>
                <span class="text-2xl font-bold" style="color: #fee347">
                  {{ ratings[key] }}/5
                </span>
              </div>
              <div class="flex gap-2">
                <button
                  v-for="star in 5"
                  :key="star"
                  @click="ratings[key] = star"
                  class="p-2 hover:scale-110 transition-transform"
                >
                  <Star
                    :size="32"
                    :fill="star <= ratings[key] ? '#FEE347' : 'none'"
                    :color="star <= ratings[key] ? '#FEE347' : '#D1D5DB'"
                  />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Overall Rating Display -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 text-center">
          <p class="text-sm text-gray-600 mb-2">Note globale</p>
          <div class="flex items-center justify-center gap-2 mb-2">
            <Star :size="40" fill="#FEE347" color="#FEE347" />
            <span class="text-5xl font-bold" style="color: #2f4f4f">
              {{ overallRating.toFixed(1) }}
            </span>
            <span class="text-2xl text-gray-500">/5</span>
          </div>
          <div class="flex items-center justify-center gap-1">
            <Star
              v-for="star in 5"
              :key="star"
              :size="20"
              :fill="star <= Math.round(overallRating) ? '#FEE347' : 'none'"
              color="#FEE347"
            />
          </div>
        </div>

        <!-- Comment -->
        <div>
          <label class="font-bold text-gray-700 mb-2 block">
            Commentaire (optionnel)
          </label>
          <textarea
            v-model="comment"
            rows="4"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none resize-none"
            placeholder="Partagez votre expérience avec cet intervenant..."
          ></textarea>
          <p class="text-xs text-gray-500 mt-1">{{ comment.length }}/500 caractères</p>
        </div>

        <!-- Would Recommend -->
        <div class="bg-gray-50 rounded-xl p-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="wouldRecommend"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300"
              style="accent-color: #92b08b"
            />
            <span class="font-bold text-gray-700">
              Je recommanderais cet intervenant
            </span>
          </label>
        </div>

        <!-- Validation Message -->
        <div v-if="!isValid" class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-600 text-sm">
          Veuillez noter tous les critères avant de soumettre votre évaluation.
        </div>
      </div>

      <!-- Footer -->
      <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6 rounded-b-2xl flex gap-3">
        <button
          @click="$emit('close')"
          class="flex-1 px-6 py-3 rounded-xl border-2 font-bold hover:bg-gray-50 transition-all"
          style="border-color: #1a5fa3; color: #1a5fa3"
        >
          Annuler
        </button>
        <button
          @click="submitRating"
          :disabled="!isValid"
          class="flex-1 px-6 py-3 rounded-xl text-white font-bold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          style="background-color: #92b08b"
        >
          Publier l'évaluation
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { X, Star } from 'lucide-vue-next';

export default {
  name: 'RateIntervenantModal',
  components: {
    X,
    Star
  },
  props: {
    intervenant: {
      type: Object,
      required: true
    },
    service: {
      type: String,
      required: true
    },
    task: {
      type: String,
      required: true
    },
    demandId: {
      type: Number,
      required: false
    }
  },
  emits: ['close', 'submit'],
  data() {
    return {
      ratingCriteria: {
        quality: 'Qualité du travail',
        punctuality: 'Ponctualité',
        professionalism: 'Professionnalisme',
        cleanup: 'Propreté / Nettoyage',
        equipment: 'Matériel / Équipement'
      },
      ratings: {
        quality: 0,
        punctuality: 0,
        professionalism: 0,
        cleanup: 0,
        equipment: 0
      },
      comment: '',
      wouldRecommend: false
    };
  },
  computed: {
    overallRating() {
      const values = Object.values(this.ratings);
      const sum = values.reduce((acc, val) => acc + val, 0);
      const count = values.filter(val => val > 0).length;
      return count > 0 ? sum / values.length : 0;
    },
    isValid() {
      return Object.values(this.ratings).every(rating => rating > 0);
    }
  },
  methods: {
    submitRating() {
      if (!this.isValid) return;

      const ratingData = {
        criteriaRatings: { ...this.ratings },
        overallRating: parseFloat(this.overallRating.toFixed(1)),
        comment: this.comment.trim(),
        wouldRecommend: this.wouldRecommend
      };

      this.$emit('submit', ratingData);
    }
  }
};
</script>