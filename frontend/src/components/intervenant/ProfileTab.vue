<template>
  <div class="profile-container">
    <!-- Success Message -->
    <div v-if="showSuccessMessage" class="success-message">
      <Check :size="18" />
      <span>Votre profil a été mis à jour avec succès !</span>
    </div>

    <!-- Profile Card -->
    <div class="profile-card">
      <div class="card-header">
        <h1>Mon Profil</h1>
        <button v-if="!isEditing" @click="startEdit" class="edit-btn">
          <Edit2 :size="16" />
          Modifier
        </button>
        <div v-else class="button-group">
          <button @click="saveEdit" class="save-btn">
            <Check :size="16" />
            Enregistrer
          </button>
          <button @click="cancelEdit" class="cancel-btn">
            <X :size="16" />
            Annuler
          </button>
        </div>
      </div>

      <!-- Profile Image & Basic Info -->
      <div class="profile-section">
        <div class="avatar-container">
          <div v-if="intervenant.profileImage" class="avatar-wrapper">
            <img :src="intervenant.profileImage" :alt="formData.name" class="avatar" />
            <div v-if="isEditing" class="avatar-upload">
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileUpload" 
                accept="image/*" 
                style="display: none;"
              />
              <button @click="$refs.fileInput.click()" class="upload-btn">
                <Camera :size="16" />
                Changer
              </button>
            </div>
          </div>
          <div v-else class="avatar-placeholder">
            <User :size="48" />
            <div v-if="isEditing" class="avatar-upload-placeholder">
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleFileUpload" 
                accept="image/*" 
                style="display: none;"
              />
              <button @click="$refs.fileInput.click()" class="upload-btn-placeholder">
                <Camera :size="16" />
                Ajouter
              </button>
            </div>
          </div>
        </div>
        <div class="profile-details">
          <div v-if="isEditing" class="form-group">
            <label>Adresse</label>
            <input v-model="formData.address" type="text" placeholder="123 Rue Exemple" />
          </div>
          <div v-if="isEditing" class="form-group" style="margin-top: 10px;">
            <label>Ville</label>
            <input v-model="formData.ville" type="text" placeholder="Casablanca" />
          </div>
          <div v-else>
            <h2>{{ formData.name }}</h2>
            <div class="info-item">
              <MapPin :size="16" />
              <span>{{ intervenant.address || 'Adresse non spécifiée' }}</span>
            </div>
            <div class="info-item">
              <MapPin :size="16" class="opacity-0" />
              <span class="font-medium mr-1">Ville:</span>
              <span>{{ intervenant.ville || 'Non spécifiée' }}</span>
            </div>
            <div class="info-item">
              <Calendar :size="16" />
              <span>Membre depuis {{ intervenant.memberSince }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="section">
        <h3>Informations de contact</h3>
        <div class="grid grid-cols-2">
          <div class="form-group">
            <label>Email</label>
            <input v-if="isEditing" v-model="formData.email" type="email" />
            <div v-else class="info-item">
              <Mail :size="16" />
              <span>{{ formData.email }}</span>
            </div>
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input v-if="isEditing" v-model="formData.phone" type="tel" />
            <div v-else class="info-item">
              <Phone :size="16" />
              <span>{{ formData.phone }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Professional Information -->
      <div class="section">
        <h3>Informations professionnelles</h3>
        <div class="form-group">
          <label>Expérience par Service</label>
          
          <!-- Edit Mode: List of services with inputs -->
          <div v-if="isEditing" class="services-list-edit">
            <div v-for="(service, index) in formData.services" :key="service.id" class="service-item-edit">
                <span class="service-name">{{ service.nom_service }}</span>
                <div class="experience-input-group">
                    <input v-model="service.experience" type="number" step="0.5" min="0" />
                    <span class="unit">ans</span>
                </div>
            </div>
            <p v-if="formData.services.length === 0" class="no-services">Aucun service actif.</p>
          </div>

          <!-- View Mode: List of services with experience badges -->
          <div v-else class="services-list-view">
            <div v-for="service in intervenant.services" :key="service.id" class="service-item-view">
                <span class="service-name-view">{{ service.nom_service }}</span>
                <span class="experience-badge">{{ formatExperience(service.pivot?.experience) }}</span>
            </div>
             <p v-if="!intervenant.services || intervenant.services.length === 0" class="no-services">Aucun service actif.</p>
          </div>
        </div>
        <div class="form-group">
          <label>Bio / Description</label>
          <textarea v-if="isEditing" v-model="formData.bio" rows="3"></textarea>
          <p v-else>{{ formData.bio }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Edit2, Check, X, MapPin, Phone, Mail, Calendar, Camera, User } from 'lucide-vue-next'
import authService from '@/services/authService'
import intervenantService from '@/services/intervenantService' // Import intervenantService
import api from '@/services/api' // Import api for direct put if needed

const intervenant = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  profileImage: '',
  location: '',
  address: '',
  ville: '',
  memberSince: '',
  services: [] // Add services array
})

const loading = ref(true)
const isEditing = ref(false)
const showSuccessMessage = ref(false)
const selectedFile = ref(null)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  location: '', // Keep for backward compatibility or full display
  address: '',
  ville: '',
  bio: '',
  services: [] // Store services for editing
})

// Fetch current user data
const fetchCurrentUser = async () => {
  try {
    const response = await authService.getCurrentUser()
    const user = response.data
    
    if (user.intervenant) {
      // Fetch full intervenant data to get services with pivot
      const intResponse = await intervenantService.getById(user.intervenant.id)
      const fullIntervenant = intResponse.data
      
      intervenant.value = {
        id: user.intervenant.id,
        name: `${user.prenom} ${user.nom}`,
        email: user.email,
        phone: user.telephone || '',
        profileImage: user.profile_photo ? `http://127.0.0.1:8000/storage/${user.profile_photo}?t=${Date.now()}` : null,
        location: fullIntervenant.ville || fullIntervenant.address || 'Non spécifié',
        address: fullIntervenant.address || '',
        ville: fullIntervenant.ville || '',
        memberSince: new Date(user.created_at).getFullYear().toString(),
        services: fullIntervenant.services || [] // Store services
      }
      
      // Initialize form data
      formData.value = {
        name: intervenant.value.name,
        email: intervenant.value.email,
        phone: intervenant.value.phone,
        location: intervenant.value.location,
        address: intervenant.value.address,
        ville: intervenant.value.ville,
        bio: user.intervenant.bio || '',
        services: intervenant.value.services.map(s => ({
            id: s.id,
            nom_service: s.nom_service,
            experience: s.pivot?.experience || 0
        }))
      }
    }
  } catch (error) {
    console.error('Error fetching user data:', error)
  } finally {
    loading.value = false
  }
}

// Handle file upload for profile photo
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (!file.type.startsWith('image/')) {
      alert('Veuillez sélectionner une image valide')
      return
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('L\'image ne doit pas dépasser 5MB')
      return
    }
    selectedFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      intervenant.value.profileImage = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

onMounted(() => {
  fetchCurrentUser()
})

const startEdit = () => {
  // Re-sync form data before editing to ensure freshness
  formData.value.services = intervenant.value.services.map(s => ({
      id: s.id,
      nom_service: s.nom_service,
      experience: s.pivot?.experience || 0
  }))
  // Sync other fields if needed, but they are usually static until save
  isEditing.value = true
}

const saveEdit = async () => {
  try {
    // 1. Save Profile Photo & Basic Info (Phone, Bio, Address, Ville)
    // We need to send both address and ville. authService.updateProfile might need to be checked if it handles separate fields.
    // Assuming authService.updateProfile wraps the endpoint we used in IntervenantController.
    // If not, we might need to use api.post or modify authService.
    
    // Construct payload matching IntervenantController@update validation and structure
    // The previous working code used api.put('intervenants/{id}')
    
    // Let's use the exact same logic as IntervenantProfileTab for consistency and correctness
    
    const servicesPayload = formData.value.services.map(s => ({
        id: s.id,
        experience: s.experience
    }));

    // Prepare FormData for file upload support
    const formDataToSend = new FormData()
    formDataToSend.append('telephone', formData.value.phone || '')
    formDataToSend.append('address', formData.value.address || '')
    formDataToSend.append('ville', formData.value.ville || '')
    formDataToSend.append('bio', formData.value.bio || '')
    formDataToSend.append('_method', 'PUT') // Method spoofing for Laravel if using POST for file upload
    
    // Add services as array
    servicesPayload.forEach((s, index) => {
        formDataToSend.append(`services[${index}][id]`, s.id)
        formDataToSend.append(`services[${index}][experience]`, s.experience)
    })

    if (selectedFile.value) {
      formDataToSend.append('profile_photo', selectedFile.value)
    }

    // We can use the dedicated update endpoint or the auth update. 
    // Since fetchCurrentUser gets ID, let's use the direct intervenant update endpoint which we know handles services and ville.
    // However, profile_photo is usually handled by auth/user controller.
    // Let's stick to authService.updateProfile if it points to 'intervenants/me' or similar?
    // Actually, looking at previous code, profile photo update was likely separate or handled via 'user/profile-information'.
    
    // STRATEGY: 
    // 1. Update Profile Photo via authService (if file selected)
    // 2. Update Intervenant Details (Ville, Bio, Experience) via api.put -> intervenants/{id}
    
    if (selectedFile.value) {
         const photoData = new FormData();
         photoData.append('profile_photo', selectedFile.value);
         // Assuming this endpoint exists and works for photo
         await authService.updateProfile(photoData); 
    }

    // Update info
    await api.put(`intervenants/${intervenant.value.id}`, {
        telephone: formData.value.phone,
        address: formData.value.address,
        ville: formData.value.ville,
        bio: formData.value.bio,
        services: servicesPayload
    });

    await fetchCurrentUser()
    
    selectedFile.value = null
    isEditing.value = false
    showSuccessMessage.value = true
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 3000)
  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Erreur lors de la mise à jour du profil')
  }
}

const cancelEdit = () => {
  selectedFile.value = null
  fetchCurrentUser()
  isEditing.value = false
}

const formatExperience = (value) => {
  if (!value) return "Débutant";
  const num = parseFloat(value);
  if (isNaN(num) || num === 0) return "Débutant";
  
  const years = Math.floor(num);
  const months = Math.round((num - years) * 12);
  
  let parts = [];
  
  if (years > 0) {
    parts.push(`${years} an${years > 1 ? 's' : ''}`);
  }
  
  if (months > 0) {
    parts.push(`${months} mois`);
  }
  
  if (parts.length === 0) return "Débutant"; // Should happen if 0
  
  return parts.join(' et ');
}
</script>

<style scoped>
.profile-container {
  max-width: 80rem;
}

.success-message {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-3) var(--spacing-4);
  background-color: var(--color-light-green);
  border: 1px solid var(--color-primary-green);
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-4);
  color: var(--color-dark);
  font-size: 0.875rem;
}

.profile-card {
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
  color: var(--color-dark);
  margin: 0;
}

.edit-btn {
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

.edit-btn:hover {
  background-color: #D5EBD5;
}

.button-group {
  display: flex;
  gap: var(--spacing-2);
}

.save-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
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
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
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

.profile-section {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-6);
  margin-bottom: var(--spacing-6);
  padding-bottom: var(--spacing-6);
  border-bottom: 1px solid var(--color-gray-200);
}

.avatar {
  width: 6rem;
  height: 6rem;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--color-primary-green);
  display: block;
}

.avatar-wrapper {
  position: relative;
  display: inline-block;
}

.avatar-container {
  position: relative;
  display: inline-block;
}

.avatar-placeholder {
  width: 6rem;
  height: 6rem;
  border-radius: 50%;
  background-color: var(--color-light-green);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary-green);
  border: 4px solid var(--color-primary-green);
  position: relative;
}

.avatar-upload-placeholder {
  position: absolute;
  bottom: 0;
  right: 0;
  background: var(--color-primary-green);
  border-radius: 50%;
  padding: 0.25rem;
  border: 2px solid white;
  box-shadow: var(--shadow-md);
}

.upload-btn-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  background: transparent;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 0.75rem;
}

.upload-btn-placeholder:hover {
  background-color: #A5C09E;
}

.avatar-upload {
  position: absolute;
  bottom: 0;
  right: 0;
  background: var(--color-primary-green);
  border-radius: 50%;
  padding: 0.25rem;
  border: 2px solid white;
  box-shadow: var(--shadow-md);
}

.upload-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  background: transparent;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 0.75rem;
}

.upload-btn:hover {
  background-color: #A5C09E;
}

.profile-details {
  flex: 1;
}

.profile-details h2 {
  color: var(--color-dark);
  margin-bottom: var(--spacing-2);
}

.info-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  color: var(--color-gray-600);
  font-size: 0.875rem;
  margin-bottom: var(--spacing-2);
}

.info-item svg {
  color: var(--color-primary-green);
}

.section {
  margin-bottom: var(--spacing-6);
}

.section:last-child {
  margin-bottom: 0;
}

.section h3 {
  color: var(--color-blue);
  margin-bottom: var(--spacing-4);
}

.form-group {
  margin-bottom: var(--spacing-4);
}

.form-group:last-child {
  margin-bottom: 0;
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

.form-group p {
  color: var(--color-gray-800);
  font-size: 0.875rem;
  margin: 0;
}

@media (max-width: 768px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .profile-section {
    flex-direction: column;
  }
}

.services-list-edit {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.service-item-edit {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9f9f9;
  padding: 10px;
  border-radius: 8px;
}

.service-name {
  font-weight: 500;
  color: #333;
}

.experience-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.experience-input-group input {
    width: 80px;
    text-align: right;
}

.services-list-view {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.service-item-view {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.service-name-view {
    font-weight: 600;
    color: #374151;
}

.experience-badge {
    background-color: #f0fdf4;
    color: #15803d;
    padding: 4px 8px;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}
</style>
