import { ref } from 'vue'

/**
 * Récupère la couleur stockée pour un service depuis localStorage
 */
const getStoredServiceColor = (serviceId) => {
  try {
    const storedColors = JSON.parse(localStorage.getItem('serviceColors') || '{}')
    return storedColors[serviceId] || null
  } catch (e) {
    return null
  }
}

/**
 * Récupère toutes les couleurs stockées
 */
const getAllStoredColors = () => {
  try {
    return JSON.parse(localStorage.getItem('serviceColors') || '{}')
  } catch (e) {
    return {}
  }
}

/**
 * Obtient les couleurs personnalisées pour certains services (Jardinage, Ménage)
 * @param {string} serviceName - Nom du service
 * @returns {Object|null} - Objet avec les couleurs ou null
 */
const getServiceColors = (serviceName) => {
  if (!serviceName) return null
  const serviceNameLower = String(serviceName).toLowerCase().trim()

  // Détection pour Jardinage (insensible à la casse et aux espaces)
  if (serviceNameLower === 'jardinage' || serviceNameLower.includes('jardinage')) {
    return {
      primary: '#86af88',      // Vert clair pour texte et éléments
      header: '#d7e4d2',      // Vert clair pour header
      button: '#86af88',      // Vert clair pour boutons
      light: '#d7e4d2',       // Vert clair pour fonds
      accent: '#86af88',      // Accent principal en vert clair
      badgeBg: '#E8F5E9',    // Fond clair pour badges
      badgeText: '#92B08B'   // Texte pour badges
    }
  }

  // Détection pour Ménage (insensible à la casse et aux espaces)
  if (serviceNameLower === 'ménage' || serviceNameLower.includes('ménage') || serviceNameLower === 'menage' || serviceNameLower.includes('menage')) {
    return {
      primary: '#67839b',      // Bleu gris pour texte et éléments
      header: '#b8c5d1',      // Bleu gris clair pour header
      button: '#67839b',      // Bleu gris pour boutons
      light: '#b8c5d1',       // Bleu gris clair pour fonds
      accent: '#67839b',      // Accent principal en bleu gris
      badgeBg: '#E3F2FD',    // Fond clair pour badges
      badgeText: '#1A5FA3'   // Texte pour badges
    }
  }

  // Couleurs par défaut (utiliser la couleur du service)
  return null
}

/**
 * Composable pour obtenir la couleur d'un service
 * @param {string} serviceName - Nom du service
 * @param {number|string} serviceId - ID du service (optionnel, pour récupérer depuis localStorage)
 * @param {Array} servicesList - Liste des services chargés (optionnel)
 * @returns {Object} - Objet avec les couleurs pour le service
 */
export function useServiceColor() {
  /**
   * Obtient la couleur principale d'un service
   * PRIORITÉ : 1. Couleur stockée en BDD/localStorage 2. Couleurs par défaut (Jardinage/Ménage) 3. Couleur par défaut
   * @param {string} serviceName - Nom du service
   * @param {number|string} serviceId - ID du service (optionnel)
   * @param {Array} servicesList - Liste des services chargés (optionnel)
   * @returns {string} - Couleur hexadécimale
   */
  const getServiceColor = (serviceName, serviceId = null, servicesList = []) => {
    if (!serviceName) return '#5B7C99'

    // PRIORITÉ 1 : Chercher d'abord la couleur stockée dans la base de données/localStorage
    // Si un serviceId est fourni, chercher dans la liste des services
    if (serviceId && servicesList && servicesList.length > 0) {
      const service = servicesList.find(s => s.id === serviceId || s.id === String(serviceId))
      if (service && service.couleur && service.couleur !== '#808080') {
        // Utiliser la couleur du service si elle existe et n'est pas la couleur par défaut
        return service.couleur
      }
    }

    // Chercher par nom dans la liste des services (support nom_service et nom pour compatibilité)
    if (servicesList && servicesList.length > 0) {
      const service = servicesList.find(s => {
        const serviceNom = s.nom_service || s.nom
        return serviceNom && serviceNom.toLowerCase().trim() === serviceName.toLowerCase().trim()
      })
      if (service && service.couleur && service.couleur !== '#808080') {
        // Utiliser la couleur du service si elle existe et n'est pas la couleur par défaut
        return service.couleur
      }
    }

    // Chercher dans localStorage si serviceId est fourni
    if (serviceId) {
      const storedColor = getStoredServiceColor(serviceId)
      if (storedColor && storedColor !== '#808080') {
        return storedColor
      }
    }

    // PRIORITÉ 2 : Si aucune couleur stockée, utiliser les couleurs par défaut pour Jardinage/Ménage
    const customColors = getServiceColors(serviceName)
    if (customColors) {
      return customColors.primary
    }

    // PRIORITÉ 3 : Couleur par défaut
    return '#5B7C99'
  }

  /**
   * Obtient les couleurs pour un badge/étiquette de service
   * PRIORITÉ : 1. Couleur stockée en BDD 2. Couleurs par défaut (Jardinage/Ménage) 3. Génération automatique
   * @param {string} serviceName - Nom du service
   * @param {number|string} serviceId - ID du service (optionnel)
   * @param {Array} servicesList - Liste des services chargés (optionnel)
   * @returns {Object} - Objet avec backgroundColor et color
   */
  const getServiceBadgeColors = (serviceName, serviceId = null, servicesList = []) => {
    if (!serviceName) {
      return {
        backgroundColor: '#E3F2FD',
        color: '#1A5FA3'
      }
    }

    // PRIORITÉ 1 : Vérifier si le service a une couleur stockée
    let serviceData = null
    if (serviceId && servicesList && servicesList.length > 0) {
      serviceData = servicesList.find(s => s.id === serviceId || s.id === String(serviceId))
    }
    if (!serviceData && servicesList && servicesList.length > 0) {
      serviceData = servicesList.find(s => {
        const serviceNom = s.nom_service || s.nom
        return serviceNom && serviceNom.toLowerCase().trim() === serviceName.toLowerCase().trim()
      })
    }

    // Si le service a une couleur stockée, l'utiliser
    if (serviceData && serviceData.couleur && serviceData.couleur !== '#808080') {
      const primaryColor = serviceData.couleur
      return {
        backgroundColor: `${primaryColor}20`, // 20% d'opacité pour le fond
        color: primaryColor
      }
    }

    // Chercher dans localStorage si serviceId est fourni
    if (serviceId) {
      const storedColor = getStoredServiceColor(serviceId)
      if (storedColor && storedColor !== '#808080') {
        return {
          backgroundColor: `${storedColor}20`,
          color: storedColor
        }
      }
    }

    // PRIORITÉ 2 : Si aucune couleur stockée, utiliser les couleurs par défaut pour Jardinage/Ménage
    const customColors = getServiceColors(serviceName)
    if (customColors) {
      return {
        backgroundColor: customColors.badgeBg,
        color: customColors.badgeText
      }
    }

    // PRIORITÉ 3 : Générer des couleurs de badge basées sur la couleur principale
    const primaryColor = getServiceColor(serviceName, serviceId, servicesList)
    return {
      backgroundColor: `${primaryColor}20`, // 20% d'opacité pour le fond
      color: primaryColor
    }
  }

  /**
   * Obtient la couleur de fond claire pour un service
   * PRIORITÉ : 1. Couleur stockée en BDD 2. Couleurs par défaut (Jardinage/Ménage) 3. Génération automatique
   * @param {string} serviceName - Nom du service
   * @param {number|string} serviceId - ID du service (optionnel)
   * @param {Array} servicesList - Liste des services chargés (optionnel)
   * @returns {string} - Couleur hexadécimale avec opacité
   */
  const getServiceLightColor = (serviceName, serviceId = null, servicesList = []) => {
    // PRIORITÉ 1 : Vérifier si le service a une couleur stockée
    let serviceData = null
    if (serviceId && servicesList && servicesList.length > 0) {
      serviceData = servicesList.find(s => s.id === serviceId || s.id === String(serviceId))
    }
    if (!serviceData && servicesList && servicesList.length > 0) {
      serviceData = servicesList.find(s => {
        const serviceNom = s.nom_service || s.nom
        return serviceNom && serviceNom.toLowerCase().trim() === serviceName.toLowerCase().trim()
      })
    }

    if (serviceData && serviceData.couleur && serviceData.couleur !== '#808080') {
      return `${serviceData.couleur}20` // 20% d'opacité
    }

    // Chercher dans localStorage si serviceId est fourni
    if (serviceId) {
      const storedColor = getStoredServiceColor(serviceId)
      if (storedColor && storedColor !== '#808080') {
        return `${storedColor}20`
      }
    }

    // PRIORITÉ 2 : Si aucune couleur stockée, utiliser les couleurs par défaut pour Jardinage/Ménage
    const customColors = getServiceColors(serviceName)
    if (customColors) {
      return customColors.light
    }

    // PRIORITÉ 3 : Génération automatique
    const primaryColor = getServiceColor(serviceName, serviceId, servicesList)
    return `${primaryColor}20` // 20% d'opacité
  }

  /**
   * Obtient toutes les couleurs d'un service (pour usage avancé)
   * PRIORITÉ : 1. Couleur stockée en BDD 2. Couleurs par défaut (Jardinage/Ménage) 3. Génération automatique
   * @param {string} serviceName - Nom du service
   * @param {number|string} serviceId - ID du service (optionnel)
   * @param {Array} servicesList - Liste des services chargés (optionnel)
   * @returns {Object} - Objet avec toutes les couleurs
   */
  const getAllServiceColors = (serviceName, serviceId = null, servicesList = []) => {
    // Obtenir la couleur principale (qui respecte déjà la priorité)
    const primaryColor = getServiceColor(serviceName, serviceId, servicesList)

    // Vérifier si c'est une couleur par défaut (Jardinage/Ménage)
    const customColors = getServiceColors(serviceName)

    // Si le service a une couleur stockée différente des couleurs par défaut, l'utiliser
    let serviceData = null
    if (serviceId && servicesList && servicesList.length > 0) {
      serviceData = servicesList.find(s => s.id === serviceId || s.id === String(serviceId))
    }
    if (!serviceData && servicesList && servicesList.length > 0) {
      serviceData = servicesList.find(s => {
        const serviceNom = s.nom_service || s.nom
        return serviceNom && serviceNom.toLowerCase().trim() === serviceName.toLowerCase().trim()
      })
    }

    // Si le service a une couleur stockée, utiliser cette couleur pour tous les usages
    if (serviceData && serviceData.couleur && serviceData.couleur !== '#808080') {
      const storedColor = serviceData.couleur
      return {
        primary: storedColor,
        header: storedColor,
        button: storedColor,
        light: `${storedColor}20`,
        accent: storedColor,
        badgeBg: `${storedColor}20`,
        badgeText: storedColor
      }
    }

    // Si c'est une couleur par défaut (Jardinage/Ménage), utiliser les couleurs personnalisées
    if (customColors) {
      return customColors
    }

    // Sinon, générer à partir de la couleur principale
    return {
      primary: primaryColor,
      header: primaryColor,
      button: primaryColor,
      light: `${primaryColor}20`,
      accent: primaryColor,
      badgeBg: `${primaryColor}20`,
      badgeText: primaryColor
    }
  }

  return {
    getServiceColor,
    getServiceBadgeColors,
    getServiceLightColor,
    getAllServiceColors,
    getServiceColors,
    getStoredServiceColor,
    getAllStoredColors
  }
}

