<template>
  <section
    class="relative py-20 px-4 sm:px-6 lg:px-8 overflow-hidden"
    style="background: linear-gradient(to bottom, #E8F5E9, #FFFFFF)"
  >
    <!-- Decorative shapes with animation -->
    <div
      class="absolute top-10 left-10 w-32 h-32 rounded-full opacity-50 blur-3xl animate-float"
      style="background-color: #92B08B"
    ></div>
    <div class="absolute bottom-10 right-10 w-40 h-40 bg-yellow-200 rounded-full opacity-50 blur-3xl animate-float-delayed"></div>

    <div class="max-w-7xl mx-auto">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div class="space-y-6 animate-fade-in-up">
          <div class="inline-block animate-slide-in-left">
            <span class="text-sm uppercase tracking-wide font-semibold animate-text-shimmer" style="color: #4682B4">
              100% Satisfaction Garantie
            </span>
          </div>
          <h1 class="text-5xl md:text-6xl font-bold animate-fade-in-up-delayed">
            <span class="inline-block animate-slide-in-right">Trouvez Votre Intervenant</span>
            <span class="relative inline-block animate-slide-in-right-delayed">
              Parfait
              <svg
                class="absolute -bottom-2 left-0 w-full animate-draw-line"
                height="8"
                viewBox="0 0 200 8"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M1 5.5C50 2 150 2 199 5.5"
                  stroke="#FCD34D"
                  stroke-width="3"
                  stroke-linecap="round"
                />
              </svg>
            </span>
          </h1>
          <p class="text-gray-600 text-lg animate-fade-in-up-delayed-2 leading-relaxed">
            Connectez-vous avec les meilleurs professionnels pour vos tâches de jardinage et ménage.
            Simple, rapide et en toute confiance.
          </p>

          <!-- Search Bar -->
          <div class="flex gap-2 bg-white p-2 rounded-lg shadow-lg animate-fade-in-up-delayed-3 hover:shadow-xl transition-shadow duration-300">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Quel service recherchez-vous ?"
              class="flex-1 px-4 py-3 outline-none transition-all duration-300 focus:scale-105"
              @keydown.enter="handleSearch"
            />
            <button
              @click="handleSearch"
              class="text-black px-8 py-3 rounded-lg transition-all duration-300 flex items-center gap-2 hover:scale-105 hover:shadow-lg transform"
              style="background-color: #F3E293"
              @mouseenter="handleButtonHover($event)"
              @mouseleave="handleButtonLeave($event)"
            >
              <svg class="w-5 h-5 animate-pulse-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
              Rechercher
            </button>
          </div>
        </div>

        <!-- Hero Image Grid -->
        <div class="relative animate-fade-in-right perspective-1000">
          <div class="animate-float-continuous">
            <img
              src="../assets/hero-image.png"
              alt="Nos intervenants professionnels"
              class="w-full h-auto rounded-2xl shadow-2xl transform transition-all duration-700 hover:scale-105 hover:rotate-2 hover:shadow-cyan-500/50 animate-zoom-in"
            />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['search'])

const searchQuery = ref('')

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    emit('search', searchQuery.value)
  }
}

const handleButtonHover = (e) => {
  e.currentTarget.style.backgroundColor = '#E5D280'
}

const handleButtonLeave = (e) => {
  e.currentTarget.style.backgroundColor = '#F3E293'
}
</script>

<style scoped>
/* Animations pour le texte */
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

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeInRight {
  from {
    opacity: 0;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes zoomIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

@keyframes drawLine {
  from {
    stroke-dasharray: 200;
    stroke-dashoffset: 200;
  }
  to {
    stroke-dasharray: 200;
    stroke-dashoffset: 0;
  }
}

@keyframes textShimmer {
  0% {
    background-position: -200% center;
  }
  100% {
    background-position: 200% center;
  }
}

@keyframes pulseSlow {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out;
}

.animate-fade-in-up-delayed {
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.animate-fade-in-up-delayed-2 {
  animation: fadeInUp 0.8s ease-out 0.4s both;
}

.animate-fade-in-up-delayed-3 {
  animation: fadeInUp 0.8s ease-out 0.6s both;
}

.animate-slide-in-left {
  animation: slideInLeft 0.8s ease-out;
}

.animate-slide-in-right {
  animation: slideInRight 0.8s ease-out;
}

.animate-slide-in-right-delayed {
  animation: slideInRight 0.8s ease-out 0.3s both;
}

.animate-fade-in-right {
  animation: fadeInRight 1s ease-out 0.5s both;
}

.animate-zoom-in {
  animation: zoomIn 1s ease-out;
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float 6s ease-in-out infinite 1s;
}

.animate-draw-line {
  animation: drawLine 1.5s ease-out 1s both;
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
}

.animate-text-shimmer {
  background: linear-gradient(
    90deg,
    #4682B4 0%,
    #5a9fd4 50%,
    #4682B4 100%
  );
  background-size: 200% auto;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: textShimmer 3s linear infinite;
}

.animate-pulse-slow {
  animation: pulseSlow 2s ease-in-out infinite;
}

.animate-float-continuous {
  animation: floatContinuous 6s ease-in-out infinite;
  animation-delay: 1.5s; /* Start after entrance */
}

.perspective-1000 {
  perspective: 1000px;
}

@keyframes floatContinuous {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}
</style>

