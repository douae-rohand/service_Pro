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
        <img :src="intervenant.profileImage" :alt="formData.name" class="avatar" />
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
import { ref } from 'vue'
import { Edit2, Check, X, MapPin, Phone, Mail, Calendar } from 'lucide-vue-next'

const intervenant = ref({
  id: 1,
  name: 'Amina Chakir',
  email: 'amina.chakir@email.com',
  phone: '+212 6 12 34 56 78',
  profileImage: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop',
  location: 'Tetouan Centre',
  memberSince: '2019'
})

const isEditing = ref(false)
const showSuccessMessage = ref(false)

const formData = ref({
  name: intervenant.value.name,
  email: intervenant.value.email,
  phone: intervenant.value.phone,
  location: intervenant.value.location,
  bio: 'Professionnelle du ménage avec plus de 5 ans d\'expérience. Spécialisée dans le nettoyage en profondeur et l\'entretien régulier des espaces résidentiels.',
  experience: '5 ans',
  languages: 'Arabe, Français'
})

const startEdit = () => {
  isEditing.value = true
}

const saveEdit = () => {
  isEditing.value = false
  showSuccessMessage.value = true
  setTimeout(() => {
    showSuccessMessage.value = false
  }, 3000)
}

const cancelEdit = () => {
  formData.value = {
    name: intervenant.value.name,
    email: intervenant.value.email,
    phone: intervenant.value.phone,
    location: intervenant.value.location,
    bio: 'Professionnelle du ménage avec plus de 5 ans d\'expérience. Spécialisée dans le nettoyage en profondeur et l\'entretien régulier des espaces résidentiels.',
    experience: '5 ans',
    languages: 'Arabe, Français'
  }
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
