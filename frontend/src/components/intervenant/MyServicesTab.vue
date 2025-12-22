<template>
  <div class="container">
    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <template v-if="loading">
        <div class="stat-card skeleton-item border-l-4 border-green-500">
          <div class="skeleton-text" style="width: 8rem; height: 1rem; margin-bottom: 0.5rem; background-color: #E2E8F0;"></div>
          <div class="skeleton-text" style="width: 3rem; height: 2rem; background-color: #E2E8F0;"></div>
        </div>
        <div class="stat-card skeleton-item border-l-4 border-blue-500">
           <div class="skeleton-text" style="width: 8rem; height: 1rem; margin-bottom: 0.5rem; background-color: #E2E8F0;"></div>
           <div class="skeleton-text" style="width: 3rem; height: 2rem; background-color: #E2E8F0;"></div>
        </div>
        <div class="stat-card skeleton-item border-l-4 border-orange-500">
           <div class="skeleton-text" style="width: 8rem; height: 1rem; margin-bottom: 0.5rem; background-color: #E2E8F0;"></div>
           <div class="skeleton-text" style="width: 3rem; height: 2rem; background-color: #E2E8F0;"></div>
        </div>
      </template>
      <template v-else>
        <div class="stat-card stat-green">
          <p class="stat-label">Sous-services Actifs</p>
          <p class="stat-value">{{ activeServices }}</p>
        </div>
        <div class="stat-card stat-blue">
          <p class="stat-label">Total Sous-services</p>
          <p class="stat-value">{{ totalServices }}</p>
        </div>
        <div class="stat-card stat-orange">
          <p class="stat-label">Missions Complétées</p>
          <p class="stat-value">{{ totalCompletedJobs }}</p>
        </div>
      </template>
    </div>

    <!-- Services List -->
    <div class="card">
      <div class="card-header">
        <div>
          <h1>Mes Sous-services</h1>
          <p class="subtitle">Gérez vos sous-services, tarifs et disponibilité</p>
        </div>
      </div>

      <div v-if="loading" class="services-list">
        <div v-for="n in 3" :key="n" class="service-item skeleton-item" style="border-color: #cbd5e1 !important; background-color: #f8fafc !important; border-width: 2px; border-style: solid;">
          <div>
            <div class="service-header justify-between items-start" style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div class="w-full" style="width: 100%;">
                <div class="service-title flex items-center gap-2 mb-2" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                   <!-- Title -->
                  <div class="skeleton-text" style="width: 12rem; height: 1.5rem; border-radius: 0.25rem; background-color: #E2E8F0;"></div> 
                   <!-- Status Badge -->
                  <div class="skeleton-box" style="width: 4rem; height: 1.25rem; border-radius: 9999px; background-color: #E2E8F0;"></div>
                </div>
                <!-- Description -->
                <div class="skeleton-text" style="width: 75%; height: 1rem; border-radius: 0.25rem; margin-bottom: 0.5rem; background-color: #E2E8F0;"></div>
                <!-- Meta -->
                <div class="service-meta flex gap-2 mt-2" style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                   <div class="skeleton-box" style="width: 5rem; height: 1rem; border-radius: 9999px; background-color: #E2E8F0;"></div>
                   <div class="skeleton-box" style="width: 4rem; height: 1rem; border-radius: 9999px; background-color: #E2E8F0;"></div>
                </div>
              </div>
              <!-- Action Button Placeholder -->
              <div class="service-actions ml-4" style="margin-left: 1rem;">
                 <div class="skeleton-box" style="width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background-color: #E2E8F0;"></div> 
              </div>
            </div>

            <!-- Rate Section -->
            <div class="rate-badge mt-4" style="margin-top: 1rem;">
               <div class="skeleton-box" style="width: 6rem; height: 1.25rem; border-radius: 0.25rem; background-color: #E2E8F0;"></div>
            </div>

            <!-- Materials -->
            <div class="materials-section mt-4" style="margin-top: 1rem;">
              <div class="skeleton-text" style="width: 8rem; height: 1rem; margin-bottom: 0.5rem; background-color: #E2E8F0;"></div>
              <div class="materials-tags flex flex-wrap gap-2" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <div class="skeleton-box" style="width: 6rem; height: 1.5rem; border-radius: 9999px; background-color: #E2E8F0;"></div>
                <div class="skeleton-box" style="width: 8rem; height: 1.5rem; border-radius: 9999px; background-color: #E2E8F0;"></div>
                <div class="skeleton-box" style="width: 5rem; height: 1.5rem; border-radius: 9999px; background-color: #E2E8F0;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="services-list">
        <div
          v-for="service in displayedServices"
          :key="service.id"
          class="service-item"
          :style="{
            borderColor: service.active ? '#92B08B' : '#E0E0E0',
            backgroundColor: service.active ? '#E8F5E9' : '#F8F9FA'
          }"
        >
          <!-- Edit Mode -->
          <div v-if="editingService === service.id" class="edit-mode">
            <div class="form-group">
              <label>Tarif horaire (DH/h)</label>
              <input v-model.number="editData.hourlyRate" type="number" min="1" />
            </div>
            <div class="button-group">
              <button @click="saveEdit(service.id)" class="save-btn" :disabled="isSaving">
                <span v-if="isSaving">Enregistrement...</span>
                <span v-else>Enregistrer</span>
              </button>
              <button @click="editingService = null" class="cancel-btn">Annuler</button>
            </div>
          </div>

          <!-- View Mode -->
          <div v-else>
            <div class="service-header">
              <div>
                <div class="service-title">
                  <h3>{{ service.name }}</h3>
                  <span
                    v-if="service.active"
                    class="badge badge-green"
                  >Actif</span>
                  <span v-else class="badge badge-inactive">Inactif</span>
                </div>
                <p class="service-description">{{ service.description }}</p>
                <div class="service-meta">
                  <span class="meta-badge">{{ service.completedJobs }} missions</span>
                  <span class="meta-separator">•</span>
                  <span>{{ service.type === 'menage' ? 'Ménage' : 'Jardinage' }}</span>
                </div>
              </div>

              <div class="service-actions">
                <button v-if="service.active" @click="startEdit(service)" class="icon-btn">
                  <Edit2 :size="18" />
                </button>
<!--                <button @click="deleteService(service.id)" class="icon-btn icon-btn-danger">-->
<!--                  <Trash2 :size="18" />-->
<!--                </button>-->
              </div>
            </div>

            <!-- Rate Section -->
            <div class="rate-badge">
              <Coins :size="20" />
              <span>{{ service.hourlyRate }} DH/h</span>
            </div>

            <!-- Materials -->
            <div v-if="service.materials && service.materials.length > 0" class="materials-section">
              <p class="materials-label">Matériaux nécessaires</p>
              <div class="materials-tags">
                <span v-for="material in service.materials" :key="material" class="material-tag">
                  {{ material }}
                </span>
              </div>
            </div>
          </div>
        </div>
      
        <div v-if="!loading && displayedServices.length === 0" class="empty-state">
          <p>Vous n'avez pas encore configuré de sous-services</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Edit2, Trash2, Coins } from 'lucide-vue-next'
import intervenantTacheService from '@/services/intervenantTacheService'
import SkeletonLoader from './SkeletonLoader.vue'

const services = ref([])
const loading = ref(true)
const error = ref(null)

const materialsByService = {
  menage: [
    'Aspirateur industriel',
    'Balai et serpillière',
    'Produits de nettoyage',
    'Nettoyeur vapeur',
    'Raclette vitres',
    'Fer à repasser'
  ],
  jardinage: [
    'Tondeuse à gazon',
    'Taille-haie électrique',
    'Débroussailleuse',
    'Râteau et fourche',
    'Sécateur professionnel',
    'Arrosoir et tuyau'
  ]
}

const editingService = ref(null)
const editData = ref({
  hourlyRate: 0
})

const displayedServices = computed(() => {
  return services.value || []
})

const activeServices = computed(() => {
  return (services.value || []).filter(s => s.active).length
})

const totalServices = computed(() => {
  return (services.value || []).length
})

const totalCompletedJobs = computed(() => {
  return (services.value || []).reduce((sum, s) => sum + s.completedJobs, 0)
})

const getMaterials = (type) => {
  return materialsByService[type] || []
}

const fetchServices = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await intervenantTacheService.getMyTaches()
    services.value = response || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des sous-services'
    console.error(err)
    services.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchServices()
})

const toggleActive = async (id) => {
  try {
    await intervenantTacheService.toggleActive(id)
    // Refresh the service in the list
    const service = services.value.find(s => s.id === id)
    if (service) {
      service.active = !service.active
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Erreur lors de la mise à jour')
    console.error(err)
  }
}

const startEdit = (service) => {
  editingService.value = service.id
  editData.value = {
    hourlyRate: service.hourlyRate
  }
}

const isSaving = ref(false)

const saveEdit = async (id) => {
  try {
    isSaving.value = true
    await intervenantTacheService.updateMyTache(id, {
      hourlyRate: editData.value.hourlyRate,
    })
    
    // Update local state with response data if available, otherwise use local data
    const service = services.value.find(s => s.id === id)
    if (service) {
      service.hourlyRate = editData.value.hourlyRate
    }
    editingService.value = null
  } catch (err) {
    alert(err.response?.data?.message || 'Erreur lors de la sauvegarde')
    console.error(err)
  } finally {
    isSaving.value = false
  }
}


const deleteService = async (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce sous-service ?')) {
    try {
      await intervenantTacheService.deleteTache(id)
      // Remove from local state
      const index = services.value.findIndex(s => s.id === id)
      if (index > -1) {
        services.value.splice(index, 1)
      }
    } catch (err) {
      alert(err.response?.data?.message || 'Erreur lors de la suppression')
      console.error(err)
    }
  }
}
</script>

<style scoped>
/* Skeleton Styles */
.skeleton-item {
  border-color: #e5e7eb !important;
  background-color: #ffffff !important;
  pointer-events: none;
}

.skeleton-box {
  background-color: #f3f4f6;
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.container {
  max-width: 80rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.stat-card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--spacing-4);
  border-left: 4px solid;
}

.stat-green { border-left-color: var(--color-primary-green); }
.stat-blue { border-left-color: var(--color-blue); }
.stat-orange { border-left-color: var(--color-orange); }

.stat-label {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-1) 0;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.stat-green .stat-value { color: var(--color-primary-green); }
.stat-blue .stat-value { color: var(--color-blue); }
.stat-orange .stat-value { color: var(--color-orange); }

.card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--spacing-6);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--spacing-6);
}

.card-header h1 {
  margin: 0 0 var(--spacing-1) 0;
  color: var(--color-dark);
}

.subtitle {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin: 0;
}

.archive-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-2) var(--spacing-4);
  background-color: var(--color-light-green);
  color: var(--color-primary-green);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  transition: background-color 0.2s;
}

.archive-btn:hover {
  background-color: #D5EBD5;
}

.services-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-3);
}

.service-item {
  border: 2px solid;
  border-radius: var(--radius-lg);
  padding: var(--spacing-4);
  transition: all 0.3s;
}

.service-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-3);
}

.service-title {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  margin-bottom: var(--spacing-1);
}

.service-title h3 {
  margin: 0;
  color: var(--color-dark);
}

.badge {
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-green {
  background-color: var(--color-primary-green);
  color: var(--color-white);
}

.badge-gray {
  background-color: var(--color-gray-400);
  color: var(--color-white);
}

.badge-inactive {
  background-color: var(--color-gray-200);
  color: var(--color-gray-600);
}

.service-description {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-2) 0;
}

.service-meta {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  font-size: 0.75rem;
  color: var(--color-gray-600);
}

.meta-badge {
  padding: 0.125rem 0.5rem;
  background-color: var(--color-light-yellow);
  color: var(--color-dark);
  border-radius: 9999px;
}

.meta-separator {
  color: var(--color-gray-400);
}

.service-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.toggle-switch {
  position: relative;
  width: 3rem;
  height: 1.5rem;
  border-radius: 9999px;
  transition: background-color 0.3s;
  flex-shrink: 0;
}

.toggle-slider {
  position: absolute;
  width: 1rem;
  height: 1rem;
  background: var(--color-white);
  border-radius: 50%;
  top: 0.25rem;
  left: 0.25rem;
  box-shadow: var(--shadow-sm);
  transition: transform 0.3s;
}

.toggle-slider-active {
  transform: translateX(1.5rem);
}

.icon-btn {
  padding: var(--spacing-2);
  border-radius: var(--radius-lg);
  color: var(--color-blue);
  transition: background-color 0.2s;
}

.icon-btn:hover {
  background-color: var(--color-gray-100);
}

.icon-btn-orange {
  color: var(--color-orange);
}

.icon-btn-danger {
  color: var(--color-orange);
}

.rate-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-3);
  background: var(--color-white);
  border: 1px solid var(--color-gray-200);
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-3);
}

.rate-badge svg {
  color: var(--color-yellow);
}

.rate-badge span {
  font-size: 1.125rem;
  color: var(--color-primary-green);
  font-weight: 600;
}

.materials-section {
  padding: var(--spacing-3);
  background: var(--color-white);
  border: 1px solid var(--color-gray-200);
  border-radius: var(--radius-lg);
}

.materials-label {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-2) 0;
}

.materials-tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-2);
}

.material-tag {
  padding: 0.25rem 0.5rem;
  background-color: var(--color-light-green);
  color: var(--color-dark);
  border-radius: 9999px;
  font-size: 0.75rem;
}

.edit-mode {
  padding-top: var(--spacing-2);
}

.form-group {
  margin-bottom: var(--spacing-3);
}

.form-group label {
  display: block;
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin-bottom: var(--spacing-1);
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: var(--spacing-2) var(--spacing-3);
  font-size: 0.875rem;
}

.materials-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-2);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-2);
  font-size: 0.875rem;
  cursor: pointer;
  border-radius: var(--radius-md);
  transition: background-color 0.2s;
}

.checkbox-label:hover {
  background-color: var(--color-gray-50);
}

.checkbox-label input {
  width: 1rem;
  height: 1rem;
  cursor: pointer;
  accent-color: var(--color-primary-green);
}

.button-group {
  display: flex;
  gap: var(--spacing-2);
}

.save-btn {
  padding: var(--spacing-2) var(--spacing-4);
  background-color: var(--color-primary-green);
  color: var(--color-white);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  transition: background-color 0.2s;
}

.save-btn:hover {
  background-color: #A5C09E;
}

.cancel-btn {
  padding: var(--spacing-2) var(--spacing-4);
  background-color: var(--color-gray-200);
  color: var(--color-gray-700);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  transition: background-color 0.2s;
}

.cancel-btn:hover {
  background-color: var(--color-gray-300);
}

.empty-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-500);
}

.loading-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-600);
}

.error-message {
  padding: var(--spacing-4);
  background-color: #FEE2E2;
  color: #DC2626;
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
  border: 1px solid #FCA5A5;
}

@media (max-width: 768px) {
  .stats-grid,
  .materials-grid {
    grid-template-columns: 1fr;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-3);
  }
}

/* Skeleton Styles */
.skeleton-item {
  border-color: #e5e7eb !important;
  background-color: #ffffff !important;
  pointer-events: none;
}
.skeleton-box {
  background-color: #f3f4f6;
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.skeleton-text {
  background-color: #f3f4f6;
  border-radius: 4px;
  animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
