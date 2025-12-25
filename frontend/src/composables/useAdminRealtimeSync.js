import { ref, onMounted, onUnmounted, watch } from 'vue'

/**
 * Composable pour la synchronisation en temps réel dans l'espace admin
 * Utilise un polling (interrogation périodique) toutes les 3 secondes
 * 
 * @param {Function} loadFunction - Fonction à appeler pour charger les données
 * @param {Object} options - Options de configuration
 */
export function useAdminRealtimeSync(loadFunction, options = {}) {
  const {
    interval = 3000,
    enabled = true,
    loadOnMount = true
  } = options

  const isActive = ref(enabled)
  let intervalId = null

  const loadData = async () => {
    if (!isActive.value) return

    try {
      if (typeof loadFunction === 'function') {
        // Mode silencieux pour ne pas afficher de spinner à chaque fois
        await loadFunction({ silent: true })
      }
    } catch (error) {
      console.error('Erreur sync admin:', error)
    }
  }

  const start = () => {
    isActive.value = true
    if (intervalId) return

    // Charger immédiatement une première fois
    loadData()

    // Lancer le cycle
    intervalId = setInterval(loadData, interval)
  }

  const stop = () => {
    isActive.value = false
    if (intervalId) {
      clearInterval(intervalId)
      intervalId = null
    }
  }

  // Lifecycle
  onMounted(() => {
    if (loadOnMount) {
      // Premier chargement officiel (avec potentiellement un spinner si la fonction le gère)
      if (typeof loadFunction === 'function') {
        loadFunction()
      }
    }

    if (isActive.value) {
      start()
    }
  })

  onUnmounted(() => {
    stop()
  })

  // Watcher pour activer/désactiver dynamiquement
  watch(() => options.enabled, (newVal) => {
    isActive.value = newVal
    if (newVal) start()
    else stop()
  })

  return {
    isActive,
    start,
    stop
  }
}

