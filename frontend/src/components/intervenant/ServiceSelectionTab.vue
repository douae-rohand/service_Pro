<template>
  <div class="container">
    <!-- Success Message -->
    <div v-if="showSuccessMessage" class="success-message">
      <div class="success-icon">
        <Check :size="20" />
      </div>
      <div>
        <p class="erfolg-title">Votre demande a été envoyée avec succès !</p>
        <p class="success-subtitle">Elle sera examinée par notre équipe dans les plus brefs délais.</p>
      </div>
    </div>

    <!-- Pending Request Notification -->
    <div v-if="showPendingNotification" class="pending-notification">
      <div class="pending-icon">
        <AlertTriangle :size="20" />
      </div>
      <div>
        <p class="pending-title">Demande déjà en cours</p>
        <p class="pending-subtitle">{{ pendingNotificationMessage }}</p>
      </div>
      <button @click="closePendingNotification" class="pending-close">×</button>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card stat-green">
        <p class="stat-label">Services Actifs</p>
        <p class="stat-value">{{ activeServicesCount }}</p>
      </div>
      <div class="stat-card stat-blue">
        <p class="stat-label">Sous-services Actifs</p>
        <p class="stat-value">{{ activeTasksCount }}</p>
      </div>
    </div>

    <!-- Activation Request Modal -->
    <div v-if="showActivationModal" class="modal-overlay" @click.self="closeActivationModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2 class="modal-title">Demande d'activation</h2>
          <button @click="closeActivationModal" class="modal-close">
            <X :size="20" />
          </button>
        </div>
        
        <div class="modal-body">
          <div class="service-label">
            Service : <span :style="{ color: currentService?.color }">{{ currentService?.name }}</span>
          </div>

          <div class="info-banner">
            <div class="info-icon">
              <AlertCircle :size="18" />
            </div>
            <p>Votre demande sera examinée par notre équipe avant l'activation. Veuillez fournir les informations demandées.</p>
          </div>

          <form @submit.prevent="submitActivationRequest">
            <!-- Présentation -->
            <div class="form-group">
              <label>
                Présentation <span class="required">*</span>
              </label>
              <textarea
                v-model="activationForm.presentation"
                rows="4"
                placeholder="Présentez-vous et expliquez pourquoi vous souhaitez activer ce service..."
                required
              ></textarea>
            </div>

            <!-- Années d'expérience -->
            <div class="form-group">
              <label>
                Expérience <span class="required">*</span>
              </label>
              <div class="experience-inputs">
                <div class="experience-field">
                  <input
                    v-model.number="activationForm.experienceYears"
                    type="number"
                    min="0"
                    max="50"
                    placeholder="0"
                  />
                  <span class="experience-suffix">Années</span>
                </div>
                <div class="experience-field">
                  <input
                    v-model.number="activationForm.experienceMonths"
                    type="number"
                    min="0"
                    max="11"
                    placeholder="0"
                  />
                  <span class="experience-suffix">Mois</span>
                </div>
              </div>
            </div>

            <!-- Carte nationale ou Passeport -->
            <div class="form-group">
              <label>
                Carte nationale ou Passeport <span class="required">*</span>
              </label>
              <div class="upload-area" @click="triggerFileInput('idCard')">
                <Upload :size="24" class="upload-icon" />
                <p v-if="!activationForm.idCard">Cliquez pour télécharger</p>
                <p v-else class="file-name">{{ activationForm.idCard.name }}</p>
                <input
                  ref="idCardInput"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  @change="handleFileUpload($event, 'idCard')"
                  style="display: none"
                  required
                />
              </div>
              <p class="file-hint">PDF, JPG, PNG (max 5MB)</p>
            </div>

            <!-- Assurance professionnelle -->
            <div class="form-group">
              <label>
                Assurance professionnelle <span class="required">*</span>
              </label>
              <div class="upload-area" @click="triggerFileInput('insurance')">
                <Upload :size="24" class="upload-icon" />
                <p v-if="!activationForm.insurance">Cliquez pour télécharger</p>
                <p v-else class="file-name">{{ activationForm.insurance.name }}</p>
                <input
                  ref="insuranceInput"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  @change="handleFileUpload($event, 'insurance')"
                  style="display: none"
                  required
                />
              </div>
              <p class="file-hint">PDF, JPG, PNG (max 5MB)</p>
            </div>

            <!-- Autres documents -->
            <div class="form-group">
              <label>Autres documents (optionnel)</label>
              
              <!-- Existing additional documents -->
              <div v-for="(doc, index) in activationForm.documents.filter(d => d.type !== 'idCard' && d.type !== 'insurance')" :key="index" class="additional-doc-item">
                <div class="doc-info">
                  <div class="doc-type-input">
                    <input
                      v-model="doc.type"
                      type="text"
                      placeholder="Type de justificatif (ex: Certificat de formation)"
                      class="doc-type-field"
                    />
                  </div>
                  <div class="doc-file-info">
                    <FileText :size="16" />
                    <span>{{ doc.file.name }}</span>
                  </div>
                </div>
                <button type="button" @click="removeAdditionalDoc(index)" class="remove-doc-btn">
                  <Trash2 :size="16" />
                </button>
              </div>

              <!-- Add new document button -->
              <div class="upload-area upload-area-dashed" @click="triggerFileInput('additional')">
                <Upload :size="20" class="upload-icon" />
                <p>Certificats, références, etc.</p>
                <input
                  ref="additionalInput"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  @change="handleAdditionalDocUpload"
                  style="display: none"
                />
              </div>
              <p class="file-hint">Documents supplémentaires : certificats, formations, références</p>
            </div>

            <!-- Form Actions -->
            <div class="modal-actions">
              <button type="button" @click="closeActivationModal" class="btn-cancel">
                Annuler
              </button>
              <button type="submit" class="btn-submit" :style="{ backgroundColor: currentService?.color }">
                <Send :size="16" />
                Envoyer la demande
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Authentication Error State -->
    <div v-if="authError && !isLoadingUser" class="error-message">
      <p>{{ authError }}</p>
      <button @click="router.push('/')" class="retry-btn">Retour à l'accueil</button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading || isLoadingUser" class="card">
      <div class="empty-state">
        <div class="loader"></div>
        <p>Chargement des services...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="loadError && !isLoading" class="error-message">
      <p>{{ loadError }}</p>
      <button @click="fetchServices" class="retry-btn">Réessayer</button>
    </div>

    <!-- Service Selection -->
    <div v-if="!isLoading && !loadError && !authError && currentUser" class="card">
      <h2>Choisissez vos services</h2>
      <div class="services-grid">
        <div
          v-for="service in services"
          :key="service.id"
          class="service-card"
          :class="{ 'service-active': serviceStates[service.id] }"
          :style="serviceStates[service.id] ? {
            borderColor: service.color,
            backgroundColor: `${service.color}10`
          } : {}"
        >
          <div class="service-header">
            <span class="service-name">{{ service.name }}</span>
            <div class="service-actions">
              <button
                v-if="serviceStates[service.id]"
                @click="archiveService(service.id)"
                class="archive-btn"
                title="Archiver ce service"
              >
                <Archive :size="16" />
              </button>
              <button
                @click="toggleService(service.id, service.name)"
                class="toggle-switch"
                :style="{ backgroundColor: serviceStates[service.id] ? service.color : '#E0E0E0' }"
              >
                <span
                  class="toggle-slider"
                  :class="{ 'toggle-slider-active': serviceStates[service.id] }"
                ></span>
              </button>
            </div>
          </div>
          <p v-if="serviceStates[service.id]" class="service-status">Service activé</p>
        </div>
      </div>
    </div>

    <!-- Task Selection for Active Services -->
    <div v-if="currentUser" v-for="(isActive, serviceId) in serviceStates" :key="serviceId">
      <div v-if="isActive" class="card">
        <h2 :style="{ color: getServiceColor(serviceId) }">
          Sous-services pour {{ getServiceName(serviceId) }}
        </h2>
        <div class="tasks-list">
          <div
            v-for="task in getTasksByService(serviceId)"
            :key="task.id"
            class="task-card"
            :class="{ 'task-active': taskStates[task.id] }"
            :style="taskStates[task.id] ? {
              borderColor: getServiceColor(serviceId),
              backgroundColor: `${getServiceColor(serviceId)}08`
            } : {}"
          >
            <div class="task-header">
              <h3>{{ task.name }}</h3>
              <button
                @click="toggleTask(task.id)"
                class="toggle-switch toggle-switch-sm"
                :style="{ backgroundColor: taskStates[task.id] ? getServiceColor(serviceId) : '#E0E0E0' }"
              >
                <span
                  class="toggle-slider toggle-slider-sm"
                  :class="{ 'toggle-slider-active': taskStates[task.id] }"
                ></span>
              </button>
            </div>

            <!-- Task Configuration (shown when active) -->
            <div v-if="taskStates[task.id] && taskFormVisible[task.id]" class="task-config">
              <div class="form-group">
                <label>Votre tarif horaire <span class="required">*</span></label>
                <div class="input-with-suffix">
                  <input
                    v-model="taskPrices[task.id]"
                    type="number"
                    min="1"
                    step="0.5"
                    placeholder="Ex: 25"
                  />
                  <span class="input-suffix">DH/h</span>
                </div>
              </div>
              
              <!-- Save Button -->
              <div class="form-actions">
                <button 
                  @click="saveTaskConfig(task.id)"
                  class="btn-save-task"
                  :style="{ backgroundColor: getServiceColor(serviceId) }"
                >
                  Enregistrer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Service Materials for Active Services -->
    <div v-if="currentUser" v-for="(isActive, serviceId) in serviceStates" :key="serviceId">
      <div v-if="isActive" class="card">
        <h2 :style="{ color: getServiceColor(serviceId) }">
          Matériaux pour {{ getServiceName(serviceId) }}
        </h2>
        
        <!-- Show materials form or toggle button -->
        <div v-if="!materialsFormVisible[serviceId]" class="form-actions">
          <button 
            @click="loadServiceMaterials(serviceId)"
            class="btn-save-task"
            :style="{ backgroundColor: getServiceColor(serviceId) }"
          >
            Configurer les matériaux
          </button>
        </div>
        
        <div v-else class="service-materials">
          <div
            v-for="material in getMaterialsByService(serviceId)"
            :key="material"
            class="service-material-item"
          >
            <div class="material-header">
              <div class="material-checkbox-section">
                <input
                  type="checkbox"
                  :id="`material-${serviceId}-${material}`"
                  :checked="materialSelections[`${serviceId}-${material}`] || false"
                  @change="toggleMaterialSelection(serviceId, material)"
                  :style="{ accentColor: getServiceColor(serviceId) }"
                />
                <label :for="`material-${serviceId}-${material}`" class="material-name">{{ material }}</label>
              </div>
              <div class="material-price-section">
                <input
                  type="number"
                  v-model="serviceMaterialPrices[`${serviceId}-${material}`]"
                  placeholder="Prix"
                  min="0"
                  step="0.01"
                  class="price-field"
                  required
                />
                <span class="price-suffix">DH</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Save Service Materials Button (only shown when form is visible) -->
        <div v-if="materialsFormVisible[serviceId]" class="form-actions">
          <button 
            @click="saveServiceMaterials(serviceId)"
            class="btn-save-task"
            :style="{ backgroundColor: getServiceColor(serviceId) }"
          >
            Enregistrer les matériaux
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="currentUser && activeServicesCount === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon">
          <FileText :size="28" />
        </div>
        <p>Aucun service activé</p>
        <p class="text-sm">Activez vos services pour commencer à recevoir des demandes</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Plus, Trash2, FileText, AlertCircle, Upload, Send, X, Archive, Check, AlertTriangle } from 'lucide-vue-next'
import authService from '@/services/authService'
import intervenantService from '@/services/intervenantService'
import intervenantTacheService from '@/services/intervenantTacheService'
import serviceService from '@/services/serviceService'
import axios from 'axios'

const router = useRouter()
const currentUser = ref(null)
const isLoadingUser = ref(true)
const authError = ref(null)

const showSuccessMessage = ref(false)
const successMessage = ref('')
const showActivationModal = ref(false)
const currentService = ref(null)

// Loading and error states
const isLoading = ref(true)
const loadError = ref(null)

// File input refs
const idCardInput = ref(null)
const insuranceInput = ref(null)
const additionalInput = ref(null)

// Activation form data
const activationForm = ref({
  presentation: '',
  experienceYears: 0,
  experienceMonths: 0,
  documents: [] // Array of { type: string, file: File }
})

// Services data from API
const services = ref([])

// Color mapping for services (based on service name or ID from DB)
const serviceColors = {
  'Ménage': '#1A5FA3',
  'Jardinage': '#92B08B',
  // Add more service colors as needed
}

// Tasks by service (populated from API)
const tasksByService = ref({})

// Materials by service (populated from API)
const materialsByService = ref({})

// Fetch services from API
const fetchServices = async () => {
  try {
    isLoading.value = true
    loadError.value = null
    
    const response = await serviceService.getAll()
    const apiServices = response.data.data || [] // Backend wraps in 'data'
    
    // Map services with colors
    services.value = apiServices.map(service => ({
      id: service.id,
      name: service.name || service.nom_service, // Handle both name and nom_service
      color: serviceColors[service.name || service.nom_service] || '#92B08B',
      description: service.description
    }))
    
    // Build tasksByService and materialsByService objects
    tasksByService.value = {}
    materialsByService.value = {}
    
    apiServices.forEach(service => {
      // Backend might return 'tasks' or 'taches', map both to 'tasks' for frontend
      tasksByService.value[service.id] = service.tasks || service.taches || []
      
      // Get materials directly from service (new relation)
      const serviceMaterials = new Set()
      if (service.materials) {
        service.materials.forEach(material => {
          serviceMaterials.add(material.name)
        })
      }
      materialsByService.value[service.id] = Array.from(serviceMaterials)
    })
    
    // Initialize service states
    const initialStates = {}
    apiServices.forEach(service => {
      initialStates[service.id] = false
    })
    serviceStates.value = initialStates
    
    isLoading.value = false
  } catch (error) {
    console.error('Erreur lors du chargement des services:', error)
    loadError.value = 'Impossible de charger les services. Veuillez réessayer.'
    isLoading.value = false
  }
}

// Load intervenant's active services and tasks from DB
const loadIntervenantActiveData = async (intervenantId) => {
  try {
    const data = await intervenantService.getActiveServicesAndTasks(intervenantId)
    
    // Activate the services that are active in DB
    if (data && data.services && data.services.length > 0) {
      data.services.forEach(service => {
        if (service.status === 'active' && serviceStates.value[service.id] !== undefined) {
          serviceStates.value[service.id] = true
        } else if (service.status === 'archive' && serviceStates.value[service.id] !== undefined) {
          // If archived, we mark it so we can fast-reactivate
          archivedServices.value[service.id] = true
          serviceStates.value[service.id] = false
        } else if (service.status === 'demmande') {
          // If service has a pending request, mark it as pending
          pendingRequestServices.value[service.id] = true
          serviceStates.value[service.id] = false
        }
      })
    }
    
    // Initialize all tasks with their actual status from DB
    if (data.tasks && data.tasks.length > 0) {
      console.log('Initializing tasks from DB:', data.tasks)
      data.tasks.forEach(task => {
        if (task.id) {
          // Set the toggle state based on actual status from DB (handle both boolean and integer)
          taskStates.value[task.id] = task.status === true || task.status === 1
          console.log(`Task ${task.id}: status=${task.status}, taskStates=${taskStates.value[task.id]}`)
          
          // Update tasksByService to include the task with correct status
          if (!tasksByService.value[task.service_id]) {
            tasksByService.value[task.service_id] = []
          }
          
          // Check if task already exists in the service array
          const existingTaskIndex = tasksByService.value[task.service_id].findIndex(t => t.id === task.id)
          if (existingTaskIndex >= 0) {
            // Update existing task
            tasksByService.value[task.service_id][existingTaskIndex] = task
          } else {
            // Add new task
            tasksByService.value[task.service_id].push(task)
          }
          
          // Store price regardless of active status (for smart reactivation)
          if (task.price) {
            taskPrices.value[task.id] = task.price
          }

          // If task is active, handle form visibility
          if (task.status === true || task.status === 1) {
            // Check if price is set
            if (task.price) {
              // Hide the form for already configured active tasks
              taskFormVisible.value[task.id] = false
            } else {
              // Show the form for active tasks without price
              taskFormVisible.value[task.id] = true
            }
            
            // Initialize materials
            if (task.materials && task.materials.length > 0) {
              taskMaterials.value[task.id] = task.materials
              // Initialize prices for existing materials
              task.materials.forEach(m => {
                 // Try to keep existing price if already set locally, else 0
                 if (!taskMaterialPrices.value[`${task.id}-${m}`]) {
                    taskMaterialPrices.value[`${task.id}-${m}`] = 0
                 }
              })
            } else {
              taskMaterials.value[task.id] = []
            }
          } else {
            // For inactive tasks, also load materials so they are available on reactivation
             if (task.materials && task.materials.length > 0) {
              taskMaterials.value[task.id] = task.materials
            }
          }
        }
      })
      console.log('Final taskStates after initialization:', taskStates.value)
      console.log('Updated tasksByService:', tasksByService.value)
    }
    
    console.log('Loaded active services:', data.services)
    console.log('Loaded active tasks:', data.tasks)
  } catch (error) {
    console.error('Erreur lors du chargement des données de l\'intervenant:', error)
  }
}

// Load authenticated user and their data
const loadAuthenticatedUser = async () => {
  try {
    isLoadingUser.value = true
    authError.value = null

    // Check if token exists
    if (!authService.isAuthenticated()) {
      authError.value = 'Vous devez être connecté pour accéder à cette page'
      isLoadingUser.value = false
      return
    }

    // Get current user from API
    const response = await authService.getCurrentUser()
    const user = response.data

    // Safely check if user exists and has data
    if (!user) {
      authError.value = 'Impossible de charger les données utilisateur'
      isLoadingUser.value = false
      return
    }

    // Check if user is an intervenant
    if (!user.intervenant || !user.intervenant.id) {
      authError.value = 'Cette page est réservée aux intervenants'
      isLoadingUser.value = false
      return
    }

    // Set current user
    currentUser.value = {
      id: user.id,
      nom: user.nom,
      prenom: user.prenom,
      email: user.email,
      intervenant: user.intervenant
    }

    isLoadingUser.value = false
  } catch (error) {
    console.error('Erreur lors du chargement de l\'utilisateur:', error)
    
    // Handle authentication errors
    if (error.status === 401 || error.response?.status === 401) {
      authError.value = 'Session expirée. Veuillez vous reconnecter'
      // Remove invalid token
      authService.setAuthToken(null)
    } else {
      authError.value = 'Erreur lors du chargement de vos informations'
    }
    
    isLoadingUser.value = false
  }
}

// Load services on component mount
onMounted(async () => {
  // Load authenticated user first
  await loadAuthenticatedUser()
  
  // Only proceed if user is authenticated and is an intervenant
  if (currentUser.value && currentUser.value.id) {
    await fetchServices()
    // Load active services and tasks for the authenticated intervenant
    await loadIntervenantActiveData(currentUser.value.id)
  }
})



const serviceStates = ref({})
const taskStates = ref({})
const taskPrices = ref({})
const taskDescriptions = ref({})
const taskMaterials = ref({})
const taskMaterialPrices = ref({}) // New: Store prices for each material
const taskFormVisible = ref({})

const activeServicesCount = computed(() => {
  return Object.values(serviceStates.value).filter(Boolean).length
})

const activeTasksCount = computed(() => {
  return Object.values(taskStates.value).filter(Boolean).length
})

// Add service materials state
const serviceMaterialPrices = ref({})
const materialSelections = ref({})
const materialsFormVisible = ref({})

// Add archived services state
const archivedServices = ref({})

// Add pending request state for services with 'demmande' status
const pendingRequestServices = ref({})

// Notification state for pending requests
const showPendingNotification = ref(false)
const pendingNotificationMessage = ref('')

const showPendingRequestNotification = () => {
  pendingNotificationMessage.value = 'Vous avez déjà déposé une demande pour ce service. Veuillez attendre que l\'administrateur traite votre demande. Un email vous sera envoyé une fois la demande traitée.'
  showPendingNotification.value = true
  
  // Auto-hide after 5 seconds
  setTimeout(() => {
    showPendingNotification.value = false
  }, 5000)
}

const closePendingNotification = () => {
  showPendingNotification.value = false
}

const toggleService = async (serviceId, serviceName) => {
  // If service is archived, reactivate directly
  if (archivedServices.value[serviceId]) {
    try {
      const response = await intervenantService.updateServiceStatus(
        currentUser.value.intervenant.id,
        serviceId,
        'active'
      )
      
      console.log('Service reactivated:', response)
      
      // Update states
      archivedServices.value[serviceId] = false
      serviceStates.value[serviceId] = true
    } catch (error) {
      console.error('Error reactivating service:', error)
      alert('Erreur lors de la réactivation du service')
    }
    return
  }
  
  // If activating the service, check if there's already a pending request
  if (!serviceStates.value[serviceId]) {
    // Check if there's already a pending request for this service
    if (pendingRequestServices.value[serviceId]) {
      // Show notification that request is already pending
      showPendingRequestNotification()
      return
    }
    
    const service = services.value.find(s => s.id === serviceId)
    if (service) {
      currentService.value = service
      showActivationModal.value = true
    }
  } else {
    // Deactivating - call API to update status in database
    try {
      const response = await intervenantService.toggleService(
        currentUser.value.intervenant.id,
        serviceId
      )
      
      console.log('Service toggle response:', response)
      
      // Update local state after successful API call
      serviceStates.value[serviceId] = false
    } catch (error) {
      console.error('Error toggling service:', error)
      // Revert the state if API call failed
      serviceStates.value[serviceId] = true
      alert('Erreur lors de la désactivation du service')
    }
  }
}

const archiveService = async (serviceId) => {
  try {
    const response = await intervenantService.updateServiceStatus(
      currentUser.value.intervenant.id,
      serviceId,
      'archive'
    )
    
    console.log('Service archived:', response)
    
    // Update states
    serviceStates.value[serviceId] = false
    archivedServices.value[serviceId] = true
  } catch (error) {
    console.error('Error archiving service:', error)
    alert('Erreur lors de l\'archivage du service')
  }
}

// Load service materials with existing prices and selections
const loadServiceMaterials = async (serviceId) => {
  if (!currentUser.value) return
  
  try {
    const response = await intervenantService.getIntervenantMaterials(
      currentUser.value.intervenant.id,
      serviceId
    )
    
    const materials = response.materials || []
    
    // Pre-fill prices and selections
    materials.forEach(material => {
      const key = `${serviceId}-${material.name}`
      
      // Set selection status (possessed)
      if (material.possessed) {
        materialSelections.value[key] = true
      }
      
      // Set price
      if (material.price > 0) {
        serviceMaterialPrices.value[key] = material.price
      }
    })
    
    // Show the form
    materialsFormVisible.value[serviceId] = true
  } catch (error) {
    console.error('Error loading service materials:', error)
    // Still show the form even if loading fails
    materialsFormVisible.value[serviceId] = true
  }
}

const saveServiceMaterials = async (serviceId) => {
  if (!currentUser.value) return
  
  // Collect selected materials with prices
  const materials = []
  const serviceMaterials = getMaterialsByService(serviceId)
  
  for (const material of serviceMaterials) {
    const isSelected = materialSelections.value[`${serviceId}-${material}`]
    const price = serviceMaterialPrices.value[`${serviceId}-${material}`]
    
    if (isSelected) {
      if (!price || price <= 0) {
        alert(`Le prix est obligatoire pour le matériau : ${material}`)
        return
      }
      materials.push({
        name: material,
        price: parseFloat(price)
      })
    }
  }
  
  // Allow saving with no materials (user can have zero materials)
  try {
    console.log('Saving service materials:', {
      serviceId: serviceId,
      materials: materials
    })
    
    // Send data to backend (can be empty array)
    const response = await intervenantService.updateServiceMaterials(
      currentUser.value.intervenant.id,
      serviceId,
      materials
    )
    
    console.log('Save response:', response)
    
    // Hide the materials form after successful save
    materialsFormVisible.value[serviceId] = false
    
    // Show success message
    showSuccessMessage.value = true
    successMessage.value = materials.length > 0 
      ? 'Matériaux enregistrés avec succès !' 
      : 'Aucun matériau sélectionné - Configuration enregistrée !'
  } catch (error) {
    console.error('Error saving service materials:', error)
    alert(`Erreur lors de l'enregistrement: ${error.response?.data?.message || error.message}`)
  }
}

// Toggle material selection with price clearing and database deletion
const toggleMaterialSelection = async (serviceId, material) => {
  if (!currentUser.value) return
  
  const key = `${serviceId}-${material}`
  const currentState = materialSelections.value[key] || false
  
  // Toggle selection
  materialSelections.value[key] = !currentState
  
  // If unchecking, clear the price and delete from database
  if (currentState) {
    serviceMaterialPrices.value[key] = ''
    
    // Find the material ID to delete from database
    try {
      const serviceMaterials = getMaterialsByService(serviceId)
      const materialIndex = serviceMaterials.indexOf(material)
      
      if (materialIndex !== -1) {
        // Get material details to find its ID
        const response = await intervenantService.getIntervenantMaterials(
          currentUser.value.intervenant.id,
          serviceId
        )
        
        const materialData = response.materials.find(m => m.name === material)
        if (materialData) {
          // Delete from database
          await intervenantService.deleteIntervenantMaterial(
            currentUser.value.intervenant.id,
            materialData.id
          )
          console.log(`Material ${material} removed from database`)
        }
      }
    } catch (error) {
      console.error('Error removing material from database:', error)
      // Still clear the UI even if database deletion fails
    }
  }
}

const closeActivationModal = () => {
  showActivationModal.value = false
  currentService.value = null
  resetActivationForm()
}

const resetActivationForm = () => {
  activationForm.value = {
    presentation: '',
    experienceYears: 0,
    experienceMonths: 0,
    documents: []
  }
  
  // Reset file inputs
  if (idCardInput.value) idCardInput.value.value = ''
  if (insuranceInput.value) insuranceInput.value.value = ''
  if (additionalInput.value) additionalInput.value.value = ''
}

const triggerFileInput = (type) => {
  if (type === 'idCard' && idCardInput.value) {
    idCardInput.value.click()
  } else if (type === 'insurance' && insuranceInput.value) {
    insuranceInput.value.click()
  } else if (type === 'additional' && additionalInput.value) {
    additionalInput.value.click()
  }
}

const handleFileUpload = (event, type) => {
  const target = event.target
  const file = target.files?.[0]
  
  if (file) {
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Le fichier est trop volumineux. Taille maximale : 5MB')
      return
    }
    
    // Remove existing document of same type if it exists
    activationForm.value.documents = activationForm.value.documents.filter(doc => doc.type !== type)
    
    // Add new document
    activationForm.value.documents.push({
      type: type,
      file: file
    })
  }
}

const handleAdditionalDocUpload = (event) => {
  const target = event.target
  const file = target.files?.[0]
  
  if (file) {
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Le fichier est trop volumineux. Taille maximale : 5MB')
      return
    }
    
    // Add document with empty type (user will fill it in)
    activationForm.value.documents.push({
      type: '', // User will fill this in
      file: file
    })
    
    // Reset the input
    if (additionalInput.value) {
      additionalInput.value.value = ''
    }
  }
}

const removeAdditionalDoc = (index) => {
  // Find the actual index in the full documents array
  const additionalDocs = activationForm.value.documents.filter(d => d.type !== 'idCard' && d.type !== 'insurance')
  const actualIndex = activationForm.value.documents.findIndex(doc => doc === additionalDocs[index])
  if (actualIndex !== -1) {
    activationForm.value.documents.splice(actualIndex, 1)
  }
}

const submitActivationRequest = async () => {
  // Validate that all documents have types specified (except idCard and insurance)
  const hasEmptyTypes = activationForm.value.documents.some(doc => !doc.type.trim())
  if (hasEmptyTypes) {
    alert('Veuillez spécifier le type pour tous les documents supplémentaires')
    return
  }

  // Validate experience
  const totalMonths = (activationForm.value.experienceYears * 12) + activationForm.value.experienceMonths
  if (totalMonths === 0) {
    alert('Veuillez spécifier votre expérience')
    return
  }

  try {
    console.log('Submitting activation request for service:', currentService.value.id)
    
    // Create FormData to handle file uploads
    const formData = new FormData()
    
    // Add form data
    formData.append('intervenant_id', currentUser.value.intervenant.id)
    formData.append('service_id', currentService.value.id)
    formData.append('presentation', activationForm.value.presentation)
    formData.append('experience', totalMonths / 12) // Convert to years as decimal
    
    // Add all documents
    activationForm.value.documents.forEach((doc, index) => {
      formData.append(`documents[${index}][type]`, doc.type)
      formData.append(`documents[${index}][file]`, doc.file)
    })
    
    // Call the API with FormData using the service
    const response = await intervenantService.requestActivation(
      currentUser.value.intervenant.id,
      currentService.value.id,
      formData
    )
    
    console.log('Activation request response:', response)
    
    // Show success message
    showSuccessMessage.value = true
    successMessage.value = 'Demande d\'activation envoyée avec succès !'
    
    // Update service state to show it's pending
    serviceStates.value[currentService.value.id] = false // Will be updated when data is refreshed
    
    // Close modal and reset form
    closeActivationModal()
    
    // Refresh data to show updated status
    await loadIntervenantActiveData(currentUser.value.id)
    
  } catch (error) {
    console.error('Error submitting activation request:', error)
    alert(`Erreur lors de l'envoi de la demande: ${error.response?.data?.message || error.message}`)
  }
}

const toggleTask = async (taskId) => {
  try {
    // If activating check if we already have a price configured
    if (!taskStates.value[taskId] && taskPrices.value[taskId] && taskPrices.value[taskId] > 0) {
        // Direct reactivation (no form needed)
        const response = await intervenantTacheService.toggleActive(taskId)
        console.log('Task reactivated directly:', response.data)
        
        taskStates.value[taskId] = true
        // Ensure form is hidden
        taskFormVisible.value[taskId] = false
        
        // Show lightweight success message
        // showSuccessMessage.value = true
        // successMessage.value = 'Service réactivé avec succès !'
        return
    }

    // Standard toggle logic
    // Call API to toggle the status in database
    const response = await intervenantTacheService.toggleActive(taskId)
    console.log('Task toggle response:', response)
    
    // Update local task state based on response
    if (response && response.active !== undefined) {
      taskStates.value[taskId] = response.active
    } else {
      // Fallback if response.active is not present
      taskStates.value[taskId] = !taskStates.value[taskId]
    }
    
    if (!taskStates.value[taskId]) {
      // Désactiver : masquer le formulaire
      // NOTE: We do NOT delete the price/materials data here anymore, so it can be restored on reactivation
      delete taskFormVisible.value[taskId]
    } else {
      // Activer : afficher le formulaire
      taskFormVisible.value[taskId] = true
      // Ne pas écraser les matériaux existants si déjà chargés
      if (!taskMaterials.value[taskId]) {
        taskMaterials.value[taskId] = []
      }
    }
  } catch (error) {
    console.error('Error toggling task:', error)
    // Revert the state if API call failed
    taskStates.value[taskId] = !taskStates.value[taskId]
    alert('Erreur lors de la modification du statut de la tâche')
  }
}
// Save task configuration and hide form
const saveTaskConfig = async (taskId) => {
  if (!currentUser.value) return
  
  // Validate price is filled
  if (!taskPrices.value[taskId] || taskPrices.value[taskId] <= 0) {
    alert('Le tarif horaire est obligatoire')
    return
  }
  
  // Validate material prices
  const materials = taskMaterials.value[taskId] || []
  const materialsWithPrices = []
  
  for (const material of materials) {
    const price = taskMaterialPrices.value[`${taskId}-${material}`]
    if (!price || price <= 0) {
      alert(`Le prix pour ${material} est obligatoire`)
      return
    }
    materialsWithPrices.push({
      name: material,
      price: parseFloat(price)
    })
  }
  
  try {
    console.log('Saving task config:', {
      taskId: taskId,
      hourlyRate: taskPrices.value[taskId],
      materials: materialsWithPrices
    })
    
    // Send data to backend using the correct service
    const response = await intervenantTacheService.updateMyTache(taskId, {
      hourlyRate: taskPrices.value[taskId],
      materials: materialsWithPrices
    })
    
    console.log('Save response:', response)
    
    // Hide the form but keep the task active
    taskFormVisible.value[taskId] = false
    
    // Ensure task state remains active (this fixes the toggle issue)
    taskStates.value[taskId] = true
    
    // Success message removed as per request (sub-services don't need "pending review" notification)
    // showSuccessMessage.value = true
    // successMessage.value = 'Configuration enregistrée avec succès !'
  } catch (error) {
    console.error('Erreur complète:', error)
    console.error('Response data:', error.response?.data)
    console.error('Status:', error.response?.status)
    alert(`Erreur lors de l'enregistrement: ${error.response?.data?.message || error.message}`)
  }
}

const toggleMaterial = (taskId, material) => {
  if (!taskMaterials.value[taskId]) {
    taskMaterials.value[taskId] = []
  }
  
  const index = taskMaterials.value[taskId].indexOf(material)
  if (index > -1) {
    taskMaterials.value[taskId].splice(index, 1)
    // Remove price when unchecked
    delete taskMaterialPrices.value[`${taskId}-${material}`]
  } else {
    taskMaterials.value[taskId].push(material)
    // Initialize price when checked
    taskMaterialPrices.value[`${taskId}-${material}`] = 0
  }
}

// Watch for changes in taskMaterials to handle price initialization
watch(taskMaterials, (newTaskMaterials, oldTaskMaterials) => {
  for (const taskId in newTaskMaterials) {
    const currentMaterials = newTaskMaterials[taskId] || []
    const oldMaterials = oldTaskMaterials[taskId] || []
    
    // Find newly added materials
    for (const material of currentMaterials) {
      if (!oldMaterials.includes(material)) {
        // Initialize price for newly added material
        if (!taskMaterialPrices.value[`${taskId}-${material}`]) {
          taskMaterialPrices.value[`${taskId}-${material}`] = 0
        }
      }
    }
    
    // Clean up prices for removed materials
    for (const material of oldMaterials) {
      if (!currentMaterials.includes(material)) {
        delete taskMaterialPrices.value[`${taskId}-${material}`]
      }
    }
  }
}, { deep: true })

const getServiceColor = (serviceId) => {
  return services.value.find(s => s.id == serviceId)?.color || '#92B08B'
}

const getServiceName = (serviceId) => {
  const service = services.value.find(s => s.id == serviceId)
  return service?.name || ''
}

const getTasksByService = (serviceId) => {
  return tasksByService.value[serviceId] || []
}

const getMaterialsByService = (serviceId) => {
  return materialsByService.value[serviceId] || []
}
</script>

<style scoped>
.container {
  max-width: 80rem;
}

.success-message {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-4);
  background: var(--color-white);
  box-shadow: var(--shadow-md);
  border-left: 4px solid var(--color-primary-green);
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
}

.success-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: var(--color-light-green);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary-green);
}

.success-title {
  font-size: 0.875rem;
  color: var(--color-dark);
  margin: 0;
}

.success-subtitle {
  font-size: 0.75rem;
  color: var(--color-gray-500);
  margin: 0;
}

/* Pending Request Notification */
.pending-notification {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-4);
  padding: var(--spacing-4);
  background: #FEF3E2;
  border-left: 4px solid #F59E0B;
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
  position: relative;
}

.pending-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: #FEF3E2;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #F59E0B;
  flex-shrink: 0;
}

.pending-title {
  font-size: 0.875rem;
  color: var(--color-dark);
  margin: 0 0 var(--spacing-1) 0;
  font-weight: 600;
}

.pending-subtitle {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin: 0;
  line-height: 1.4;
}

.pending-close {
  position: absolute;
  top: var(--spacing-3);
  right: var(--spacing-3);
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  background: transparent;
  border: none;
  color: var(--color-gray-500);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  transition: all 0.2s;
}

.pending-close:hover {
  background: rgba(0, 0, 0, 0.1);
  color: var(--color-dark);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--spacing-4);
  overflow-y: auto;
}

.modal-container {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-xl);
  max-width: 40rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-6);
  border-bottom: 1px solid var(--color-gray-200);
}

.modal-title {
  font-size: 1.25rem;
  color: var(--color-dark);
  margin: 0;
}

.modal-close {
  width: 2rem;
  height: 2rem;
  border-radius: var(--radius-md);
  background: transparent;
  border: none;
  color: var(--color-gray-600);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.modal-close:hover {
  background: var(--color-gray-100);
  color: var(--color-dark);
}

.modal-body {
  padding: var(--spacing-6);
}

.service-label {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin-bottom: var(--spacing-4);
}

.service-label span {
  font-weight: 600;
}

.info-banner {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-3);
  padding: var(--spacing-4);
  background: #FEF3E2;
  border-left: 4px solid #F59E0B;
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-6);
}

.info-icon {
  color: #F59E0B;
  flex-shrink: 0;
  margin-top: 2px;
}

.info-banner p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--color-gray-700);
  line-height: 1.5;
}

.form-group {
  margin-bottom: var(--spacing-5);
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-dark);
  margin-bottom: var(--spacing-2);
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea {
  width: 100%;
  padding: var(--spacing-3);
  border: 1px solid var(--color-gray-300);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  transition: all 0.2s;
}

.form-group input[type="text"]:focus,
.form-group input[type="number"]:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--color-primary-green);
  box-shadow: 0 0 0 3px rgba(146, 176, 139, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.upload-area {
  border: 2px dashed var(--color-gray-300);
  border-radius: var(--radius-md);
  padding: var(--spacing-6);
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--color-gray-50);
}

.upload-area:hover {
  border-color: var(--color-primary-green);
  background: #F0F4EF;
}

.upload-area-dashed {
  padding: var(--spacing-4);
  margin-top: var(--spacing-3);
}

.upload-icon {
  color: var(--color-gray-400);
  margin-bottom: var(--spacing-2);
}

.upload-area p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--color-gray-600);
}

.file-name {
  color: var(--color-primary-green) !important;
  font-weight: 500;
}

.file-hint {
  font-size: 0.75rem;
  color: var(--color-gray-500);
  margin-top: var(--spacing-2);
}

.additional-doc-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-3);
  background: var(--color-gray-50);
  border: 1px solid var(--color-gray-200);
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-3);
}

.doc-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2);
}

.doc-type-input {
  width: 100%;
}

.doc-type-field {
  width: 100%;
  padding: var(--spacing-2);
  border: 1px solid var(--color-gray-300);
  border-radius: var(--radius-md);
  font-size: 0.75rem;
}

.doc-file-info {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  color: var(--color-gray-600);
  font-size: 0.75rem;
}

.remove-doc-btn {
  padding: var(--spacing-2);
  background: transparent;
  border: none;
  color: #EF4444;
  cursor: pointer;
  border-radius: var(--radius-md);
  transition: all 0.2s;
  flex-shrink: 0;
}

.remove-doc-btn:hover {
  background: #FEE2E2;
}

.modal-actions {
  display: flex;
  gap: var(--spacing-3);
  margin-top: var(--spacing-6);
  padding-top: var(--spacing-6);
  border-top: 1px solid var(--color-gray-200);
}

.btn-cancel,
.btn-submit {
  flex: 1;
  padding: var(--spacing-3) var(--spacing-4);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-2);
}

.btn-cancel {
  background: var(--color-white);
  color: var(--color-gray-700);
  border: 1px solid var(--color-gray-300);
}

.btn-cancel:hover {
  background: var(--color-gray-50);
}

.btn-submit {
  color: var(--color-white);
}

.btn-submit:hover {
  opacity: 0.9;
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
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

.stat-green {
  border-left-color: var(--color-primary-green);
}

.stat-blue {
  border-left-color: var(--color-blue);
}

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

.stat-green .stat-value {
  color: var(--color-primary-green);
}

.stat-blue .stat-value {
  color: var(--color-blue);
}

.card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--spacing-6);
  margin-bottom: var(--spacing-6);
}

.card h2 {
  color: var(--color-dark);
  margin: 0 0 var(--spacing-4) 0;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-4);
}

.service-card {
  padding: var(--spacing-4);
  border: 2px solid var(--color-gray-200);
  border-radius: var(--radius-lg);
  transition: all 0.3s;
}

.service-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.service-name {
  font-size: 1.125rem;
  color: var(--color-dark);
}

.service-status {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  margin: var(--spacing-2) 0 0 0;
}

/* Service Actions */
.service-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.archive-btn {
  width: 2rem;
  height: 2rem;
  border-radius: var(--radius-md);
  background: transparent;
  border: 1px solid var(--color-gray-300);
  color: var(--color-gray-600);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.archive-btn:hover {
  background: var(--color-gray-100);
  color: var(--color-dark);
  border-color: var(--color-gray-400);
}

.toggle-switch {
  position: relative;
  width: 3.5rem;
  height: 1.75rem;
  border-radius: 9999px;
  transition: background-color 0.3s;
}

.toggle-switch-sm {
  width: 3rem;
  height: 1.5rem;
}

.toggle-slider {
  position: absolute;
  width: 1.25rem;
  height: 1.25rem;
  background: var(--color-white);
  border-radius: 50%;
  top: 0.25rem;
  left: 0.25rem;
  box-shadow: var(--shadow-sm);
  transition: transform 0.3s;
}

.toggle-slider-sm {
  width: 1rem;
  height: 1rem;
}

.toggle-slider-active {
  transform: translateX(1.75rem);
}

.toggle-slider-sm.toggle-slider-active {
  transform: translateX(1.5rem);
}

.tasks-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-3);
}

.task-card {
  padding: var(--spacing-4);
  border: 2px solid var(--color-gray-200);
  border-radius: var(--radius-lg);
  transition: all 0.3s;
}

.task-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--spacing-4);
}

.task-header h3 {
  font-size: 0.875rem;
  color: var(--color-dark);
  margin: 0;
  flex: 1;
}

.task-config {
  margin-top: var(--spacing-4);
  padding-top: var(--spacing-4);
  border-top: 1px solid var(--color-gray-200);
}

.required {
  color: #E8793F;
}

.input-with-suffix {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.input-with-suffix input {
  flex: 1;
}

.input-suffix {
  font-size: 0.875rem;
  color: var(--color-gray-600);
}

/* Experience Inputs */
.experience-inputs {
  display: flex;
  gap: var(--spacing-3);
  align-items: flex-start;
}

.experience-field {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  flex: 1;
  max-width: 120px;
}

.experience-field input {
  width: 60px;
  padding: var(--spacing-2);
  border: 1px solid var(--color-gray-300);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  text-align: center;
  transition: border-color 0.2s;
}

.experience-field input:focus {
  outline: none;
  border-color: var(--color-primary-green);
  box-shadow: 0 0 0 2px rgba(146, 176, 139, 0.2);
}

.experience-suffix {
  font-size: 0.8rem;
  color: var(--color-gray-600);
  font-weight: 500;
  white-space: nowrap;
}

.materials-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-3);
}

.material-item {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2);
  padding: var(--spacing-3);
  border: 1px solid var(--color-gray-200);
  border-radius: var(--radius-md);
  transition: all 0.2s;
}

.material-item:hover {
  border-color: var(--color-gray-300);
  background-color: var(--color-gray-50);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 0.875rem;
  flex: 1;
}

.checkbox-label input {
  width: 1.1rem;
  height: 1.1rem;
  cursor: pointer;
  flex-shrink: 0;
}

.material-price-input {
  display: flex;
  align-items: center;
  gap: var(--spacing-1);
  margin-top: var(--spacing-1);
}

.price-field {
  width: 80px;
  padding: var(--spacing-1) var(--spacing-2);
  border: 1px solid var(--color-gray-300);
  border-radius: var(--radius-sm);
  font-size: 0.8rem;
  text-align: center;
  transition: border-color 0.2s;
}

.price-field:focus {
  outline: none;
  border-color: var(--color-primary-green);
  box-shadow: 0 0 0 2px rgba(146, 176, 139, 0.2);
}

.price-suffix {
  font-size: 0.75rem;
  color: var(--color-gray-600);
  font-weight: 500;
}

/* Task Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: var(--spacing-4);
  padding-top: var(--spacing-4);
  border-top: 1px solid var(--color-gray-200);
}

.btn-save-task {
  padding: var(--spacing-2) var(--spacing-6);
  color: var(--color-white);
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.btn-save-task:hover {
  opacity: 0.9;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.empty-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-500);
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  background-color: var(--color-light-yellow);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto var(--spacing-4);
  color: var(--color-primary-green);
}

@media (max-width: 768px) {
  .stats-grid,
  .services-grid,
  .materials-grid {
    grid-template-columns: 1fr;
  }

  .modal-container {
    max-height: 95vh;
  }
  
  .modal-header,
  .modal-body {
    padding: var(--spacing-4);
  }

  .modal-actions {
    flex-direction: column;
  }
}

/* Loader Animation */
.loader {
  width: 40px;
  height: 40px;
  border: 4px solid var(--color-gray-200);
  border-top-color: var(--color-primary-green);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto var(--spacing-4);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Error Message */
.error-message {
  background: #FEE2E2;
  border-left: 4px solid #DC2626;
  border-radius: var(--radius-lg);
  padding: var(--spacing-4);
  margin-bottom: var(--spacing-6);
  text-align: center;
}

.error-message p {
  color: #991B1B;
  margin: 0 0 var(--spacing-3) 0;
}

.retry-btn {
  padding: var(--spacing-2) var(--spacing-4);
  background-color: #DC2626;
  color: var(--color-white);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background-color: #B91C1C;
}

/* Service Materials */
.service-materials {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
}

.service-material-item {
  border: 1px solid var(--color-gray-200);
  border-radius: var(--radius-lg);
  padding: var(--spacing-4);
  transition: all 0.2s;
}

.service-material-item:hover {
  border-color: var(--color-gray-300);
  background-color: var(--color-gray-50);
}

.material-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-3);
  margin-bottom: var(--spacing-3);
}

.material-checkbox-section {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  flex: 1;
}

.material-price-section {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.material-name {
  font-weight: 600;
  color: var(--color-dark);
  flex: 1;
}

.material-tasks {
  border-top: 1px solid var(--color-gray-200);
  padding-top: var(--spacing-3);
}

.task-checkbox-label {
  display: block;
  margin-bottom: var(--spacing-2);
}

.checkbox-title {
  font-weight: 500;
  color: var(--color-gray-700);
  font-size: 0.875rem;
}

.tasks-checkboxes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: var(--spacing-2);
}

.task-checkbox-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  cursor: pointer;
  font-size: 0.8rem;
  color: var(--color-gray-600);
}

.task-checkbox-item input {
  width: 0.9rem;
  height: 0.9rem;
  cursor: pointer;
}

.task-checkbox-item span {
  line-height: 1.2;
}
</style>
