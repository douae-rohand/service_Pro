/**
 * Service SSE pour les notifications temps réel de l'administration
 */
class AdminSSEService {
    constructor() {
        this.eventSource = null
        this.listeners = new Map()
        this.reconnectAttempts = 0
        this.maxReconnectAttempts = 5
        this.reconnectDelay = 3000
        this.url = `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'}/api/admin/stream`
    }

    /**
     * Connecter au flux SSE Admin
     */
    connect() {
        if (this.eventSource) {
            if (this.eventSource.readyState === EventSource.OPEN) {
                console.log('SSE Admin déjà connecté')
                return
            }
            this.disconnect()
        }

        console.log('Connexion SSE Admin...', this.url)

        // Utiliser withCredentials si nécessaire pour les cookies sanctum
        this.eventSource = new EventSource(this.url, { withCredentials: true })

        this.eventSource.onopen = () => {
            console.log('SSE Admin connecté')
            this.reconnectAttempts = 0
            this.notifyListeners('connected', true)
        }

        this.eventSource.onmessage = (event) => {
            // Message générique
            try {
                const data = JSON.parse(event.data)
                this.notifyListeners('message', data)
            } catch (error) {
                // Ignorer les pings simples ou erreurs de format
            }
        }

        this.eventSource.onerror = (error) => {
            console.error('Erreur SSE Admin:', error)
            this.notifyListeners('error', error)
            this.handleReconnect()
        }

        // Écouter les mises à jour de stats
        this.eventSource.addEventListener('stats_update', (event) => {
            try {
                const data = JSON.parse(event.data)
                console.log('SSE Stats Update reçue', data)
                this.notifyListeners('stats_update', data)
            } catch (error) {
                console.error('Erreur parsing stats_update:', error)
            }
        })

        this.eventSource.addEventListener('ping', () => {
            // Ping reçu, connexion active
        })
    }

    /**
     * Gérer la reconnexion automatique
     */
    handleReconnect() {
        if (this.eventSource) {
            this.eventSource.close()
            this.eventSource = null
        }

        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++
            console.log(`Tentative de reconnexion SSE Admin ${this.reconnectAttempts}/${this.maxReconnectAttempts}`)

            setTimeout(() => {
                this.connect()
            }, this.reconnectDelay * this.reconnectAttempts)
        } else {
            console.error('Max tentatives de reconnexion SSE Admin atteintes')
            this.notifyListeners('disconnected', true)
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
                    console.error(`Erreur dans l'écouteur SSE Admin pour ${event}:`, error)
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
            this.notifyListeners('disconnected', true)
            console.log('SSE Admin déconnecté')
        }
    }

    /**
     * Vérifier si la connexion est active
     */
    isConnected() {
        return this.eventSource && this.eventSource.readyState === EventSource.OPEN
    }
}

export default new AdminSSEService()
