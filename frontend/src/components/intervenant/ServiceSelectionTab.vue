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
                Années d'expérience <span class="required">*</span>
              </label>
              <input
                v-model.number="activationForm.experience"
                type="number"
                min="0"
                placeholder="Ex: 5"
                required
              />
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
              <div v-for="(doc, index) in activationForm.additionalDocs" :key="index" class="additional-doc-item">
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

    <!-- Service Selection -->
    <div class="card">
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
          <p v-if="serviceStates[service.id]" class="service-status">Service activé</p>
        </div>
      </div>
    </div>

    <!-- Task Selection for Active Services -->
    <div v-for="(isActive, serviceId) in serviceStates" :key="serviceId">
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
            <div v-if="taskStates[task.id]" class="task-config">
              <div class="form-group">
                <label>Votre tarif horaire <span class="required">*</span></label>
                <div class="input-with-suffix">
                  <input
                    v-model="taskPrices[task.id]"
                    type="number"
                    min="1"
                    step="0.5"
                    :placeholder="String(task.baseRate)"
                  />
                  <span class="input-suffix">DH/h</span>
                </div>
              </div>

              <div class="form-group">
                <label>Description de votre présentation</label>
                <textarea
                  v-model="taskDescriptions[task.id]"
                  rows="3"
                  placeholder="Décrivez votre expérience et ce que vous proposez pour ce sous-service..."
                ></textarea>
              </div>

              <div class="form-group">
                <label>Matériaux nécessaires</label>
                <div class="materials-grid">
                  <label
                    v-for="material in getMaterialsByService(serviceId)"
                    :key="material"
                    class="checkbox-label"
                  >
                    <input
                      type="checkbox"
                      :checked="taskMaterials[task.id]?.includes(material)"
                      @change="toggleMaterial(task.id, material)"
                      :style="{ accentColor: getServiceColor(serviceId) }"
                    />
                    <span>{{ material }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="activeServicesCount === 0" class="card">
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
import { ref, computed } from 'vue'
import { Check, FileText, X, Upload, AlertCircle, Trash2, Send } from 'lucide-vue-next'

const showSuccessMessage = ref(false)
const showActivationModal = ref(false)
const currentService = ref(null)

// File input refs
const idCardInput = ref(null)
const insuranceInput = ref(null)
const additionalInput = ref(null)

// Activation form data
const activationForm = ref({
  presentation: '',
  experience: 0,
  idCard: null,
  insurance: null,
  additionalDocs: []
})

const services = [
  { id: 'menage', name: 'Ménage', color: '#1A5FA3' },
  { id: 'jardinage', name: 'Jardinage', color: '#92B08B' }
]

const tasksByService = {
  menage: [
    { id: 'menage-1', name: 'Ménage résidentiel & régulier', baseRate: 20 },
    { id: 'menage-2', name: 'Nettoyage en profondeur (Deep Cleaning)', baseRate: 24 },
    { id: 'menage-3', name: 'Nettoyage spécial : déménagement & post-travaux', baseRate: 25 },
    { id: 'menage-4', name: 'Lavage vitres & surfaces spécialisées', baseRate: 22 },
    { id: 'menage-5', name: 'Nettoyage mobilier & textiles', baseRate: 23 },
    { id: 'menage-6', name: 'Nettoyage professionnel (bureaux & commerces)', baseRate: 21 }
  ],
  jardinage: [
    { id: 'jardinage-1', name: 'Tonte de pelouse', baseRate: 25 },
    { id: 'jardinage-2', name: 'Taille de haies', baseRate: 28 },
    { id: 'jardinage-3', name: 'Plantation de fleurs', baseRate: 26 },
    { id: 'jardinage-4', name: 'Élagage d\'arbres', baseRate: 30 },
    { id: 'jardinage-5', name: 'Désherbage', baseRate: 24 },
    { id: 'jardinage-6', name: 'Entretien de potager', baseRate: 27 }
  ]
}

const materialsByService = {
  menage: [
    'Aspirateur',
    'Balai et serpillière',
    'Produits de nettoyage',
    'Chiffons et éponges',
    'Seau',
    'Nettoyeur vapeur'
  ],
  jardinage: [
    'Tondeuse',
    'Taille-haie',
    'Râteau',
    'Pelle et bêche',
    'Arrosoir',
    'Sécateur'
  ]
}

const serviceStates = ref({
  menage: false,
  jardinage: false
})

const taskStates = ref({})
const taskPrices = ref({})
const taskDescriptions = ref({})
const taskMaterials = ref({})

const activeServicesCount = computed(() => {
  return Object.values(serviceStates.value).filter(Boolean).length
})

const activeTasksCount = computed(() => {
  return Object.values(taskStates.value).filter(Boolean).length
})

const toggleService = (serviceId, serviceName) => {
  // If activating the service, show the activation request modal
  if (!serviceStates.value[serviceId]) {
    const service = services.find(s => s.id === serviceId)
    if (service) {
      currentService.value = service
      showActivationModal.value = true
    }
  } else {
    // Deactivating - just toggle off
    serviceStates.value[serviceId] = false
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
    experience: 0,
    idCard: null,
    insurance: null,
    additionalDocs: []
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

const handleFileUpload = (event, field) => {
  const target = event.target
  const file = target.files?.[0]
  
  if (file) {
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Le fichier est trop volumineux. Taille maximale : 5MB')
      return
    }
    
    activationForm.value[field] = file
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
    
    activationForm.value.additionalDocs.push({
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
  activationForm.value.additionalDocs.splice(index, 1)
}

const submitActivationRequest = async () => {
  // Validate that all additional documents have types specified
  const hasEmptyTypes = activationForm.value.additionalDocs.some(doc => !doc.type.trim())
  if (hasEmptyTypes) {
    alert('Veuillez spécifier le type pour tous les documents supplémentaires')
    return
  }

  // TODO: Send the activation request to the backend
  // For now, we'll just simulate a successful submission
  try {
    console.log('Activation request data:', {
      service: currentService.value,
      presentation: activationForm.value.presentation,
      experience: activationForm.value.experience,
      idCard: activationForm.value.idCard?.name,
      insurance: activationForm.value.insurance?.name,
      additionalDocs: activationForm.value.additionalDocs.map(doc => ({
        type: doc.type,
        fileName: doc.file.name
      }))
    })

    // Here you would normally send to backend:
    // const formData = new FormData()
    // formData.append('serviceId', currentService.value.id)
    // formData.append('presentation', activationForm.value.presentation)
    // formData.append('experience', String(activationForm.value.experience))
    // formData.append('idCard', activationForm.value.idCard)
    // formData.append('insurance', activationForm.value.insurance)
    // activationForm.value.additionalDocs.forEach((doc, index) => {
    //   formData.append(`additionalDocs[${index}][type]`, doc.type)
    //   formData.append(`additionalDocs[${index}][file]`, doc.file)
    // })
    // await axios.post('/api/service-activation-requests', formData)

    // Show success message
    showActivationModal.value = false
    showSuccessMessage.value = true
    
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 4000)
    
    resetActivationForm()
  } catch (error) {
    console.error('Error submitting activation request:', error)
    alert('Une erreur est survenue lors de l\'envoi de la demande')
  }
}

const toggleTask = (taskId) => {
  taskStates.value[taskId] = !taskStates.value[taskId]
  
  if (!taskStates.value[taskId]) {
    delete taskPrices.value[taskId]
    delete taskDescriptions.value[taskId]
    delete taskMaterials.value[taskId]
  }
}

const toggleMaterial = (taskId, material) => {
  if (!taskMaterials.value[taskId]) {
    taskMaterials.value[taskId] = []
  }
  
  const index = taskMaterials.value[taskId].indexOf(material)
  if (index > -1) {
    taskMaterials.value[taskId].splice(index, 1)
  } else {
    taskMaterials.value[taskId].push(material)
  }
}

const getServiceColor = (serviceId) => {
  return services.find(s => s.id === serviceId)?.color || '#92B08B'
}

const getServiceName = (serviceId) => {
  return services.find(s => s.id === serviceId)?.name || ''
}

const getTasksByService = (serviceId) => {
  return tasksByService[serviceId] || []
}

const getMaterialsByService = (serviceId) => {
  return materialsByService[serviceId] || []
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

.service-active {
  /* Dynamic styles applied inline */
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
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 0.875rem;
}

.checkbox-label:hover {
  background-color: var(--color-gray-50);
}

.checkbox-label input {
  width: 1rem;
  height: 1rem;
  cursor: pointer;
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
</style>
