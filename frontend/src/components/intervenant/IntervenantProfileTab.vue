<template>
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex items-start gap-6 mb-8">
      <img
        :src="profileImage"
        :alt="intervenant.name"
        class="w-24 h-24 rounded-full object-cover"
      />
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ intervenant.name }}</h2>
        <div class="flex items-center gap-2 text-gray-600 mb-2">
          <MapPinIcon :size="16" />
          <span>{{ intervenant.location }}</span>
        </div>
        <p class="text-gray-600">{{ intervenant.bio }}</p>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-gray-50 p-4 rounded-xl">
        <div class="text-sm text-gray-500 mb-1">Note moyenne</div>
        <div class="flex items-center gap-2">
          <StarIcon class="text-yellow-400 fill-current" :size="20" />
          <span class="text-xl font-bold">{{ intervenant.rating || 0 }}</span>
          <span class="text-sm text-gray-400">({{ intervenant.reviewCount || 0 }} avis)</span>
        </div>
      </div>
      <!-- Add more stats as needed -->
    </div>
  </div>
</template>

<script>
import { MapPin as MapPinIcon, Star as StarIcon } from 'lucide-vue-next';

export default {
  name: 'IntervenantProfileTab',
  components: {
    MapPinIcon,
    StarIcon
  },
  props: {
    intervenant: {
      type: Object,
      required: true
    }
  },
  computed: {
    profileImage() {
        return this.intervenant.profileImage || 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop';
    }
  }
};
</script>
