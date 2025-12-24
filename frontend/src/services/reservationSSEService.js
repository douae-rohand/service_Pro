/**
 * Service SSE pour les notifications temps réel des réservations
 */
class ReservationSSEService {
  constructor() {
    this.eventSource = null
    this.listeners = new Map()
    this.reconnectAttempts = 0
    this.maxReconnectAttempts = 5
    this.reconnectDelay = 3000
  }

  /**
   * Connecter au flux SSE des réservations
   */
  connect(intervenantId) {
    if (this.eventSource) {
      this.disconnect()
    }

    const url = `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'}/api/reservations/stream?intervenant_id=${intervenantId}`
    
    this.eventSource = new EventSource(url)

    this.eventSource.onopen = () => {
      console.log('SSE connecté pour les réservations')
      this.reconnectAttempts = 0
    }

    this.eventSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data)
        this.notifyListeners('reservation', data)
      } catch (error) {
        console.error('Erreur parsing SSE data:', error)
      }
    }

    this.eventSource.onerror = (error) => {
      console.error('Erreur SSE:', error)
      this.handleReconnect(intervenantId)
    }

    // Écouter les événements spécifiques
    this.eventSource.addEventListener('new_reservation', (event) => {
      try {
        const data = JSON.parse(event.data)
        this.notifyListeners('new_reservation', data)
      } catch (error) {
        console.error('Erreur parsing new_reservation:', error)
      }
    })

    this.eventSource.addEventListener('status_update', (event) => {
      try {
        const data = JSON.parse(event.data)
        this.notifyListeners('status_update', data)
      } catch (error) {
        console.error('Erreur parsing status_update:', error)
      }
    })
  }

  /**
   * Gérer la reconnexion automatique
   */
  handleReconnect(intervenantId) {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++
      console.log(`Tentative de reconnexion SSE ${this.reconnectAttempts}/${this.maxReconnectAttempts}`)
      
      setTimeout(() => {
        this.connect(intervenantId)
      }, this.reconnectDelay * this.reconnectAttempts)
    } else {
      console.error('Max tentatives de reconnexion SSE atteintes')
    }
  }

  /**
   * Ajouter un écouteur d'événement
   */
  addListener(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, [])
    }
    this.listeners.get(event).push(callback)
  }

  /**
   * Retirer un écouteur d'événement
   */
  removeListener(event, callback) {
    if (this.listeners.has(event)) {
      const callbacks = this.listeners.get(event)
      const index = callbacks.indexOf(callback)
      if (index > -1) {
        callbacks.splice(index, 1)
      }
    }
  }

  /**
   * Notifier tous les écouteurs d'un événement
   */
  notifyListeners(event, data) {
    if (this.listeners.has(event)) {
      this.listeners.get(event).forEach(callback => {
        try {
          callback(data)
        } catch (error) {
          console.error(`Erreur dans l'écouteur SSE pour ${event}:`, error)
        }
      })
    }
  }

  /**
   * Déconnecter le flux SSE
   */
  disconnect() {
    if (this.eventSource) {
      this.eventSource.close()
      this.eventSource = null
      this.reconnectAttempts = 0
    }
  }

  /**
   * Vérifier si la connexion est active
   */
  isConnected() {
    return this.eventSource && this.eventSource.readyState === EventSource.OPEN
  }
}

export default new ReservationSSEService()
