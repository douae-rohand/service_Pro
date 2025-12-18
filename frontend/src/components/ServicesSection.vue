<template>
  <section id="services" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold mt-4 mb-4">Découvrez Nos Services Professionnels</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Nous vous mettons en relation avec des intervenants qualifiés et vérifiés pour tous vos besoins.
        </p>
      </div>



      <!-- Error state -->
      <div v-if="error" class="text-center py-12">
        <p class="text-red-600 mb-4">{{ error }}</p>
        <button
          @click="loadServices"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Réessayer
        </button>
      </div>

      <!-- Services grid -->
      <div v-else class="grid md:grid-cols-2 gap-8">
        <div
          v-for="(service, index) in services"
          :key="service.id"
          class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 animate-fade-in-up-card"
          :style="{ animationDelay: `${index * 0.2}s` }"
        >
          <div class="flex justify-center items-center p-8 bg-gradient-to-br from-gray-50 to-white overflow-hidden">
            <img 
              :src="service.image" 
              :alt="service.name" 
              class="w-64 h-64 object-contain transform transition-transform duration-500 hover:scale-110 hover:rotate-3" 
            />
          </div>

          <div class="p-8 text-center">
            <h3 class="text-3xl mb-4 font-bold transform transition-all duration-300 hover:scale-105" :style="{ color: service.color }">
              {{ service.name }}
            </h3>
            <p class="text-gray-600 mb-6 leading-relaxed">{{ service.description }}</p>

            <button
              @click="handleServiceClick(service.id)"
              class="inline-flex items-center gap-2 text-white px-8 py-4 rounded-xl transition-all duration-300 transform hover:scale-110 hover:shadow-xl group relative overflow-hidden"
              :style="{ backgroundColor: service.color }"
              @mouseenter="handleButtonHover($event)"
              @mouseleave="handleButtonLeave($event)"
            >
              <span class="relative z-10">Découvrir les sous-services</span>
              <svg
                class="w-5 h-5 group-hover:translate-x-2 transition-transform duration-300 relative z-10"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
              <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import serviceService from '@/services/serviceService'
import jardinageImg from '@/assets/jardinage.png'
import menageImg from '@/assets/menage.png'

// Définir l'événement émis
const emit = defineEmits(['service-click'])

const services = ref([])
const loading = ref(true)
const error = ref(null)

// Mapping pour les images et couleurs par nom de service
const serviceConfig = {
  'Jardinage': {
    image: jardinageImg,
    color: '#92B08B',
  },
  'Ménage': {
    image: menageImg,
    color: '#4682B4',
  },
}

// Fonction pour obtenir la configuration d'un service
const getServiceConfig = (serviceName) => {
  return serviceConfig[serviceName] || {
    image: jardinageImg,
    color: '#6B7280',
  }
}

// Charger les services depuis l'API
const loadServices = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await serviceService.getAll()
    
    console.log('[ServicesSection] API Response:', response.data)
    
    // Handle new API response format: response.data.data instead of response.data.services
    const servicesData = response.data.data || response.data.services || response.data || []
    
    // Mapper les données pour inclure image et couleur
    services.value = servicesData.map(service => {
      const config = getServiceConfig(service.nom_service || service.name)
      return {
        id: service.id,
        name: service.nom_service || service.name,
        description: service.description || '',
        image: config.image,
        color: config.color,
      }
    })
    
    console.log('[ServicesSection] Loaded services:', services.value)
    
  } catch (err) {
    console.error('Erreur lors du chargement des services:', err)
    error.value = 'Impossible de charger les services. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadServices()
})

const handleServiceClick = (serviceId) => {
  emit('service-click', serviceId)
}

const handleButtonHover = (e) => {
  e.currentTarget.style.opacity = '0.9'
}

const handleButtonLeave = (e) => {
  e.currentTarget.style.opacity = '1'
}
</script>

<style scoped>
@keyframes fadeInUpCard {
  from {
    opacity: 0;
    transform: translateY(50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-fade-in-up-card {
  animation: fadeInUpCard 0.8s ease-out both;
}
</style>