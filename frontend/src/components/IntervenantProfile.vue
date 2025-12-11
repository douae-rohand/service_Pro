<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm sticky top-0 z-40">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <button
          @click="$emit('back')"
          class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-all"
        >
          <ArrowLeftIcon :size="20" />
          <span>Retour</span>
        </button>
      </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Profile Header Card -->
      <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
        <div class="flex items-start gap-6">
          <!-- Profile Image -->
          <div class="flex-shrink-0 relative">
            <ImageWithFallback
              :src="intervenant.profileImage"
              :alt="intervenant.name"
              class="w-32 h-32 rounded-full object-cover"
            />
            <div
              v-if="intervenant.verified"
              class="absolute bottom-1 right-1 w-8 h-8 rounded-full flex items-center justify-center shadow-md"
              :style="{ backgroundColor: buttonColor }"
            >
              <CheckCircleIcon :size="20" class="text-white" />
            </div>
          </div>

          <!-- Profile Info -->
          <div class="flex-1">
            <h1 class="text-4xl mb-2">{{ intervenant.name }}</h1>
            <div class="flex items-center gap-3 text-gray-600 mb-3">
              <div class="flex items-center gap-1">
                <MapPinIcon :size="16" />
                <span>{{ intervenant.location }}</span>
              </div>
              <span>•</span>
              <span>{{ intervenant.experience }}</span>
            </div>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mb-4">
              <StarIcon
                v-for="star in 5"
                :key="star"
                :size="20"
                class="fill-yellow-400 text-yellow-400"
              />
              <span class="text-lg ml-1">{{ intervenant.rating }}</span>
              <span class="text-gray-600">({{ intervenant.reviewCount }} avis)</span>
            </div>
          </div>

          <!-- Favorite Button -->
          <button
            @click="isFavorite = !isFavorite"
            class="px-5 py-2.5 rounded-lg border-2 transition-all flex items-center gap-2"
            :style="{ borderColor: primaryColor, color: primaryColor }"
          >
            <HeartIcon :size="18" :class="{ 'fill-current': isFavorite }" />
            Ajouter aux favoris
          </button>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="bg-white rounded-2xl shadow-sm mb-6">
        <div class="border-b border-gray-200">
          <div class="flex gap-2 px-4">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'px-6 py-4 transition-all relative',
                activeTab !== tab.id ? 'text-gray-600 hover:text-gray-900' : ''
              ]"
              :style="activeTab === tab.id ? { color: primaryColor } : {}"
            >
              {{ tab.label }}
              <div
                v-if="activeTab === tab.id"
                class="absolute bottom-0 left-0 right-0 h-0.5"
                :style="{ backgroundColor: primaryColor }"
              />
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="p-8">
          <!-- À propos Tab -->
          <div v-if="activeTab === 'apropos'">
            <h2 class="text-2xl mb-6">À propos de {{ intervenant.name.split(' ')[0] }}</h2>
            <div class="space-y-4 text-gray-700 leading-relaxed">
              <p v-for="(paragraph, index) in intervenant.bio" :key="index">
                {{ paragraph }}
              </p>
            </div>
          </div>

          <!-- Services Tab -->
          <div v-if="activeTab === 'services'">
            <h2 class="text-2xl mb-6">Services proposés</h2>
            <div class="space-y-6">
              <div
                v-for="serviceItem in intervenant.services"
                :key="serviceItem.id"
                class="border border-gray-200 rounded-xl p-6"
              >
                <h3 class="text-xl mb-3">{{ serviceItem.name }}</h3>
                <p class="text-gray-600 mb-4 leading-relaxed">
                  {{ serviceItem.description }}
                </p>
                <div class="flex items-center gap-3 mb-4">
                  <div class="flex items-center gap-2 text-gray-600">
                    <ClockIcon :size="18" :style="{ color: primaryColor }" />
                    <span>{{ serviceItem.duration }}</span>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <div class="text-2xl" :style="{ color: buttonColor }">
                    {{ serviceItem.price }}DH/h
                  </div>
                  <button
                    class="px-8 py-3 rounded-lg text-white transition-all hover:opacity-90"
                    :style="{ backgroundColor: buttonColor }"
                  >
                    Réserver ce service
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Avis Tab -->
          <div v-if="activeTab === 'avis'">
            <h2 class="text-2xl mb-6">Avis & Évaluations</h2>
            
            <!-- Rating Summary -->
            <div class="flex flex-col md:flex-row gap-8 mb-8 pb-8 border-b border-gray-200">
              <div class="text-center">
                <div class="text-6xl mb-2">{{ intervenant.rating }}</div>
                <div class="flex items-center justify-center gap-1 mb-2">
                  <StarIcon
                    v-for="star in 5"
                    :key="star"
                    :size="20"
                    class="fill-yellow-400 text-yellow-400"
                  />
                </div>
                <p class="text-gray-600">{{ intervenant.reviewCount }} avis</p>
              </div>

              <!-- Rating Distribution -->
              <div class="flex-1">
                <div
                  v-for="item in ratingDistribution"
                  :key="item.stars"
                  class="flex items-center gap-3 mb-2"
                >
                  <span class="text-sm w-12">{{ item.stars }} ★</span>
                  <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all"
                      :style="{ 
                        width: item.percentage + '%', 
                        backgroundColor: primaryColor 
                      }"
                    />
                  </div>
                  <span class="text-sm text-gray-600 w-16">{{ item.percentage }}%</span>
                </div>
              </div>
            </div>

            <!-- Reviews List -->
            <div class="space-y-6">
              <div
                v-for="review in intervenant.reviews"
                :key="review.id"
                class="border-b border-gray-100 pb-6 last:border-0"
              >
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h4 class="text-lg mb-1">{{ review.clientName }}</h4>
                    <p class="text-sm text-gray-500">
                      {{ formatDate(review.date) }}
                    </p>
                  </div>
                  <div class="flex items-center gap-1">
                    <StarIcon
                      v-for="star in 5"
                      :key="star"
                      :size="16"
                      :class="star <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                    />
                  </div>
                </div>
                <p class="text-gray-700 leading-relaxed">{{ review.comment }}</p>
              </div>
            </div>
          </div>

          <!-- Photos Tab -->
          <div v-if="activeTab === 'photos'">
            <h2 class="text-2xl mb-6">Photos des réalisations</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div
                v-for="(photo, index) in intervenant.photos"
                :key="index"
                class="relative aspect-video rounded-xl overflow-hidden cursor-pointer group"
                @click="selectedImage = photo"
              >
                <ImageWithFallback
                  :src="photo"
                  :alt="`Réalisation ${index + 1}`"
                  class="w-full h-full object-cover transition-transform group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                  <CameraIcon class="text-white opacity-0 group-hover:opacity-100 transition-opacity" :size="32" />
                </div>
              </div>
            </div>
          </div>

          <!-- Disponibilités Tab -->
          <div v-if="activeTab === 'disponibilites'">
            <h2 class="text-2xl mb-6">Disponibilités</h2>
            <div class="space-y-3 mb-8">
              <div
                v-for="(day, index) in intervenant.availability"
                :key="index"
                class="flex items-center justify-between py-4 border-b border-gray-200 last:border-0"
              >
                <span
                  class="text-lg"
                  :style="{ color: day.available ? primaryColor : '#9CA3AF' }"
                >
                  {{ day.day }}
                </span>
                <span
                  class="text-lg"
                  :style="{ color: day.available ? primaryColor : '#9CA3AF' }"
                >
                  {{ day.available ? day.hours : 'Non disponible' }}
                </span>
              </div>
            </div>
            <button
              class="w-full px-8 py-4 rounded-lg text-white text-lg transition-all hover:opacity-90"
              :style="{ backgroundColor: buttonColor }"
            >
              Demander une disponibilité spécifique
            </button>
          </div>
        </div>
      </div>

      <!-- Bottom CTA -->
      <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
        <h3 class="text-2xl mb-4">Réserver maintenant</h3>
        <p class="text-gray-600 mb-6">
          Contactez {{ intervenant.name.split(' ')[0] }} pour discuter de vos besoins et planifier une intervention
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button
            class="px-10 py-4 rounded-lg text-white text-lg transition-all hover:opacity-90"
            :style="{ backgroundColor: primaryColor }"
          >
            Réserver
          </button>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div
      v-if="selectedImage"
      class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4"
      @click="selectedImage = null"
    >
      <button
        class="absolute top-4 right-4 p-2 bg-white rounded-full hover:bg-gray-100 transition-all"
        @click="selectedImage = null"
      >
        <XIcon :size="24" />
      </button>
      <img
        :src="selectedImage"
        alt="Réalisation"
        class="max-w-full max-h-full object-contain rounded-lg"
      />
    </div>
  </div>
</template>

<script>
import { 
  ArrowLeft as ArrowLeftIcon, 
  Star as StarIcon, 
  MapPin as MapPinIcon, 
  Heart as HeartIcon, 
  CheckCircle as CheckCircleIcon, 
  Clock as ClockIcon,
  Camera as CameraIcon, 
  X as XIcon 
} from 'lucide-vue-next';
import ImageWithFallback from './figma/ImageWithFallback.vue';

export default {
  name: 'IntervenantProfilePage',
  components: {
    ArrowLeftIcon,
    StarIcon,
    MapPinIcon,
    HeartIcon,
    CheckCircleIcon,
    ClockIcon,
    CameraIcon,
    XIcon,
    ImageWithFallback
  },
  props: {
    intervenantId: {
      type: Number,
      required: true
    },
    service: {
      type: String,
      required: true,
      validator: (value) => ['jardinage', 'menage'].includes(value)
    }
  },
  emits: ['back'],
  data() {
    return {
      selectedImage: null,
      activeTab: 'apropos',
      isFavorite: false,
      tabs: [
        { id: 'apropos', label: 'À propos' },
        { id: 'services', label: 'Taches' },
        { id: 'avis', label: 'Avis' },
        { id: 'photos', label: 'Photos' },
        { id: 'disponibilites', label: 'Disponibilités' }
      ],
      ratingDistribution: [
        { stars: 5, percentage: 92, count: 82 },
        { stars: 4, percentage: 6, count: 5 },
        { stars: 3, percentage: 1, count: 1 },
        { stars: 2, percentage: 1, count: 1 },
        { stars: 1, percentage: 0, count: 0 }
      ]
    };
  },
  computed: {
    primaryColor() {
      return this.service === 'jardinage' ? '#92B08B' : '#4682B4';
    },
    buttonColor() {
      return '#609B41';
    },
    intervenant() {
      // Mock data - dans une vraie app, cela viendrait d'une API
      return {
        id: this.intervenantId,
        name: 'Amina Chakir',
        profileImage: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400&h=400&fit=crop',
        rating: 5.0,
        reviewCount: 89,
        location: 'Tetouan Centre',
        experience: '8 ans d\'expérience',
        verified: true,
        expert: true,
        memberSince: 'Membre depuis 2019',
        responseTime: '~2h',
        completedJobs: 456,
        bio: [
          'Bonjour ! Je m\'appelle Amina et je suis professionnelle du ménage depuis 8 ans. Passionnée par mon métier, j\'accorde une attention particulière aux détails pour vous offrir un intérieur impeccable.',
          'Mon expérience m\'a permis de développer des méthodes efficaces et respectueuses de l\'environnement. J\'apporte toujours mon propre matériel professionnel et j\'utilise des produits écologiques sur demande.',
          'Ponctuelle, discrète et méticuleuse, je m\'adapte à vos besoins spécifiques pour que vous retrouviez un foyer parfaitement entretenu.'
        ],
        services: this.getServices(),
        reviews: this.getReviews(),
        photos: [
          'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400&h=300&fit=crop',
          'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=400&h=300&fit=crop',
          'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&h=300&fit=crop',
          'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?w=400&h=300&fit=crop',
          'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?w=400&h=300&fit=crop',
          'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=400&h=300&fit=crop'
        ],
        availability: [
          { day: 'Lundi', available: true, hours: '9h00 - 17h00' },
          { day: 'Mardi', available: true, hours: '9h00 - 17h00' },
          { day: 'Mercredi', available: true, hours: '9h00 - 17h00' },
          { day: 'Jeudi', available: true, hours: '9h00 - 17h00' },
          { day: 'Vendredi', available: true, hours: '9h00 - 17h00' },
          { day: 'Samedi', available: true, hours: '9h00 - 17h00' },
          { day: 'Dimanche', available: false, hours: '' }
        ]
      };
    }
  },
  methods: {
    getServices() {
      if (this.service === 'menage') {
        return [
          {
            id: 1,
            name: 'Ménage résidentiel & régulier',
            description: 'Nettoyage complet de votre domicile : dépoussiérage complet, entretien régulier, sols, cuisine, salle de bain.',
            duration: '2-3 heures recommandées',
            price: 20
          },
          {
            id: 2,
            name: 'Nettoyage en profondeur (Deep Cleaning)',
            description: 'Désinfection totale, nettoyage sous/derrière meubles, détartrage, plinthes, lavage des murs.',
            duration: '4-6 heures recommandées',
            price: 24
          },
          {
            id: 3,
            name: 'Nettoyage spécial : déménagement & post-travaux',
            description: 'Avant/après déménagement, après rénovation, élimination poussières fines et résidus.',
            duration: '3-5 heures recommandées',
            price: 25
          },
          {
            id: 4,
            name: 'Lavage vitres & surfaces spécialisées',
            description: 'Vitres intérieur/extérieur, marbre, bois nobles, soins textiles, moquettes & tapis.',
            duration: '2-3 heures recommandées',
            price: 22
          },
          {
            id: 5,
            name: 'Nettoyage mobilier & textiles',
            description: 'Shampooing canapé, rénovation tissus/cuir, fauteuils, matelas, blanchisserie & repassage.',
            duration: '2-4 heures recommandées',
            price: 23
          },
          {
            id: 6,
            name: 'Nettoyage professionnel (bureaux & commerces)',
            description: 'Entretien quotidien bureaux, désinfection postes de travail, vidage poubelles, salles de réunion.',
            duration: '3-4 heures recommandées',
            price: 21
          }
        ];
      } else {
        return [
          {
            id: 1,
            name: 'Tonte de pelouse',
            description: 'Entretien régulier de votre gazon avec équipement professionnel pour un résultat impeccable.',
            duration: '1-2 heures recommandées',
            price: 25
          },
          {
            id: 2,
            name: 'Taille de haies',
            description: 'Façonnage et entretien de vos haies pour un jardin bien structuré et esthétique.',
            duration: '2-3 heures recommandées',
            price: 28
          },
          {
            id: 3,
            name: 'Plantation de fleurs',
            description: 'Création et aménagement de massifs floraux selon vos goûts et la saison.',
            duration: '2-4 heures recommandées',
            price: 26
          },
          {
            id: 4,
            name: 'Élagage d\'arbres',
            description: 'Taille et soin professionnel de vos arbres pour leur santé et votre sécurité.',
            duration: '3-5 heures recommandées',
            price: 30
          },
          {
            id: 5,
            name: 'Désherbage',
            description: 'Élimination soignée des mauvaises herbes pour un jardin propre et sain.',
            duration: '1-3 heures recommandées',
            price: 24
          },
          {
            id: 6,
            name: 'Entretien de potager',
            description: 'Soin et maintenance complète de votre potager : arrosage, fertilisation, récolte.',
            duration: '2-3 heures recommandées',
            price: 27
          }
        ];
      }
    },
    getReviews() {
      return [
        {
          id: 1,
          clientName: 'Salma K.',
          date: '2024-11-20',
          rating: 5,
          comment: 'Excellente prestation ! Amina est très professionnelle, ponctuelle et méticuleuse. Mon appartement n\'a jamais été aussi propre. Je la recommande vivement.'
        },
        {
          id: 2,
          clientName: 'Omar M.',
          date: '2024-11-12',
          rating: 5,
          comment: 'Travail impeccable. Amina utilise des produits écologiques qui sentent bon. Très satisfait du résultat.'
        },
        {
          id: 3,
          clientName: 'Zineb R.',
          date: '2024-10-28',
          rating: 5,
          comment: 'Très bon service. Amina est agréable et fait du bon travail. Une personne de confiance.'
        },
        {
          id: 4,
          clientName: 'Youssef L.',
          date: '2024-10-15',
          rating: 5,
          comment: 'Je fais appel à Amina depuis plusieurs mois maintenant. Toujours aussi professionnelle et soigneuse. Un vrai plaisir de rentrer chez soi !'
        },
        {
          id: 5,
          clientName: 'Meryem B.',
          date: '2024-09-30',
          rating: 5,
          comment: 'Amina a fait un grand nettoyage après mes travaux. Le résultat est bluffant ! Elle a même nettoyé des endroits auxquels je n\'aurais pas pensé.'
        }
      ];
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    }
  }
};
</script>

<style scoped>
/* Styles spécifiques si nécessaire */
</style>