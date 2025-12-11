<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" style="background-color: #ffffff">
    <!-- Decorative shapes -->
    <div
      class="absolute top-10 left-10 w-32 h-32 rounded-full opacity-30 blur-3xl"
      style="background-color: #92b08b"
    ></div>
    <div
      class="absolute top-20 right-20 w-40 h-40 rounded-full opacity-30 blur-3xl"
      style="background-color: #fee347"
    ></div>

    <div class="max-w-4xl mx-auto my-8 px-4">
      <div class="bg-white rounded-2xl w-full shadow-2xl overflow-hidden">
        <!-- Header -->
        <div
          class="sticky top-0 p-6 text-white z-10 flex items-center justify-between"
          style="background-color: #1a5fa3"
        >
          <div class="flex items-center gap-4">
            <img
              :src="demand.intervenant.image"
              :alt="demand.intervenant.name"
              class="w-16 h-16 rounded-full object-cover border-4 border-white"
            />
            <div>
              <h2 class="text-2xl">Détails de la demande</h2>
              <p class="opacity-90">{{ demand.intervenant.name }}</p>
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
          <!-- Status Badge -->
          <div class="flex items-center justify-between">
            <span
              class="px-6 py-3 rounded-xl text-white font-bold text-lg"
              :style="{ backgroundColor: getStatusColor(demand.status) }"
            >
              {{ getStatusLabel(demand.status) }}
            </span>
            <span class="text-gray-500">Demande #{{ demand.id }}</span>
          </div>

          <!-- Service Info -->
          <div class="bg-gray-50 rounded-xl p-6">
            <h3 class="font-bold text-lg mb-4" style="color: #2f4f4f">
              Informations du service
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-start gap-3">
                <Package :size="20" style="color: #1a5fa3" class="mt-1" />
                <div>
                  <p class="text-sm text-gray-600">Service</p>
                  <p class="font-bold">{{ demand.service }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <CheckCircle :size="20" style="color: #92b08b" class="mt-1" />
                <div>
                  <p class="text-sm text-gray-600">Tâche</p>
                  <p class="font-bold">{{ demand.task }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Calendar :size="20" style="color: #fee347" class="mt-1" />
                <div>
                  <p class="text-sm text-gray-600">Date</p>
                  <p class="font-bold">
                    {{ formatLongDate(demand.date) }}
                  </p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Clock :size="20" style="color: #e8793f" class="mt-1" />
                <div>
                  <p class="text-sm text-gray-600">Heure</p>
                  <p class="font-bold">{{ demand.time }}</p>
                  <p v-if="demand.duration" class="text-sm text-gray-500">Durée: {{ demand.duration }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Location -->
          <div class="bg-gray-50 rounded-xl p-6">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <MapPin :size="20" />
              Localisation
            </h3>
            <p class="mb-1">{{ demand.address }}</p>
            <p class="text-gray-600">{{ demand.quartier }}, Tetouan</p>
          </div>

          <!-- Description -->
          <div v-if="demand.description" class="bg-gray-50 rounded-xl p-6">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <FileText :size="20" />
              Description
            </h3>
            <p class="text-gray-700">{{ demand.description }}</p>
          </div>

          <!-- Photos -->
          <div v-if="demand.photos && demand.photos.length > 0" class="bg-gray-50 rounded-xl p-6">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <Camera :size="20" style="color: #1a5fa3" />
              Photos du site ({{ demand.photos.length }})
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <div
                v-for="(photo, index) in demand.photos"
                :key="index"
                class="relative group cursor-pointer"
                @click="selectedPhoto = photo"
              >
                <img
                  :src="photo"
                  :alt="`Photo ${index + 1}`"
                  class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 hover:border-blue-400 transition-all"
                />
                <div
                  class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all rounded-lg flex items-center justify-center"
                >
                  <ZoomIn
                    :size="24"
                    class="text-white opacity-0 group-hover:opacity-100 transition-all"
                  />
                </div>
                <div
                  class="absolute bottom-2 left-2 px-2 py-1 bg-black bg-opacity-60 text-white text-xs rounded"
                >
                  Photo {{ index + 1 }}
                </div>
              </div>
            </div>
          </div>

          <!-- Materials -->
          <div
            v-if="demand.materials && Object.keys(demand.materials).length > 0"
            class="bg-gray-50 rounded-xl p-6"
          >
            <h3 class="font-bold text-lg mb-4" style="color: #2f4f4f">Matériel disponible</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <div
                v-for="(available, material) in demand.materials"
                :key="material"
                class="flex items-center gap-2 p-3 rounded-lg"
                :class="available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                <CheckCircle v-if="available" :size="16" />
                <X v-else :size="16" />
                <span class="text-sm font-bold capitalize">{{ material }}</span>
              </div>
            </div>
          </div>

          <!-- Intervenant Response -->
          <div
            v-if="demand.intervenantResponse"
            class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6"
          >
            <h3 class="font-bold text-lg mb-3 flex items-center gap-2" style="color: #1a5fa3">
              <User :size="20" />
              Réponse de l'intervenant
            </h3>
            <p class="text-gray-700">{{ demand.intervenantResponse }}</p>
          </div>

          <!-- Invoice - Only for completed -->
          <div
            v-if="demand.status === 'completed' && demand.invoice"
            class="border-2 rounded-xl p-6"
            style="border-color: #92b08b"
          >
            <div class="flex items-center justify-between mb-6">
              <h3 class="font-bold text-2xl flex items-center gap-2" style="color: #2f4f4f">
                <FileText :size="24" style="color: #92b08b" />
                Facture
              </h3>
              <button
                class="px-4 py-2 rounded-lg text-white font-bold flex items-center gap-2 hover:shadow-lg transition-all"
                style="background-color: #1a5fa3"
              >
                <Download :size="18" />
                Télécharger PDF
              </button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                  <p>Date de facturation</p>
                  <p class="font-bold text-gray-900">{{ formatDate(demand.invoice.date) }}</p>
                </div>
                <div>
                  <p>Durée réelle</p>
                  <p class="font-bold text-gray-900">{{ demand.invoice.actualDuration }}</p>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-4 space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600"
                    >Main d'œuvre ({{ demand.invoice.actualDuration }})</span
                  >
                  <span class="font-bold">{{ demand.invoice.laborCost }} DH</span>
                </div>

                <div
                  v-if="
                    demand.invoice.materialsProvided && demand.invoice.materialsProvided.length > 0
                  "
                >
                  <p class="text-gray-600 mb-2">Matériel fourni par l'intervenant</p>
                  <div class="pl-4 space-y-2">
                    <div
                      v-for="(item, index) in demand.invoice.materialsProvided"
                      :key="index"
                      class="flex justify-between text-sm"
                    >
                      <span class="text-gray-700">• {{ item.name }}</span>
                      <span>{{ item.cost }} DH</span>
                    </div>
                  </div>
                  <div class="flex justify-between mt-2">
                    <span class="text-gray-600">Sous-total matériel</span>
                    <span class="font-bold">{{ demand.invoice.materialsCost }} DH</span>
                  </div>
                </div>

                <div class="border-t-2 border-gray-300 pt-3 flex justify-between text-xl">
                  <span class="font-bold">Total</span>
                  <span class="font-bold" style="color: #92b08b">
                    {{ calculateTotal() }} DH
                  </span>
                </div>
              </div>

              <div
                class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800 flex items-center gap-2"
              >
                <CheckCircle :size="16" />
                <span
                  >Paiement effectué le {{ formatDate(demand.invoice.paymentDate) }}</span
                >
              </div>
            </div>
          </div>

          <!-- My Rating -->
          <div
            v-if="demand.status === 'completed' && demand.rating"
            class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6"
          >
            <h3 class="font-bold text-xl mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <Star :size="24" fill="#FEE347" color="#FEE347" />
              Mon évaluation
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
              <div
                v-for="(score, criteria) in demand.rating.criteriaRatings"
                :key="criteria"
                class="bg-white rounded-lg p-3 text-center border-2"
                style="border-color: #fee347"
              >
                <div class="flex items-center justify-center gap-1 mb-1">
                  <Star :size="14" fill="#FEE347" color="#FEE347" />
                  <span class="font-bold">{{ score }}/5</span>
                </div>
                <p class="text-xs text-gray-600 capitalize">{{ criteria }}</p>
              </div>
            </div>

            <div class="bg-white rounded-lg p-4 border-2" style="border-color: #fee347">
              <div class="flex items-center justify-between mb-2">
                <span class="font-bold">Note globale</span>
                <div class="flex items-center gap-2">
                  <Star :size="20" fill="#FEE347" color="#FEE347" />
                  <span class="text-2xl font-bold" style="color: #2f4f4f">
                    {{ demand.rating.overallRating }}/5
                  </span>
                </div>
              </div>
              <div v-if="demand.rating.comment" class="mt-3 pt-3 border-t border-gray-200">
                <p class="text-sm text-gray-600 italic">"{{ demand.rating.comment }}"</p>
              </div>
            </div>
          </div>

          <!-- Intervenant's Ratings (if completed and not yet rated) -->
          <div
            v-if="
              demand.status === 'completed' &&
              !demand.rating &&
              demand.intervenant.averageRating
            "
            class="bg-gray-50 rounded-xl p-6"
          >
            <h3 class="font-bold text-xl mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <Star :size="24" fill="#FEE347" color="#FEE347" />
              Évaluations de {{ demand.intervenant.name }}
            </h3>
            <div class="flex items-center gap-4 mb-4">
              <div class="text-center">
                <div class="text-5xl font-bold" style="color: #fee347">
                  {{ demand.intervenant.averageRating }}
                </div>
                <div class="flex items-center justify-center gap-1 mt-1">
                  <Star
                    v-for="star in 5"
                    :key="star"
                    :size="16"
                    :fill="star <= demand.intervenant.averageRating ? '#FEE347' : 'none'"
                    color="#FEE347"
                  />
                </div>
                <p class="text-sm text-gray-600 mt-1">
                  {{ demand.intervenant.totalReviews }} avis
                </p>
              </div>
              <div class="flex-1 space-y-2">
                <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center gap-2">
                  <span class="text-sm w-12">{{ rating }} ★</span>
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full"
                      :style="{
                        width: `${getPercentage(rating)}%`,
                        backgroundColor: '#FEE347'
                      }"
                    />
                  </div>
                  <span class="text-sm text-gray-600 w-12 text-right">
                    {{ Math.round(getPercentage(rating)) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Action: Rate if completed and not rated -->
          <div
            v-if="demand.status === 'completed' && !demand.rating"
            class="bg-green-50 border-2 border-green-300 rounded-xl p-6 text-center"
          >
            <AlertCircle :size="48" class="mx-auto mb-3" style="color: #92b08b" />
            <h3 class="font-bold text-xl mb-2" style="color: #2f4f4f">Service terminé !</h3>
            <p class="text-gray-600 mb-4">
              Partagez votre expérience avec {{ demand.intervenant.name }}
            </p>
            <button
              @click="openRatingModal"
              class="px-8 py-3 rounded-xl text-white font-bold hover:shadow-lg transition-all flex items-center gap-2 mx-auto"
              style="background-color: #92b08b"
            >
              <Star :size="20" />
              Évaluer maintenant
            </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-6 rounded-b-2xl">
          <button
            @click="$emit('close')"
            class="w-full px-6 py-3 rounded-xl text-white font-bold hover:shadow-lg transition-all"
            style="background-color: #1a5fa3"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>

    <!-- Photo Lightbox Modal -->
    <div
      v-if="selectedPhoto"
      class="fixed inset-0 z-[60] bg-black bg-opacity-90 flex items-center justify-center p-4"
      @click="selectedPhoto = null"
    >
      <button
        @click="selectedPhoto = null"
        class="absolute top-4 right-4 p-3 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full transition-all text-white"
      >
        <X :size="28" />
      </button>
      <img
        :src="selectedPhoto"
        alt="Photo agrandie"
        class="max-w-full max-h-[90vh] object-contain rounded-lg"
        @click.stop
      />
    </div>
  </div>
</template>

<script>
import {
  X,
  Calendar,
  Clock,
  MapPin,
  User,
  FileText,
  Star,
  DollarSign,
  CheckCircle,
  Package,
  AlertCircle,
  Download,
  Camera,
  ZoomIn
} from 'lucide-vue-next';

export default {
  name: 'DemandDetailsModal',
  components: {
    X,
    Calendar,
    Clock,
    MapPin,
    User,
    FileText,
    Star,
    DollarSign,
    CheckCircle,
    Package,
    AlertCircle,
    Download,
    Camera,
    ZoomIn
  },
  props: {
    demand: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'open-rating'],
  data() {
    return {
      selectedPhoto: null
    };
  },
  methods: {
    getStatusColor(status) {
      const colors = {
        pending: '#FEE347',
        accepted: '#1A5FA3',
        'in-progress': '#92B08B',
        completed: '#92B08B',
        cancelled: '#6B7280',
        refused: '#E8793F'
      };
      return colors[status] || '#6B7280';
    },
    getStatusLabel(status) {
      const labels = {
        pending: 'En attente',
        accepted: 'Acceptée',
        'in-progress': 'En cours',
        completed: 'Complétée',
        cancelled: 'Annulée',
        refused: 'Refusée'
      };
      return labels[status] || status;
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('fr-FR');
    },
    formatLongDate(dateString) {
      return new Date(dateString).toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    calculateTotal() {
      if (!this.demand.invoice) return 0;
      return this.demand.invoice.laborCost + this.demand.invoice.materialsCost;
    },
    getPercentage(rating) {
      if (!this.demand.intervenant.ratingDistribution) return 0;
      const count = this.demand.intervenant.ratingDistribution[rating] || 0;
      return (count / this.demand.intervenant.totalReviews) * 100;
    },
    openRatingModal() {
      // Emit event to parent to open rating modal
      this.$emit('open-rating');
    }
  }
};
</script>