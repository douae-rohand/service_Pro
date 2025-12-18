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
            <label>Nom complet</label>
            <input v-model="formData.name" type="text" />
          </div>
          <div v-else>
            <h2>{{ formData.name }}</h2>
            <div class="info-item">
              <MapPin :size="16" />
              <span>{{ formData.location }}</span>
            </div>
            <div class="info-item">
              <Calendar :size="16" />
              <span>Membre depuis {{ intervenant.memberSince }}</span>
            </div>
          </div>
          
          <div v-if="isEditing" class="form-group">
            <label>Localisation</label>
            <input v-model="formData.location" type="text" />
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
          <label>Expérience</label>
          <input v-if="isEditing" v-model="formData.experience" type="text" />
          <p v-else>{{ formData.experience }}</p>
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

const intervenant = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  profileImage: '',
  location: '',
  memberSince: ''
})

const loading = ref(true)
const isEditing = ref(false)
const showSuccessMessage = ref(false)
const selectedFile = ref(null)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  location: '',
  bio: '',
  experience: '',
  languages: ''
})

// Fetch current user data
const fetchCurrentUser = async () => {
  try {
    const response = await authService.getCurrentUser()
    const user = response.data.user
    
    console.log('User data from backend:', user)
    console.log('Profile photo path:', user.profile_photo)
    
    if (user.intervenant) {
      intervenant.value = {
        id: user.intervenant.id,
        name: `${user.prenom} ${user.nom}`,
        email: user.email,
        phone: user.telephone || '',
        profileImage: user.profile_photo ? `http://127.0.0.1:8000/storage/${user.profile_photo}?t=${Date.now()}` : null,
        location: user.address || 'Non spécifié',
        memberSince: new Date(user.created_at).getFullYear().toString()
      }
      
      console.log('Final profile image URL:', intervenant.value.profileImage)
      
      // Initialize form data with real values
      formData.value = {
        name: intervenant.value.name,
        email: intervenant.value.email,
        phone: intervenant.value.phone,
        location: intervenant.value.location,
        bio: user.intervenant.bio || '',
        experience: '', // You might want to add this to the database
        languages: '' // You might want to add this to the database
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
    // Check if file is an image
    if (!file.type.startsWith('image/')) {
      alert('Veuillez sélectionner une image valide')
      return
    }
    
    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('L\'image ne doit pas dépasser 5MB')
      return
    }
    
    selectedFile.value = file
    
    // Create a preview
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
  isEditing.value = true
}

const saveEdit = async () => {
  try {
    // Debug: Log the form data values
    console.log('FormData values being sent:', {
      phone: formData.value.phone,
      location: formData.value.location,
      bio: formData.value.bio,
      bioType: typeof formData.value.bio,
      bioValue: formData.value.bio
    })
    
    let response
    
    // Use FormData only if there's a file, otherwise use JSON
    if (selectedFile.value) {
      const formDataToSend = new FormData()
      
      // Add text fields
      formDataToSend.append('telephone', formData.value.phone || '')
      formDataToSend.append('address', formData.value.location || '')
      formDataToSend.append('bio', formData.value.bio || '')
      
      // Add profile photo
      formDataToSend.append('profile_photo', selectedFile.value)
      
      // Debug: Log FormData contents
      console.log('FormData entries:')
      for (let [key, value] of formDataToSend.entries()) {
        console.log(`${key}:`, value, typeof value)
      }
      
      response = await authService.updateProfile(formDataToSend)
    } else {
      // Send as JSON when no file
      const jsonData = {
        telephone: formData.value.phone || '',
        address: formData.value.location || '',
        bio: formData.value.bio || ''
      }
      
      console.log('JSON data being sent:', jsonData)
      
      response = await authService.updateProfile(jsonData)
    }
    
    // Refresh user data to get updated profile
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
  formData.value = {
    name: intervenant.value.name,
    email: intervenant.value.email,
    phone: intervenant.value.phone,
    location: intervenant.value.location,
    bio: intervenant.value.bio || '',
    experience: formData.value.experience,
    languages: formData.value.languages
  }
  selectedFile.value = null
  // Reset profile image to original
  fetchCurrentUser()
  isEditing.value = false
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
</style>
