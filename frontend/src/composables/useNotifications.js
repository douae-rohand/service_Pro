import { ref } from 'vue'

// État global des notifications
const notifications = ref([])
let notificationId = 0

/**
 * Composable pour gérer les notifications dans toute l'application
 */
export function useNotifications() {
  /**
   * Ajouter une notification
   * @param {string} message - Message à afficher
   * @param {string} type - Type de notification: 'success', 'error', 'info', 'warning'
   * @param {number} duration - Durée en ms (0 = pas de fermeture auto)
   * @param {string} position - Position: 'top-right', 'top-left', etc.
   * @returns {number} ID de la notification
   */
  const notify = (message, type = 'info', duration = 4000, position = 'top-right') => {
    const id = ++notificationId
    notifications.value.push({
      id,
      message,
      type,
      duration,
      position
    })
    return id
  }

  /**
   * Notification de succès
   */
  const success = (message, duration = 4000) => {
    return notify(message, 'success', duration)
  }

  /**
   * Notification d'erreur
   */
  const error = (message, duration = 5000) => {
    return notify(message, 'error', duration)
  }

  /**
   * Notification d'information
   */
  const info = (message, duration = 4000) => {
    return notify(message, 'info', duration)
  }

  /**
   * Notification d'avertissement
   */
  const warning = (message, duration = 4000) => {
    return notify(message, 'warning', duration)
  }

  /**
   * Supprimer une notification
   */
  const remove = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  /**
   * Confirmation avec modal personnalisé
   * @param {string} message - Message de confirmation
   * @param {string} title - Titre (optionnel)
   * @returns {Promise<boolean>}
   */
  const confirm = (message, title = 'Confirmation') => {
    return new Promise((resolve) => {
      const id = ++notificationId
      notifications.value.push({
        id,
        message,
        type: 'confirm',
        title,
        duration: 0, // Pas de fermeture auto
        position: 'top-center',
        resolve,
        show: true
      })
    })
  }

  return {
    notifications,
    notify,
    success,
    error,
    info,
    warning,
    remove,
    confirm
  }
}
