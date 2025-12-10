<template>
  <section id="services" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-4xl mt-4 mb-4">Découvrez Nos Services Professionnels</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Nous vous mettons en relation avec des intervenants qualifiés et vérifiés pour tous vos besoins.
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-8">
        <div
          v-for="service in services"
          :key="service.id"
          class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all"
        >
          <div class="flex justify-center items-center p-8 bg-gradient-to-br from-gray-50 to-white">
            <img :src="service.image" :alt="service.name" class="w-64 h-64 object-contain" />
          </div>

          <div class="p-8 text-center">
            <h3 class="text-3xl mb-4" :style="{ color: service.color }">{{ service.name }}</h3>
            <p class="text-gray-600 mb-6">{{ service.description }}</p>

            <button
              @click="handleServiceClick(service.id)"
              class="inline-flex items-center gap-2 text-white px-8 py-4 rounded-xl transition-all transform hover:scale-105 group"
              :style="{ backgroundColor: service.color }"
              @mouseenter="handleButtonHover($event)"
              @mouseleave="handleButtonLeave($event)"
            >
              Découvrir les sous-services
              <svg
                class="w-5 h-5 group-hover:translate-x-1 transition-transform"
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
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import jardinageImg from '@/assets/jardinage.png'
import menageImg from '@/assets/menage.png'

// Définir l'événement émis
const emit = defineEmits(['service-click'])

const services = [
  {
    id: 'jardinage',
    name: 'Jardinage',
    image: jardinageImg,
    description: 'Des professionnels pour entretenir votre jardin et vos espaces verts.',
    color: '#92B08B',
  },
  {
    id: 'menage',
    name: 'Ménage',
    image: menageImg,
    description: 'Des experts en nettoyage pour un intérieur impeccable.',
    color: '#4682B4',
  },
]

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