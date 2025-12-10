<template>
  <section class="py-20 relative overflow-hidden" style="background-color: #F3E293">
    <!-- Decorative shapes -->
    <div
      class="absolute top-0 left-0 w-64 h-64 rounded-full opacity-20 blur-3xl"
      style="background-color: #A5D4E6"
    ></div>
    <div
      class="absolute bottom-0 right-0 w-64 h-64 rounded-full opacity-20 blur-3xl"
      style="background-color: #92B08B"
    ></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <div class="text-center mb-16">
        <h2 class="text-4xl mt-4 mb-4">Ce Que Nos Clients Disent de Nous</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Découvrez les avis de nos clients satisfaits sur la qualité de nos intervenants.
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <div
          v-for="(testimonial, index) in testimonials"
          :key="index"
          class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow"
        >
          <div class="flex items-center gap-4 mb-6">
            <div
              class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-2xl"
            >
              {{ testimonial.initial }}
            </div>
            <div>
              <h4>{{ testimonial.name }}</h4>
              <p class="text-sm text-gray-500">{{ testimonial.role }}</p>
            </div>
          </div>

          <div class="flex gap-1 mb-4">
            <svg
              v-for="i in testimonial.rating"
              :key="i"
              class="w-5 h-5 fill-yellow-400 text-yellow-400"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
              />
            </svg>
          </div>

          <p class="text-gray-600 leading-relaxed">{{ testimonial.text }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const testimonials = [
  {
    name: 'Meryem Benali',
    role: 'Cliente depuis 2 ans',
    initial: 'M',
    rating: 5,
    text: "Service exceptionnel ! J'ai trouvé un jardinier formidable en quelques minutes. Mon jardin n'a jamais été aussi beau. Je recommande vivement cette plateforme.",
  },
  {
    name: 'Karim Alaoui',
    role: 'Client satisfait',
    initial: 'K',
    rating: 5,
    text: "Excellente expérience ! Les intervenants sont professionnels et ponctuels. Le système de réservation est très simple. Parfait pour quelqu'un qui travaille beaucoup.",
  },
  {
    name: 'Samira Idrissi',
    role: 'Cliente régulière',
    initial: 'S',
    rating: 5,
    text: "Je fais appel à leurs services de ménage toutes les semaines. C'est toujours impeccable et les tarifs sont très corrects. Une vraie aide au quotidien !",
  },
]
</script>

