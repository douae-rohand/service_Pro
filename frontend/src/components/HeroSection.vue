<template>
  <section
    class="relative py-20 px-4 sm:px-6 lg:px-8 overflow-hidden"
    style="background: linear-gradient(to bottom, #E8F5E9, #FFFFFF)"
  >
    <!-- Decorative shapes -->
    <div
      class="absolute top-10 left-10 w-32 h-32 rounded-full opacity-50 blur-3xl"
      style="background-color: #92B08B"
    ></div>
    <div class="absolute bottom-10 right-10 w-40 h-40 bg-yellow-200 rounded-full opacity-50 blur-3xl"></div>

    <div class="max-w-7xl mx-auto">
      <div class="grid md:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
          <div class="inline-block">
            <span class="text-sm uppercase tracking-wide" style="color: #4682B4">
              100% Satisfaction Garantie
            </span>
          </div>
          <h1 class="text-5xl md:text-6xl">
            Trouvez Votre Intervenant
            <span class="relative">
              Parfait
              <svg
                class="absolute -bottom-2 left-0 w-full"
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
          <p class="text-gray-600 text-lg">
            Connectez-vous avec les meilleurs professionnels pour vos tâches de jardinage et ménage.
            Simple, rapide et en toute confiance.
          </p>

          <!-- Search Bar -->
          <div class="flex gap-2 bg-white p-2 rounded-lg shadow-lg">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Quel service recherchez-vous ?"
              class="flex-1 px-4 py-3 outline-none"
              @keydown.enter="handleSearch"
            />
            <button
              @click="handleSearch"
              class="text-black px-8 py-3 rounded-lg transition-colors flex items-center gap-2"
              style="background-color: #F3E293"
              @mouseenter="handleButtonHover($event)"
              @mouseleave="handleButtonLeave($event)"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div className="relative">
            <img
              src="../assets/hero-image.png"
              alt="Nos intervenants professionnels"
              className="w-full h-auto rounded-2xl"
            />
          </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  onSearch: Function,
})

const searchQuery = ref('')

const handleSearch = () => {
  if (props.onSearch) {
    props.onSearch()
  }
}

const handleButtonHover = (e) => {
  e.currentTarget.style.backgroundColor = '#E5D280'
}

const handleButtonLeave = (e) => {
  e.currentTarget.style.backgroundColor = '#F3E293'
}
</script>

