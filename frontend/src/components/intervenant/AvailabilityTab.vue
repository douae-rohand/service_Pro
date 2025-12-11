<template>
  <div class="container">
    <!-- Success Message -->
    <div v-if="showSuccessMessage" class="success-message">
      <div class="success-icon">
        <Check />
      </div>
      <span>Vos disponibilités ont été enregistrées avec succès !</span>
    </div>

    <!-- Regular Availability -->
    <div class="card card-primary">
      <h1 class="card-title">Disponibilités Régulières</h1>
      <p class="card-subtitle">Définissez vos horaires de travail pour chaque jour de la semaine</p>

      <div class="days-list">
        <div
          v-for="(day, index) in regularAvailability"
          :key="day.day"
          class="day-item"
          :class="{ 'day-available': day.available }"
        >
          <button
            @click="toggleDay(index)"
            class="day-toggle"
            :class="{ 'day-toggle-active': day.available }"
          >
            <span class="day-name">{{ day.day }}</span>
            <Check v-if="day.available" :size="18" />
          </button>

          <div v-if="day.available" class="time-inputs">
            <div class="time-group">
              <label>De</label>
              <input
                v-model="day.startTime"
                type="time"
              />
            </div>
            <span class="time-separator">à</span>
            <div class="time-group">
              <label>À</label>
              <input
                v-model="day.endTime"
                type="time"
              />
            </div>
          </div>
          <span v-else class="unavailable-text">Non disponible</span>
        </div>
      </div>

      <button @click="saveRegularAvailability" class="save-btn">
        Enregistrer les disponibilités régulières
      </button>
    </div>

    <!-- Special Availability -->
    <div class="card card-secondary">
      <div class="card-header">
        <div>
          <h1 class="card-title">Disponibilités Ponctuelles</h1>
          <p class="card-subtitle">Ajoutez des exceptions à vos horaires réguliers (jours fériés, absences, etc.)</p>
        </div>
        <button @click="showAddSpecial = !showAddSpecial" class="add-btn">
          <Plus :size="20" />
          Ajouter
        </button>
      </div>

      <!-- Add Special Form -->
      <div v-if="showAddSpecial" class="add-form">
        <h3>Nouvelle disponibilité ponctuelle</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>Date</label>
            <input v-model="newSpecial.date" type="date" />
          </div>
          <div class="form-group">
            <label>Statut</label>
            <select v-model="newSpecial.available">
              <option :value="true">Disponible</option>
              <option :value="false">Non disponible</option>
            </select>
          </div>
        </div>

        <div v-if="newSpecial.available" class="form-grid">
          <div class="form-group">
            <label>Heure de début</label>
            <input v-model="newSpecial.startTime" type="time" />
          </div>
          <div class="form-group">
            <label>Heure de fin</label>
            <input v-model="newSpecial.endTime" type="time" />
          </div>
        </div>

        <div class="form-group">
          <label>Raison (optionnel)</label>
          <input
            v-model="newSpecial.reason"
            type="text"
            placeholder="Ex: Jour férié, rendez-vous médical..."
          />
        </div>

        <div class="button-group">
          <button @click="addSpecialAvailability" :disabled="!newSpecial.date" class="accept-btn">
            Ajouter cette disponibilité
          </button>
          <button @click="showAddSpecial = false" class="cancel-btn">
            Annuler
          </button>
        </div>
      </div>

      <!-- Special Availability List -->
      <div class="special-list">
        <div
          v-for="special in specialAvailability"
          :key="special.id"
          class="special-item"
          :class="special.available ? 'special-available' : 'special-unavailable'"
        >
          <div class="special-content">
            <Calendar
              :size="24"
              :class="special.available ? 'icon-blue' : 'icon-red'"
            />
            <div>
              <p class="special-date">{{ formatDate(special.date) }}</p>
              <p v-if="special.available && special.startTime && special.endTime" class="special-time">
                {{ special.startTime }} - {{ special.endTime }}
              </p>
              <p v-else class="special-unavailable-text">Non disponible</p>
              <p v-if="special.reason" class="special-reason">{{ special.reason }}</p>
            </div>
          </div>
          <button @click="deleteSpecialAvailability(special.id)" class="delete-btn">
            <Trash2 :size="20" />
          </button>
        </div>
      </div>

      <div v-if="specialAvailability.length === 0 && !showAddSpecial" class="empty-state">
        <p>Aucune disponibilité ponctuelle configurée</p>
        <p class="text-sm">Cliquez sur "Ajouter" pour en créer une</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Plus, Trash2, Check, Calendar } from 'lucide-vue-next'

const showSuccessMessage = ref(false)
const showAddSpecial = ref(false)

const regularAvailability = ref([
  { day: 'Lundi', available: true, startTime: '09:00', endTime: '17:00' },
  { day: 'Mardi', available: true, startTime: '09:00', endTime: '17:00' },
  { day: 'Mercredi', available: true, startTime: '09:00', endTime: '17:00' },
  { day: 'Jeudi', available: true, startTime: '09:00', endTime: '17:00' },
  { day: 'Vendredi', available: true, startTime: '09:00', endTime: '17:00' },
  { day: 'Samedi', available: true, startTime: '09:00', endTime: '17:00' },
  { day: 'Dimanche', available: false, startTime: '09:00', endTime: '17:00' }
])

const specialAvailability = ref([
  {
    id: 1,
    date: '2024-12-25',
    available: false,
    reason: 'Jour férié'
  },
  {
    id: 2,
    date: '2024-12-15',
    available: true,
    startTime: '14:00',
    endTime: '18:00',
    reason: 'Disponibilité exceptionnelle'
  }
])

const newSpecial = ref({
  date: '',
  available: true,
  startTime: '09:00',
  endTime: '17:00',
  reason: ''
})

const toggleDay = (index) => {
  regularAvailability.value[index].available = !regularAvailability.value[index].available
}

const saveRegularAvailability = () => {
  showSuccessMessage.value = true
  setTimeout(() => {
    showSuccessMessage.value = false
  }, 3000)
}

const addSpecialAvailability = () => {
  if (!newSpecial.value.date) return

  const newItem = {
    id: Date.now(),
    date: newSpecial.value.date,
    available: newSpecial.value.available,
    startTime: newSpecial.value.available ? newSpecial.value.startTime : undefined,
    endTime: newSpecial.value.available ? newSpecial.value.endTime : undefined,
    reason: newSpecial.value.reason
  }

  specialAvailability.value.push(newItem)
  specialAvailability.value.sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())

  // Reset form
  newSpecial.value = {
    date: '',
    available: true,
    startTime: '09:00',
    endTime: '17:00',
    reason: ''
  }
  showAddSpecial.value = false
}

const deleteSpecialAvailability = (id) => {
  const index = specialAvailability.value.findIndex(s => s.id === id)
  if (index > -1) {
    specialAvailability.value.splice(index, 1)
  }
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-6);
}

.success-message {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-4);
  background-color: var(--color-light-green);
  border: 2px solid var(--color-primary-green);
  border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-lg);
  font-size: 1.125rem;
  color: var(--color-dark);
}

.success-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: var(--color-primary-green);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-white);
}

.card {
  background: var(--color-white);
  border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-xl);
  padding: var(--spacing-8);
  border: 4px solid;
}

.card-primary {
  border-color: var(--color-teal);
}

.card-secondary {
  border-color: var(--color-yellow);
}

.card-title {
  font-size: 2rem;
  color: var(--color-blue);
  margin: 0 0 var(--spacing-2) 0;
}

.card-primary .card-title {
  color: var(--color-blue);
}

.card-secondary .card-title {
  color: var(--color-orange);
}

.card-subtitle {
  color: var(--color-gray-600);
  margin: 0 0 var(--spacing-8) 0;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--spacing-8);
}

.add-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
  padding: var(--spacing-3) var(--spacing-6);
  background-color: var(--color-blue);
  color: var(--color-white);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
  transition: all 0.2s;
}

.add-btn:hover {
  background-color: #2574C7;
}

.days-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.day-item {
  border: 3px solid var(--color-gray-200);
  border-radius: var(--radius-2xl);
  padding: var(--spacing-6);
  box-shadow: var(--shadow-lg);
  transition: all 0.3s;
}

.day-available {
  border-color: var(--color-primary-green);
  background-color: var(--color-light-green);
}

.day-toggle {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-3) var(--spacing-6);
  border-radius: var(--radius-xl);
  background-color: var(--color-gray-200);
  color: var(--color-gray-600);
  box-shadow: var(--shadow-md);
  transition: all 0.2s;
  margin-bottom: var(--spacing-4);
}

.day-toggle-active {
  background-color: var(--color-primary-green);
  color: var(--color-white);
}

.day-name {
  width: 8rem;
  text-align: left;
  font-size: 1.125rem;
}

.time-inputs {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
}

.time-group {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.time-group label {
  font-size: 0.875rem;
  color: var(--color-gray-600);
}

.time-group input {
  padding: var(--spacing-2) var(--spacing-4);
  border: 2px solid var(--color-teal);
  border-radius: var(--radius-xl);
}

.time-separator {
  color: var(--color-gray-400);
}

.unavailable-text {
  color: var(--color-gray-500);
  font-size: 1.125rem;
}

.save-btn {
  width: 100%;
  padding: var(--spacing-4) var(--spacing-8);
  background-color: var(--color-primary-green);
  color: var(--color-white);
  border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-lg);
  font-size: 1rem;
  transition: all 0.2s;
}

.save-btn:hover {
  background-color: #A5C09E;
  transform: scale(1.02);
}

.add-form {
  border: 3px solid var(--color-teal);
  border-radius: var(--radius-2xl);
  padding: var(--spacing-6);
  background-color: #F0F9FF;
  box-shadow: var(--shadow-lg);
  margin-bottom: var(--spacing-6);
}

.add-form h3 {
  font-size: 1.5rem;
  color: var(--color-blue);
  margin: 0 0 var(--spacing-4) 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-4);
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
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin-bottom: var(--spacing-2);
}

.form-group input,
.form-group select {
  width: 100%;
  padding: var(--spacing-2) var(--spacing-4);
}

.button-group {
  display: flex;
  gap: var(--spacing-2);
}

.accept-btn {
  flex: 1;
  padding: var(--spacing-2) var(--spacing-6);
  background-color: #16A34A;
  color: var(--color-white);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.accept-btn:hover {
  background-color: #15803D;
}

.accept-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cancel-btn {
  flex: 1;
  padding: var(--spacing-2) var(--spacing-6);
  background-color: var(--color-gray-200);
  color: var(--color-gray-700);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.cancel-btn:hover {
  background-color: var(--color-gray-300);
}

.special-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-4);
}

.special-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border: 2px solid;
  border-radius: var(--radius-xl);
  padding: var(--spacing-6);
}

.special-available {
  border-color: #3B82F6;
  background-color: #DBEAFE;
}

.special-unavailable {
  border-color: #DC2626;
  background-color: #FEE2E2;
}

.special-content {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
}

.icon-blue {
  color: #3B82F6;
}

.icon-red {
  color: #DC2626;
}

.special-date {
  font-size: 1.125rem;
  margin: 0 0 var(--spacing-1) 0;
  color: var(--color-dark);
}

.special-time {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin: 0;
}

.special-unavailable-text {
  font-size: 0.875rem;
  color: #DC2626;
  margin: 0;
}

.special-reason {
  font-size: 0.875rem;
  color: var(--color-gray-500);
  margin: var(--spacing-1) 0 0 0;
}

.delete-btn {
  padding: var(--spacing-2);
  border-radius: var(--radius-lg);
  color: #DC2626;
  transition: background-color 0.2s;
}

.delete-btn:hover {
  background-color: var(--color-white);
}

.empty-state {
  text-align: center;
  padding: var(--spacing-12) var(--spacing-6);
  color: var(--color-gray-500);
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .time-inputs {
    flex-direction: column;
    align-items: flex-start;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-4);
  }
}
</style>
