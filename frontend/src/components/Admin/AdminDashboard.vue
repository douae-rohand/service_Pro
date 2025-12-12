<template>
  <div class="min-h-screen" style="background: linear-gradient(135deg, #F0F9F4 0%, #E8F5F9 50%, #FFF9E6 100%)">
    <!-- Header -->
    <header class="bg-white sticky top-0 z-40" style="box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08)">
      <div class="max-w-7xl mx-auto px-8 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: #92B08B">
              <Sparkles :size="24" color="white" />
            </div>
            <div>
              <h1 class="text-2xl" style="color: #2F4F4F">ServicePro</h1>
              <p class="text-sm text-gray-500">Espace administrateur</p>
            </div>
          </div>
          <button
            @click="$emit('logout')"
            class="px-5 py-2 rounded-lg transition-all text-sm hover:shadow-lg text-white"
            style="background-color: #FF6B6B; border: 2px solid #FF6B6B"
            @mouseenter="handleLogoutHover"
            @mouseleave="handleLogoutLeave"
          >
            Déconnexion
          </button>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-8 py-8">
      <!-- Overview Section -->
        <template v-if="activeSection === 'overview'">
          <AdminOverview 
            :stats="stats" 
            :loading="loading"
            @navigate="handleNavigate" 
          />
        </template>

      <!-- Clients Section -->
      <template v-if="activeSection === 'clients'">
        <AdminClients 
          :clients="clients"
          :loading="loading"
          @back="activeSection = 'overview'"
          @view-client="viewClient"
          @suspend-client="suspendClient"
          @view-all="activeSection = 'all-clients'"
        />
      </template>

      <!-- Intervenants Section -->
      <template v-if="activeSection === 'intervenants'">
        <AdminIntervenants
          :intervenants="intervenants"
          :loading="loading"
          @back="activeSection = 'overview'"
          @view-intervenant="viewIntervenant"
          @suspend-intervenant="suspendIntervenant"
          @view-all="activeSection = 'all-intervenants'"
        />
      </template>

      <!-- Demandes Section -->
      <template v-if="activeSection === 'demandes'">
        <AdminDemandes
          @back="activeSection = 'overview'"
        />
      </template>

      <!-- Services Section -->
      <template v-if="activeSection === 'services'">
        <AdminServices
          @back="activeSection = 'overview'"
        />
      </template>

      <!-- Réclamations Section -->
      <template v-if="activeSection === 'reclamations'">
        <AdminReclamations
          @back="activeSection = 'overview'"
        />
      </template>

      <!-- Historique Section -->
      <template v-if="activeSection === 'historique'">
        <AdminHistorique
          @back="activeSection = 'overview'"
        />
      </template>

      <!-- All Intervenants Section (avec filtres avancés) -->
      <template v-if="activeSection === 'all-intervenants'">
        <AdminAllIntervenants
          :service="selectedService"
          @back="activeSection = 'overview'"
          @view-profile="viewIntervenantProfile"
        />
      </template>

      <!-- All Clients Section (avec filtres avancés) -->
      <template v-if="activeSection === 'all-clients'">
        <AdminAllClients
          @back="activeSection = 'overview'"
          @view-profile="viewClientProfile"
        />
      </template>
    </div>

    <!-- Detail Modal -->
    <AdminDetailModal
      v-if="showDetailModal"
      :type="detailModalType"
      :data="detailModalData"
      @close="showDetailModal = false"
      @action="handleModalAction"
    />

    <!-- Intervenant Profile Modal -->
    <AdminIntervenantProfile
      :is-open="showIntervenantProfile"
      :intervenant="selectedIntervenant"
      @close="showIntervenantProfile = false"
      @suspend="handleSuspendIntervenant"
    />

    <!-- Client Details Modal -->
    <AdminClientDetails
      :is-open="showClientDetails"
      :client="selectedClient"
      @close="showClientDetails = false"
      @suspend="handleSuspendClient"
      @activate="handleActivateClient"
    />

    <!-- Profile Modal -->
    <AdminProfileModal
      :show="showProfileModal"
      :type="profileModalType"
      :data="profileModalData"
      @close="showProfileModal = false"
      @toggle-status="handleToggleStatus"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Sparkles } from 'lucide-vue-next'
import adminService from '@/services/adminService'

// Sub-components
import AdminOverview from './AdminOverview.vue'
import AdminClients from './AdminClients.vue'
import AdminIntervenants from './AdminIntervenants.vue'
import AdminDemandes from './AdminDemandes.vue'
import AdminServices from './AdminServices.vue'
import AdminHistorique from './AdminHistorique.vue'
import AdminReclamations from './AdminReclamations.vue'
import AdminDetailModal from './AdminDetailModal.vue'
import AdminAllIntervenants from './AdminAllIntervenants.vue'
import AdminAllClients from './AdminAllClients.vue'
import AdminProfileModal from './AdminProfileModal.vue'
import AdminIntervenantProfile from './AdminIntervenantProfile.vue'
import AdminClientDetails from './AdminClientDetails.vue'

const emit = defineEmits(['logout'])

// State
const activeSection = ref('overview')
const loading = ref(false)
const showDetailModal = ref(false)
const detailModalType = ref('')
const detailModalData = ref(null)
const showProfileModal = ref(false)
const profileModalType = ref('')
const profileModalData = ref(null)
const selectedService = ref('tous')
const showIntervenantProfile = ref(false)
const selectedIntervenant = ref(null)
const showClientDetails = ref(false)
const selectedClient = ref(null)

// Data
const stats = ref({
  totalClients: 0,
  clientsGrowth: '+0%',
  totalIntervenants: 0,
  intervenantsGrowth: '+0%',
  interventionsMois: 0,
  interventionsGrowth: '+0%',
  satisfaction: 0,
  satisfactionLabel: '0/5',
  heuresTotal: 0,
  heuresGrowth: '+0%',
  demandesEnAttente: 0,
  reclamationsNouvelles: 0
})

const clients = ref([])
const intervenants = ref([])

// Methods
const loadStats = async () => {
  try {
    const response = await adminService.getStats()
    stats.value = response.data
  } catch (error) {
    console.error('Erreur chargement stats:', error)
    // Données par défaut si l'API échoue
    stats.value = {
      totalClients: 0,
      clientsGrowth: '+0%',
      totalIntervenants: 0,
      intervenantsGrowth: '+0%',
      interventionsMois: 0,
      interventionsGrowth: '+0%',
      satisfaction: 0,
      satisfactionLabel: '0/5',
      heuresTotal: 0,
      heuresGrowth: '+0%',
      demandesEnAttente: 0,
      reclamationsNouvelles: 0
    }
  }
}

const loadClients = async () => {
  try {
    loading.value = true
    const response = await adminService.getClients()
    clients.value = response.data || []
  } catch (error) {
    console.error('Erreur chargement clients:', error)
    clients.value = []
  } finally {
    loading.value = false
  }
}

const loadIntervenants = async () => {
  try {
    loading.value = true
    const response = await adminService.getIntervenants()
    intervenants.value = response.data || []
  } catch (error) {
    console.error('Erreur chargement intervenants:', error)
    intervenants.value = []
  } finally {
    loading.value = false
  }
}


// Actions
const viewClient = async (client) => {
  try {
    // Charger les détails complets du client avec feedbacks
    const response = await adminService.getClientDetails(client.id)
    selectedClient.value = response.data
    showClientDetails.value = true
  } catch (error) {
    console.error('Erreur chargement détails client:', error)
    // En cas d'erreur, utiliser les données de base
    selectedClient.value = client
    showClientDetails.value = true
  }
}

const suspendClient = async (clientId) => {
  if (confirm('Voulez-vous vraiment changer le statut de ce client ?')) {
    try {
      const response = await adminService.toggleClientStatus(clientId)
      alert(response.data.message)
      await loadClients()
      await loadStats()
    } catch (error) {
      console.error('Erreur changement statut client:', error)
      alert('Erreur lors du changement de statut du client')
    }
  }
}

const handleSuspendClient = async (client) => {
  if (confirm(`Voulez-vous vraiment suspendre le compte de ${client.prenom} ${client.nom} ?`)) {
    try {
      const response = await adminService.toggleClientStatus(client.id)
      alert(response.data.message)
      showClientDetails.value = false
      await loadClients()
      await loadStats()
    } catch (error) {
      console.error('Erreur suspension client:', error)
      alert('Erreur lors de la suspension du client')
    }
  }
}

const handleActivateClient = async (client) => {
  if (confirm(`Voulez-vous vraiment activer le compte de ${client.prenom} ${client.nom} ?`)) {
    try {
      const response = await adminService.toggleClientStatus(client.id)
      alert(response.data.message)
      showClientDetails.value = false
      await loadClients()
      await loadStats()
    } catch (error) {
      console.error('Erreur activation client:', error)
      alert('Erreur lors de l\'activation du client')
    }
  }
}

const viewIntervenant = (intervenant) => {
  selectedIntervenant.value = intervenant
  showIntervenantProfile.value = true
}

const handleSuspendIntervenant = async (intervenantId) => {
  if (confirm('Voulez-vous vraiment changer le statut de cet intervenant ?')) {
    try {
      const response = await adminService.toggleIntervenantStatus(intervenantId)
      alert(response.data.message)
      showIntervenantProfile.value = false
      await loadIntervenants()
      await loadStats()
    } catch (error) {
      console.error('Erreur changement statut intervenant:', error)
      alert('Erreur lors du changement de statut de l\'intervenant')
    }
  }
}

const suspendIntervenant = async (intervenantId) => {
  await handleSuspendIntervenant(intervenantId)
}

const handleModalAction = (action) => {
  console.log('Modal action:', action)
  showDetailModal.value = false
}

const viewIntervenantProfile = (intervenant) => {
  profileModalType.value = 'intervenant'
  profileModalData.value = intervenant
  showProfileModal.value = true
}

const viewClientProfile = (client) => {
  profileModalType.value = 'client'
  profileModalData.value = client
  showProfileModal.value = true
}

const handleToggleStatus = async (id) => {
  if (profileModalType.value === 'intervenant') {
    await suspendIntervenant(id)
  } else {
    await suspendClient(id)
  }
  showProfileModal.value = false
}

const handleLogoutHover = (e) => {
  e.currentTarget.style.backgroundColor = '#FF5252'
  e.currentTarget.style.transform = 'scale(1.05)'
}

const handleLogoutLeave = (e) => {
  e.currentTarget.style.backgroundColor = '#FF6B6B'
  e.currentTarget.style.transform = 'scale(1)'
}

// Navigation handler - Charger les données appropriées lors du changement de section
const handleNavigate = async (section) => {
  activeSection.value = section
  
  // Charger les données correspondantes à la section
  switch (section) {
    case 'clients':
      await loadClients()
      break
    case 'intervenants':
      await loadIntervenants()
      break
  }
}

// Lifecycle - Charger les stats au démarrage
onMounted(async () => {
  loading.value = true
  try {
    await loadStats()
  } catch (error) {
    console.error('Erreur initialisation:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

