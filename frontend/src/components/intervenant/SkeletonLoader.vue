<template>
  <div class="skeleton-container" :class="{ 'contents-layout': layout === 'grid-wrapper' }">
    <div v-for="n in count" :key="n" :class="['skeleton-item', `skeleton-${type}`]">
      <!-- Stats Skeleton -->
      <template v-if="type === 'stat'">
        <div class="skeleton-stat-content">
          <div class="skeleton-line skeleton-short"></div>
          <div class="skeleton-line skeleton-large"></div>
        </div>
      </template>

      <!-- Service Card Skeleton -->
      <template v-else-if="type === 'service'">
        <div class="skeleton-header">
          <div class="skeleton-line skeleton-medium"></div>
          <div class="skeleton-toggle"></div>
        </div>
        <div class="skeleton-line skeleton-short mt-4"></div>
      </template>

      <!-- Reservation Card Skeleton -->
      <template v-else-if="type === 'reservation'">
        <div class="skeleton-row">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-content">
            <div class="skeleton-line skeleton-medium"></div>
            <div class="skeleton-line skeleton-short"></div>
          </div>
        </div>
        <div class="skeleton-actions mt-4">
          <div class="skeleton-button"></div>
          <div class="skeleton-button"></div>
        </div>
      </template>

      <!-- Availability Row Skeleton -->
      <template v-else-if="type === 'availability'">
        <div class="skeleton-row-center">
          <div class="skeleton-toggle"></div>
          <div class="skeleton-line skeleton-medium"></div>
          <div class="skeleton-input ml-auto"></div>
          <div class="skeleton-input"></div>
        </div>
      </template>

      <!-- Profile Skeleton -->
      <template v-else-if="type === 'profile'">
        <div class="skeleton-profile">
          <div class="skeleton-profile-header">
            <div class="skeleton-avatar-large"></div>
            <div class="skeleton-content">
              <div class="skeleton-line skeleton-medium"></div>
              <div class="skeleton-line skeleton-short"></div>
            </div>
          </div>
          <div class="skeleton-grid-2">
            <div class="skeleton-input-large"></div>
            <div class="skeleton-input-large"></div>
            <div class="skeleton-input-large"></div>
            <div class="skeleton-input-large"></div>
          </div>
        </div>
      </template>

      <!-- Review Skeleton -->
      <template v-else-if="type === 'review'">
        <div class="skeleton-review">
          <div class="skeleton-header">
            <div class="skeleton-avatar-small"></div>
            <div class="skeleton-content">
              <div class="skeleton-line skeleton-short"></div>
              <div class="skeleton-stars"></div>
            </div>
          </div>
          <div class="skeleton-body">
            <div class="skeleton-line"></div>
            <div class="skeleton-line skeleton-long"></div>
          </div>
        </div>
      </template>

      <!-- Distribution Skeleton (Chart) -->
      <template v-else-if="type === 'distribution'">
        <div class="skeleton-distribution">
          <div v-for="n in 5" :key="n" class="skeleton-row-center mb-3">
            <div class="skeleton-line skeleton-tiny"></div>
            <div class="skeleton-bar-container">
              <div class="skeleton-bar" :style="{ width: (Math.random() * 60 + 20) + '%' }"></div>
            </div>
            <div class="skeleton-line skeleton-tiny"></div>
          </div>
        </div>
      </template>

      <!-- My Service Skeleton (Specific for MyServicesTab) -->
      <template v-else-if="type === 'my-service'">
        <div class="skeleton-my-service">
          <div class="skeleton-header-row items-start">
            <div class="flex-1">
              <div class="skeleton-title-row">
                <div class="skeleton-line skeleton-medium"></div>
                <div class="skeleton-badge-small ml-3"></div>
              </div>
              <div class="skeleton-line skeleton-long mt-3"></div>
              <div class="skeleton-line skeleton-short mt-2"></div>
            </div>
            <div class="skeleton-icon-btn ml-4"></div>
          </div>
          
          <div class="skeleton-rate-badge mt-4"></div>
          
          <div class="skeleton-materials-section mt-4">
            <div class="skeleton-line skeleton-tiny mb-2"></div>
            <div class="skeleton-materials-row">
              <div class="skeleton-pill"></div>
              <div class="skeleton-pill"></div>
              <div class="skeleton-pill"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Selection Skeleton (Service Selection Card) -->
      <template v-else-if="type === 'selection'">
        <div class="skeleton-selection-card">
          <div class="skeleton-header-row">
            <div class="skeleton-line skeleton-medium"></div>
            <div class="skeleton-toggle ml-auto"></div>
          </div>
        </div>
      </template>

      <!-- Default Card Skeleton -->
      <template v-else>
        <div class="skeleton-header">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-content">
            <div class="skeleton-line skeleton-short"></div>
            <div class="skeleton-line skeleton-long"></div>
          </div>
        </div>
        <div class="skeleton-body">
          <div class="skeleton-line"></div>
          <div class="skeleton-line"></div>
          <div class="skeleton-line skeleton-medium"></div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
defineProps({
  count: {
    type: Number,
    default: 1
  },
  type: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'stat', 'service', 'reservation', 'availability', 'profile', 'review', 'distribution', 'my-service', 'selection'].includes(value)
  },
  layout: {
    type: String,
    default: 'column', // 'column' | 'grid-wrapper' (display: contents)
    validator: (value) => ['column', 'grid-wrapper'].includes(value)
  }
})
</script>

<style scoped>
.skeleton-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
}

.skeleton-item {
  background: #fff;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  position: relative;
  overflow: hidden;
}

/* Variant Specific Styles */
.skeleton-stat {
  padding: 1rem;
}

.skeleton-service {
  padding: 1.25rem;
}

.skeleton-reservation {
  padding: 1.5rem;
}

.skeleton-availability {
  padding: 1rem;
  border: 3px solid #e5e7eb; /* Match day item border */
}

.skeleton-profile {
  padding: 0;
}

.skeleton-review {
  padding: 0;
}

.skeleton-distribution {
  padding: 0;
}

.skeleton-bar-container {
  flex: 1;
  height: 8px;
  background: #f3f4f6;
  border-radius: 9999px;
  margin: 0 12px;
  overflow: hidden;
}

.skeleton-bar {
  height: 100%;
  background: #e5e7eb;
  border-radius: 9999px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-tiny {
  width: 30px;
}

.skeleton-my-service {
  padding: 1rem; /* spacing-4 */
  border: 1px solid #e5e7eb; /* gray-200 */
  border-radius: 0.5rem; /* radius-lg */
  background: white;
}

.skeleton-title-row {
  display: flex;
  align-items: center;
}

.skeleton-badge-small {
  width: 50px;
  height: 20px;
  border-radius: 9999px;
  background: #e5e7eb;
}

.skeleton-icon-btn {
  width: 36px; /* 2.25rem closely */
  height: 36px;
  border-radius: 0.375rem; /* radius-md */
  background: #f3f4f6;
}

.skeleton-rate-badge {
  width: 140px;
  height: 48px; /* close to actual badge */
  border-radius: 0.5rem;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}

.skeleton-pill {
  width: 80px;
  height: 28px;
  border-radius: 9999px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}

.skeleton-selection-card {
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem; /* radius-xl equivalent */
  background: white;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
}

/* Elements */
.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #e5e7eb;
}

.skeleton-avatar-large {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  background: #e5e7eb;
}

.skeleton-avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e5e7eb;
}

.skeleton-profile-header {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 2rem;
}

.skeleton-grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.skeleton-input-large {
  height: 48px;
  border-radius: 8px;
  background: #e5e7eb;
}

.skeleton-stars {
  width: 100px;
  height: 16px;
  border-radius: 4px;
  background: #e5e7eb;
}

.skeleton-line {
  height: 12px;
  border-radius: 6px;
  background: #e5e7eb;
  margin-bottom: 8px;
}

.skeleton-short { width: 40%; }
.skeleton-medium { width: 60%; }
.skeleton-long { width: 80%; }
.skeleton-large { 
  height: 24px; 
  width: 30%; 
  margin-top: 8px;
}

.skeleton-toggle {
  width: 48px;
  height: 24px;
  border-radius: 12px;
  background: #e5e7eb;
}

.skeleton-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.skeleton-row {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.skeleton-row-center {
  display: flex;
  gap: 1rem;
  align-items: center;
  width: 100%;
}

.skeleton-content {
  flex: 1;
}

.skeleton-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.skeleton-button {
  width: 100px;
  height: 36px;
  border-radius: 8px;
  background: #e5e7eb;
}

.skeleton-input {
  width: 80px;
  height: 36px;
  border-radius: 8px;
  background: #e5e7eb;
}

/* Utilities */
.mt-4 { margin-top: 1rem; }
.ml-auto { margin-left: auto; }

/* Animation */
.skeleton-item::after {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  transform: translateX(-100%);
  background-image: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0,
    rgba(255, 255, 255, 0.4) 20%,
    rgba(255, 255, 255, 0.7) 60%,
    rgba(255, 255, 255, 0)
  );
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
</style>
