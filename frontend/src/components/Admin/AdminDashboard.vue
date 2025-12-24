<template>
  <div class="min-h-screen" style="background: linear-gradient(135deg, #F0F9F4 0%, #E8F5F9 50%, #FFF9E6 100%)">
    <!-- Header -->
    <header class="bg-white sticky top-0 z-40" style="box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08)">
      <div class="max-w-7xl mx-auto px-8 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <img src="@/assets/logo.png" alt="Logo" class="h-16 w-auto" />
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
      <!-- Loading State (minimal) -->
      <div v-if="loading && activeSection === 'overview'" class="text-center py-4">
        <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
      </div>

      <!-- Overview Section -->
        <template v-if="activeSection === 'overview'">
          <AdminOverview 
            v-if="!loading"
            :stats="stats" 
            :loading="loading"
            @navigate="handleNavigate" 
          />
        </template>

      <!-- Clients Section -->
      <template v-if="activeSection === 'clients'">
        <AdminClients 
          @back="activeSection = 'overview'"
          @view-client="viewClient"
          @suspend-client="suspendClient"
        />
      </template>

      <!-- Intervenants Section -->
      <template v-if="activeSection === 'intervenants'">
        <AdminIntervenants
          @back="activeSection = 'overview'"
          @view-intervenant="viewIntervenant"
          @suspend-intervenant="suspendIntervenant"
        />
      </template>

      <!-- Demandes Section -->
      <template v-if="activeSection === 'demandes'">
        <AdminDemandes
          :loading="loading"
          @back="activeSection = 'overview'"
          @stats-updated="loadStats"
        />
      </template>

      <!-- Services Section -->
      <template v-if="activeSection === 'services'">
        <AdminServices
          @back="activeSection = 'overview'"
          @demandes-updated="handleDemandesUpdated"
        />
      </template>

      <!-- Réclamations Section -->
      <template v-if="activeSection === 'reclamations'">
        <AdminReclamations
          @back="activeSection = 'overview'"
          @view-profile="handleViewProfileFromReclamation"
        />
      </template>

      <!-- Historique Section -->
      <template v-if="activeSection === 'historique'">
        <AdminHistorique
          @back="activeSection = 'overview'"
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
      v-if="showIntervenantProfile && selectedIntervenant"
      :is-open="showIntervenantProfile"
      :intervenant="selectedIntervenant"
      @close="showIntervenantProfile = false"
      @suspend="handleSuspendIntervenant"
    />

    <!-- Client Details Modal -->
    <AdminClientDetails
      v-if="showClientDetails && selectedClient"
      :is-open="showClientDetails"
      :client="selectedClient"
      @close="showClientDetails = false"
      @suspend="handleSuspendClient"
      @activate="handleActivateClient"
    />

    <!-- Profile Modal -->
    <AdminProfileModal
      v-if="showProfileModal && profileModalData"
      :show="showProfileModal"
      :type="profileModalType"
      :data="profileModalData"
      @close="showProfileModal = false"
      @toggle-status="handleToggleStatus"
    />

    <!-- Notification Container -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
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
import AdminProfileModal from './AdminProfileModal.vue'
import AdminIntervenantProfile from './AdminIntervenantProfile.vue'
import AdminClientDetails from './AdminClientDetails.vue'
import NotificationContainer from './NotificationContainer.vue'
import { useNotifications } from '@/composables/useNotifications'
import { useAdminRealtimeSync } from '@/composables/useAdminRealtimeSync'

const emit = defineEmits(['logout'])
const { success, error, confirm: confirmDialog } = useNotifications()

// State
const activeSection = ref('overview')
const loading = ref(false)
const showDetailModal = ref(false)
const detailModalType = ref('')
const detailModalData = ref(null)
const showProfileModal = ref(false)
const profileModalType = ref('')
const profileModalData = ref(null)
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



// Methods
const loadStats = async (options = {}) => {
  const { silent = false } = options
  try {
    const response = await adminService.getStats()
    // Mise à jour silencieuse : ne mettre à jour que si les valeurs ont changé
    const newStats = response.data
    if (silent) {
      // Comparer et mettre à jour seulement les changements
      Object.keys(newStats).forEach(key => {
        if (stats.value[key] !== newStats[key]) {
          stats.value[key] = newStats[key]
        }
      })
    } else {
      stats.value = newStats
    }
  } catch (error) {
    console.error('Erreur chargement stats:', error)
    if (!silent) {
      // Données par défaut si l'API échoue (seulement si ce n'est pas une mise à jour silencieuse)
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
  const confirmed = await confirmDialog('Voulez-vous vraiment changer le statut de ce client ?')
  if (confirmed) {
    try {
      const response = await adminService.toggleClientStatus(clientId)
      success(response.data.message)
      success(response.data.message)
      await loadStats()
    } catch (err) {
      console.error('Erreur changement statut client:', err)
      error('Erreur lors du changement de statut du client')
    }
  }
}

const handleSuspendClient = async (client) => {
  const confirmed = await confirmDialog(`Voulez-vous vraiment suspendre le compte de ${client.prenom} ${client.nom} ?`)
  if (confirmed) {
    try {
      const response = await adminService.toggleClientStatus(client.id)
      success(response.data.message)
      showClientDetails.value = false
      showClientDetails.value = false
      await loadStats()
    } catch (err) {
      console.error('Erreur suspension client:', err)
      error('Erreur lors de la suspension du client')
    }
  }
}

const handleActivateClient = async (client) => {
  const confirmed = await confirmDialog(`Voulez-vous vraiment activer le compte de ${client.prenom} ${client.nom} ?`)
  if (confirmed) {
    try {
      const response = await adminService.toggleClientStatus(client.id)
      success(response.data.message)
      showClientDetails.value = false
      showClientDetails.value = false
      await loadStats()
    } catch (err) {
      console.error('Erreur activation client:', err)
      error('Erreur lors de l\'activation du client')
    }
  }
}

const viewIntervenant = (intervenant) => {
  selectedIntervenant.value = intervenant
  showIntervenantProfile.value = true
}

const handleSuspendIntervenant = async (intervenantId) => {
  const confirmed = await confirmDialog('Voulez-vous vraiment changer le statut de cet intervenant ?')
  if (confirmed) {
    try {
      const response = await adminService.toggleIntervenantStatus(intervenantId)
      success(response.data.message)
      showIntervenantProfile.value = false
      showIntervenantProfile.value = false
      await loadStats()
    } catch (err) {
      console.error('Erreur changement statut intervenant:', err)
      error('Erreur lors du changement de statut de l\'intervenant')
    }
  }
}

const suspendIntervenant = async (intervenantId) => {
  await handleSuspendIntervenant(intervenantId)
}

const handleModalAction = (action) => {
  showDetailModal.value = false
}

const viewIntervenantProfile = (intervenant) => {
  profileModalType.value = 'intervenant'
  profileModalData.value = intervenant
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

// Gérer l'ouverture des profils depuis les réclamations
const handleViewProfileFromReclamation = async ({ id, type }) => {
  try {
    // Normaliser le type (peut être "Client", "Intervenant" ou en minuscules)
    const normalizedType = type ? type.toLowerCase() : ''
    
    if (normalizedType === 'client') {
      // Charger les détails complets du client
      const response = await adminService.getClientDetails(id)
      selectedClient.value = response.data
      showClientDetails.value = true
    } else if (normalizedType === 'intervenant') {
      // Charger les détails complets de l'intervenant via l'endpoint admin
      const response = await adminService.getIntervenant(id)
      selectedIntervenant.value = response.data
      showIntervenantProfile.value = true
    } else {
      console.error('Type de profil non reconnu:', type)
      error(`Type de profil non reconnu: ${type}. ID: ${id}`)
    }
  } catch (err) {
    console.error('Erreur chargement profil:', err)
    console.error('Détails:', { id, type, error: err.response?.data || err.message })
    error(`Erreur lors du chargement du profil: ${err.response?.data?.message || err.message}`)
  }
}

const handleLogoutHover = (e) => {
  e.currentTarget.style.backgroundColor = '#FF5252'
  e.currentTarget.style.transform = 'scale(1.05)'
}

const handleLogoutLeave = (e) => {
  e.currentTarget.style.backgroundColor = '#FF6B6B'
  e.currentTarget.style.transform = 'scale(1)'
}

// Handler pour rafraîchir les demandes après désactivation/réactivation d'un service
const handleDemandesUpdated = async () => {
  // Rafraîchir les stats pour mettre à jour le nombre de demandes en attente
  await loadStats()
  
  // Si la section demandes est active, on peut aussi émettre un événement pour la rafraîchir
  // Mais AdminDemandes se rafraîchit déjà automatiquement via loadDemandes()
  // donc pas besoin de faire quelque chose de spécial ici
}

// Navigation handler - Charger les données appropriées lors du changement de section
const handleNavigate = async (section) => {
  activeSection.value = section
}

// Synchronisation en temps réel - SSE
// Synchronisation en temps réel - SSE (Maintenant Polling)
// Écoute les mises à jour globales des stats
const { start: startStatsSync, stop: stopStatsSync } = useAdminRealtimeSync(
  async () => {
    // Polling simple : on recharge les stats complètes
    await loadStats({ silent: true })
  },
  { enabled: true }
)



// Synchronisation pour les demandes (si section active)
// Note: On peut ajouter ceci si on veut que la liste des demandes se rafraîchisse aussi
const { start: startDemandesSync, stop: stopDemandesSync } = useAdminRealtimeSync(
  async (data) => {
    if (!data || data.demandesEnAttente !== stats.value.demandesEnAttente) {
      // AdminDemandes a sa propre logique, mais on pourrait trigger un event global
      // Ici on laisse AdminDemandes gérer son état, ou on pourrait émettre un event global bus
      // Pour l'instant on se contente de rafraîchir les stats qui sont passées en props
    }
  },
  { enabled: false }
)

// Démarrer/arrêter la synchronisation selon la section active
watch(activeSection, (newSection) => {
  // Stats sync est toujours actif (défini plus haut avec enabled: true)
  
  if (newSection === 'demandes') {
    startDemandesSync()
  } else {
    stopDemandesSync()
  }
}, { immediate: true })

// Lifecycle - Charger les stats au démarrage
onMounted(async () => {
  loading.value = true
  try {
    await loadStats()
    // Le SSE démarre automatiquement via le composable statsSync (enabled: true)
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

