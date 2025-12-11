<template>
  <img 
    :src="imageSrc" 
    :alt="alt" 
    @error="handleError"
    class="object-cover"
  />
</template>

<script>
export default {
  name: 'ImageWithFallback',
  props: {
    src: {
      type: String,
      default: ''
    },
    alt: {
      type: String,
      default: 'Image'
    },
    fallback: {
      type: String,
      default: 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400&h=300&fit=crop'
    }
  },
  data() {
    return {
      imageSrc: this.src || this.fallback
    }
  },
  watch: {
    src(newVal) {
      this.imageSrc = newVal || this.fallback;
    }
  },
  methods: {
    handleError() {
      this.imageSrc = this.fallback;
    }
  }
}
</script>
