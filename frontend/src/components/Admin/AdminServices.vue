<template>
  <div>
    <!-- Services List View -->
    <div v-if="!showStatsModal">
      <!-- Back Button -->
      <button
        @click="$emit('back')"
        class="flex items-center gap-2 mb-6 text-sm transition-colors hover:underline"
        style="color: #5B7C99"
      >
        ← Retour au tableau de bord
      </button>

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl" style="color: #2F4F4F">Gestion des Services</h2>
        <button
          @click="openAddServiceModal"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium transition-all hover:shadow-md"
          style="background-color: #7FB3D3"
        >
          <Plus :size="18" />
          Ajouter un Service
        </button>
      </div>

      <!-- Loading State (minimal) -->
      <div v-if="loading" class="text-center py-4">
        <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
      </div>

      <!-- Services Grid -->
      <div v-else-if="services.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div
        v-for="service in paginatedServices"
        :key="service.id"
        class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all"
      >
        <!-- Cover Image -->
        <div class="relative h-48 overflow-hidden">
          <div
            class="w-full h-full flex items-center justify-center"
            :style="{ background: `linear-gradient(135deg, ${service.couleur}20, ${service.couleur}40)` }"
          >
            <div class="text-center text-white">
              <Package :size="48" class="mx-auto mb-2 opacity-80" />
              <p class="text-lg font-semibold">{{ service.nom_service || service.nom }}</p>
            </div>
          </div>
          <div
            class="absolute inset-0"
            :style="{
              background: `linear-gradient(to bottom, transparent 0%, ${service.couleur}15 100%)`
            }"
          />
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl" :style="{ color: service.couleur }">{{ service.nom_service || service.nom }}</h3>

            <!-- Switch Button -->
            <button
              @click="toggleService(service)"
              class="relative w-14 h-7 rounded-full transition-all"
              :style="{
                backgroundColor: service.isActive ? '#B8E6AF' : '#FFD4D4'
              }"
            >
              <div
                class="absolute top-1 w-5 h-5 rounded-full bg-white shadow-md transition-all"
                :style="{
                  left: service.isActive ? 'calc(100% - 24px)' : '4px'
                }"
              />
            </button>
          </div>

          <p class="text-sm text-gray-600 mb-6">{{ service.description }}</p>

          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center">
              <p class="text-xs text-gray-500 mb-1">Intervenants</p>
              <p class="text-xl" :style="{ color: service.couleur }">{{ service.intervenants }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-500 mb-1">Missions</p>
              <p class="text-xl" :style="{ color: service.couleur }">{{ service.missions }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-500 mb-1">Note</p>
              <div class="flex items-center justify-center gap-1">
                <Star :size="14" fill="#FEE347" :style="{ color: '#FEE347' }" />
                <p class="text-xl" :style="{ color: service.couleur }">{{ service.note }}</p>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              @click="viewServiceStats(service)"
              class="flex-1 py-3 rounded-lg transition-all text-white text-sm hover:shadow-lg flex items-center justify-center gap-2"
              :style="{ backgroundColor: service.couleur }"
            >
              <Activity :size="16" />
              Voir détails
            </button>
            <button
              @click="manageSubServices(service)"
              class="flex-1 py-3 rounded-lg transition-all text-white text-sm hover:shadow-lg flex items-center justify-center gap-2"
              :style="{ backgroundColor: service.couleur, opacity: 0.9 }"
            >
              <Package :size="16" />
              Sous-services
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="services.length > 0 && totalPages > 1" class="mt-6 flex items-center justify-between flex-wrap gap-3">
        <!-- Info -->
        <div class="text-xs text-gray-600">
          Affichage de {{ startIndex + 1 }} à {{ endIndex }} sur {{ services.length }} service{{ services.length > 1 ? 's' : '' }}
        </div>

        <!-- Contrôles de pagination -->
        <div class="flex items-center gap-2">
          <!-- Bouton Précédent -->
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all border"
            :style="{
              borderColor: currentPage === 1 ? '#D1D5DB' : '#5B7C99',
              color: currentPage === 1 ? '#9CA3AF' : '#5B7C99',
              cursor: currentPage === 1 ? 'not-allowed' : 'pointer',
              opacity: currentPage === 1 ? 0.5 : 1
            }"
          >
            Précédent
          </button>

          <!-- Numéros de page -->
          <div class="flex items-center gap-1">
            <template v-for="page in visiblePages" :key="page">
              <button
                v-if="page !== '...'"
                @click="currentPage = page"
                class="w-8 h-8 rounded-lg text-xs font-medium transition-all"
                :style="{
                  backgroundColor: currentPage === page ? '#5B7C99' : 'transparent',
                  color: currentPage === page ? 'white' : '#5B7C99',
                  border: `1px solid ${currentPage === page ? '#5B7C99' : '#D1D5DB'}`
                }"
              >
                {{ page }}
              </button>
              <span v-else class="px-1 text-gray-500 text-xs">...</span>
            </template>
          </div>

          <!-- Bouton Suivant -->
          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all border"
            :style="{
              borderColor: currentPage === totalPages ? '#D1D5DB' : '#5B7C99',
              color: currentPage === totalPages ? '#9CA3AF' : '#5B7C99',
              cursor: currentPage === totalPages ? 'not-allowed' : 'pointer',
              opacity: currentPage === totalPages ? 0.5 : 1
            }"
          >
            Suivant
          </button>
        </div>

        <!-- Sélecteur d'éléments par page -->
        <div class="flex items-center gap-2">
          <span class="text-xs text-gray-600">Par page:</span>
          <select
            v-model="itemsPerPage"
            @change="currentPage = 1"
            class="px-2 py-1.5 rounded-lg text-xs border"
            style="border-color: #D1D5DB; color: #2F4F4F"
          >
            <option :value="4">4</option>
            <option :value="6">6</option>
            <option :value="8">8</option>
            <option :value="12">12</option>
          </select>
        </div>
      </div>
    </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <Package :size="64" class="mx-auto mb-4 text-gray-300" />
        <p class="text-gray-500 text-lg">Aucun service disponible</p>
      </div>
    </div>

    <!-- Service Stats Detailed View -->
    <div v-if="showStatsModal" class="min-h-screen">
      <!-- Back Button -->
      <div class="p-6">
        <button
          @click="showStatsModal = false"
          class="flex items-center gap-2 text-sm transition-colors hover:underline mb-6"
          :style="{ color: serviceColor }"
        >
          ← Retour aux services
        </button>

        <!-- Loading State (minimal) -->
        <div v-if="loadingStats" class="text-center py-4 mb-6">
          <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
        </div>

        <!-- Header Banner -->
        <div
          v-else-if="serviceStats"
          class="relative rounded-xl overflow-hidden mb-6 p-6 shadow-lg"
          :style="{
            background: isJardinage ? '#d7e4d2' : (isMenage ? '#b8c5d1' : (serviceColor ? `linear-gradient(135deg, ${serviceColor}, ${serviceColor}DD)` : 'linear-gradient(135deg, #5B7C99, #5B7C99DD)')),
            color: (isJardinage || isMenage) ? '#4a4a4a' : 'white'
          }"
        >
          <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-1" :style="{ color: isJardinage ? '#86af88' : (isMenage ? '#67839b' : 'inherit') }">
              {{ serviceStats?.service?.nom || 'Service' }}
            </h1>
            <p class="text-base opacity-95" :style="{ color: (isJardinage || isMenage) ? '#4a4a4a' : 'inherit' }">Statistiques détaillées</p>
          </div>
        </div>

        <!-- Stats Content -->
        <div v-if="serviceStats" class="space-y-6">
          <!-- KPI Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Intervenants -->
            <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" :style="{ backgroundColor: lightColor }">
                  <Users :size="22" :style="{ color: serviceColor }" />
                </div>
              </div>
              <p class="text-xs text-gray-500 mb-1">Total Intervenants</p>
              <p class="text-xl mb-1.5" :style="{ color: serviceColor }">
                {{ serviceStats.totalIntervenants }}
              </p>
              <p class="text-xs font-medium" v-if="serviceStats.croissanceIntervenants !== 0">
                <span :style="serviceStats.croissanceIntervenants > 0 ? { color: positiveGrowthColor } : {}" :class="serviceStats.croissanceIntervenants < 0 ? 'text-red-600' : ''">
                  {{ serviceStats.croissanceIntervenants > 0 ? '↗ +' : '↘ ' }}{{ serviceStats.croissanceIntervenants }}% ce mois
                </span>
              </p>
              <p v-else class="text-xs text-gray-400">Aucune variation</p>
            </div>

            <!-- Missions Total -->
            <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" :style="{ backgroundColor: lightColor }">
                  <Briefcase :size="22" :style="{ color: serviceColor }" />
                </div>
              </div>
              <p class="text-xs text-gray-500 mb-1">Missions Total</p>
              <p class="text-xl mb-1.5" :style="{ color: serviceColor }">
                {{ serviceStats.totalMissions }}
              </p>
              <p class="text-xs font-medium" v-if="serviceStats.croissanceMissions !== 0">
                <span :style="serviceStats.croissanceMissions > 0 ? { color: positiveGrowthColor } : {}" :class="serviceStats.croissanceMissions < 0 ? 'text-red-600' : ''">
                  {{ serviceStats.croissanceMissions > 0 ? '↗ +' : '↘ ' }}{{ serviceStats.croissanceMissions }}% ce mois
                </span>
              </p>
              <p v-else class="text-xs text-gray-400">Aucune variation</p>
            </div>

            <!-- Note Moyenne -->
            <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" :style="{ backgroundColor: lightColor }">
                  <Star :size="22" :style="{ color: serviceColor }" fill="#FEE347" />
                </div>
              </div>
              <p class="text-xs text-gray-500 mb-1">Note Moyenne</p>
              <div class="flex items-center gap-1 mb-1.5">
                <Star :size="14" fill="#FEE347" :style="{ color: '#FEE347' }" />
                <p class="text-xl" :style="{ color: serviceColor }">
                  {{ serviceStats.noteMoyenne }}
              </p>
              </div>
              <p class="text-xs font-semibold" :style="{ color: serviceColor }">
                {{ serviceStats.noteMoyenne >= 4.5 ? '⭐ Excellent' : serviceStats.noteMoyenne >= 4 ? '⭐ Très bien' : serviceStats.noteMoyenne >= 3 ? '⭐ Bien' : '⭐ À améliorer' }}
              </p>
            </div>

            <!-- Revenus -->
            <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-sm" :style="{ backgroundColor: lightColor }">
                  <TrendingUp :size="22" :style="{ color: serviceColor }" />
                </div>
              </div>
              <p class="text-xs text-gray-500 mb-1">Revenus</p>
              <p class="text-xl mb-1.5" :style="{ color: serviceColor }">
                {{ formatCurrency(serviceStats.revenusTotaux) }}
              </p>
              <p class="text-xs font-medium" v-if="serviceStats.croissanceRevenus !== 0">
                <span :style="serviceStats.croissanceRevenus > 0 ? { color: positiveGrowthColor } : {}" :class="serviceStats.croissanceRevenus < 0 ? 'text-red-600' : ''">
                  {{ serviceStats.croissanceRevenus > 0 ? '↗ +' : '↘ ' }}{{ serviceStats.croissanceRevenus }}% ce mois
                </span>
              </p>
              <p v-else class="text-xs text-gray-400">Aucune variation</p>
            </div>
          </div>

          <!-- Charts and Lists Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Missions par mois -->
            <div class="bg-white rounded-xl p-5 shadow-md border border-gray-100">
              <h3 class="text-lg font-bold mb-4 text-gray-800">Missions par mois</h3>
              <div class="space-y-2">
                <div v-for="(mois, index) in serviceStats.missionsParMois" :key="index" class="flex items-center gap-3">
                  <div class="w-20 text-xs text-gray-500">{{ mois.mois }}</div>
                  <div class="flex-1">
                    <div class="relative h-4 rounded-full overflow-hidden bg-gray-200">
                      <div
                        class="h-full rounded-full transition-all duration-500"
                        :style="{
                          width: `${(mois.count / Math.max(...serviceStats.missionsParMois.map(m => m.count), 1)) * 100}%`,
                          backgroundColor: serviceColor
                        }"
                      ></div>
                    </div>
                  </div>
                  <div class="w-16 text-right text-sm text-gray-400 font-medium">
                    {{ mois.count }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Intervenants -->
            <div class="bg-white rounded-xl p-5 shadow-md border border-gray-100">
              <h3 class="text-lg font-bold mb-4 text-gray-800">Top Intervenants</h3>
              <div class="space-y-3">
                <div
                  v-for="intervenant in serviceStats.topIntervenants.slice(0, 5)"
                  :key="intervenant.id"
                  class="flex items-center gap-3"
                >
                  <div
                    class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-sm"
                    :style="{ backgroundColor: lightColor, color: serviceColor }"
                  >
                    {{ getInitials(intervenant.nom, intervenant.prenom) }}
                  </div>
                  <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">
                      {{ intervenant.prenom }} {{ intervenant.nom }}
                    </p>
                    <p class="text-xs text-gray-500">{{ intervenant.missions }} missions</p>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <Star :size="16" fill="#FEE347" :style="{ color: '#FEE347' }" />
                    <span class="font-bold text-sm" :style="{ color: serviceColor }">
                      {{ intervenant.note }}
                    </span>
                  </div>
                </div>
                <div v-if="serviceStats.topIntervenants.length === 0" class="text-center py-8 text-gray-400">
                  <Users :size="40" class="mx-auto mb-2 opacity-30" />
                  <p class="text-sm">Aucun intervenant disponible</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Revenus par tâche -->
          <div v-if="serviceStats" 
               :style="getRevenusParTacheStyle()"
               class="rounded-xl">
            <h3 class="text-lg font-bold mb-4" :style="{ color: serviceColor }">Revenus par tâche</h3>
            
            <!-- Afficher les tâches si elles existent -->
            <div v-if="serviceStats.revenusParTache && Array.isArray(serviceStats.revenusParTache) && serviceStats.revenusParTache.length > 0" 
                 class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div
                v-for="tache in serviceStats.revenusParTache"
                :key="tache.id"
                class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg transition-all"
              >
                <p class="text-xs text-gray-500 mb-1">{{ tache.nom }}</p>
                <p class="text-xl mb-1.5" :style="{ color: serviceColor }">
                  {{ formatCurrency(tache.revenus) }}
                </p>
                <p class="text-xs text-gray-500">{{ tache.missions }} missions</p>
              </div>
            </div>
            
            <!-- Message si aucune tâche ou aucun revenu -->
            <div v-else class="text-center py-8">
              <Package :size="48" class="mx-auto mb-3 opacity-30" :style="{ color: serviceColor }" />
              <p class="text-sm text-gray-500 mb-2">Aucune tâche avec revenus enregistrée</p>
              <p class="text-xs text-gray-400">Les revenus par tâche apparaîtront ici une fois que des missions auront été complétées</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sub-Services Management Modal -->
    <div
      v-if="showSubServicesModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="() => { showSubServicesModal = false; currentSubServicesPage = 1; }"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div
          class="p-4 flex items-center justify-between"
          :style="{
            backgroundColor: currentService?.couleur || '#5B7C99',
            color: 'white'
          }"
        >
          <div>
            <h2 class="text-lg font-semibold">Gestion des Sous-services</h2>
            <p class="text-xs opacity-90">{{ currentService?.nom }}</p>
          </div>
          <button
            @click="() => { showSubServicesModal = false; currentSubServicesPage = 1; }"
            class="p-1.5 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors"
          >
            <X :size="20" />
          </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
          <!-- Loading State (minimal) -->
          <div v-if="loadingSubServices" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
          </div>

          <!-- Sub-Services List -->
          <div v-else>
            <div class="flex justify-between items-center mb-3">
              <h3 class="text-base font-semibold text-gray-800">Liste des sous-services</h3>
              <button
                @click="openAddModal"
                class="px-3 py-1.5 rounded-lg text-white text-xs font-medium flex items-center gap-1.5 hover:shadow-md transition-all"
                :style="{ backgroundColor: currentService?.couleur || '#5B7C99' }"
              >
                <Plus :size="14" />
                Ajouter un sous-service
              </button>
            </div>

            <!-- Empty State -->
            <div v-if="subServices.length === 0" class="text-center py-8 bg-gray-50 rounded-xl">
              <Package :size="36" class="mx-auto mb-3 text-gray-300" />
              <p class="text-sm text-gray-500">Aucun sous-service disponible</p>
              <button
                @click="openAddModal"
                class="mt-3 px-3 py-1.5 rounded-lg text-white text-xs font-medium"
                :style="{ backgroundColor: currentService?.couleur || '#5B7C99' }"
              >
                Créer le premier sous-service
              </button>
            </div>

            <!-- Sub-Services Grid -->
            <div v-else>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div
                  v-for="subService in paginatedSubServices"
                :key="subService.id"
                class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-md transition-all"
              >
                <div class="flex items-start justify-between mb-2">
                  <div class="flex-1">
                    <h4 class="text-sm font-semibold text-gray-800 mb-1">{{ subService.nom_tache }}</h4>
                    <p class="text-xs text-gray-500 mb-2">{{ subService.description || 'Aucune description' }}</p>
                    <span
                      class="inline-block px-2 py-0.5 rounded text-xs font-medium"
                      :class="subService.status === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                    >
                      {{ subService.status === 'actif' ? 'Actif' : 'Inactif' }}
                    </span>
                  </div>
                </div>
                <div class="flex gap-1.5 mt-3">
                  <button
                    @click="openEditModal(subService)"
                    class="flex-1 px-2.5 py-1.5 rounded-lg text-xs font-medium flex items-center justify-center gap-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                  >
                    <Edit :size="14" />
                    Modifier
                  </button>
                  <button
                    @click="openDeleteModal(subService)"
                    class="flex-1 px-2.5 py-1.5 rounded-lg text-xs font-medium flex items-center justify-center gap-1.5 bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                  >
                    <Trash2 :size="14" />
                    Supprimer
                  </button>
                </div>
              </div>
            </div>

              <!-- Pagination Controls for Sub-Services -->
              <div 
                v-if="subServices.length > 0 && totalSubServicesPages > 1"
                class="flex items-center justify-between mt-4 pt-3 border-t"
                style="border-color: #E5E7EB"
              >
                <button
                  @click="currentSubServicesPage = currentSubServicesPage - 1"
                  :disabled="currentSubServicesPage === 1"
                  class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  :style="{
                    backgroundColor: currentSubServicesPage === 1 ? '#E5E7EB' : (currentService?.couleur || '#5B7C99'),
                    color: currentSubServicesPage === 1 ? '#9CA3AF' : 'white'
                  }"
                >
                  ← Précédent
                </button>
                
                <div class="flex items-center gap-2">
                  <span class="text-xs" style="color: #6B7280">
                    Page {{ currentSubServicesPage }} / {{ totalSubServicesPages }}
                  </span>
                  <div class="flex gap-1">
                    <div
                      v-for="page in totalSubServicesPages"
                      :key="page"
                      @click="currentSubServicesPage = page"
                      class="w-2 h-2 rounded-full cursor-pointer transition-all"
                      :style="{
                        backgroundColor: currentSubServicesPage === page ? (currentService?.couleur || '#5B7C99') : '#D1D5DB',
                        width: currentSubServicesPage === page ? '24px' : '8px'
                      }"
                    ></div>
                  </div>
                </div>
                
                <button
                  @click="currentSubServicesPage = currentSubServicesPage + 1"
                  :disabled="currentSubServicesPage === totalSubServicesPages"
                  class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  :style="{
                    backgroundColor: currentSubServicesPage === totalSubServicesPages ? '#E5E7EB' : (currentService?.couleur || '#5B7C99'),
                    color: currentSubServicesPage === totalSubServicesPages ? '#9CA3AF' : 'white'
                  }"
                >
                  Suivant →
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Sub-Service Modal -->
    <div
      v-if="showAddModal || showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModals"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div
          class="p-4 flex items-center justify-between"
          :style="{
            backgroundColor: currentService?.couleur || '#5B7C99',
            color: 'white'
          }"
        >
          <h2 class="text-lg font-semibold">
            {{ showAddModal ? 'Ajouter un sous-service' : 'Modifier le sous-service' }}
          </h2>
          <button
            @click="closeModals"
            class="p-1.5 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors"
          >
            <X :size="20" />
          </button>
        </div>

        <!-- Form -->
        <div class="flex-1 overflow-y-auto p-4">
          <form @submit.prevent="saveSubService" class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Nom du sous-service <span class="text-red-500">*</span>
              </label>
              <input
                v-model="subServiceForm.nom_tache"
                type="text"
                required
                maxlength="150"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Ex: Taille de haie"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Description
              </label>
              <textarea
                v-model="subServiceForm.description"
                rows="3"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Description détaillée du sous-service"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Statut
              </label>
              <select
                v-model="subServiceForm.status"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                URL de l'image (optionnel)
              </label>
              <input
                v-model="subServiceForm.image_url"
                type="url"
                maxlength="255"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="https://example.com/image.jpg"
              />
            </div>

            <div class="flex gap-2 pt-3">
              <button
                type="button"
                @click="closeModals"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs font-medium hover:bg-gray-50 transition-colors"
              >
                Annuler
              </button>
              <button
                type="submit"
                class="flex-1 px-3 py-2 rounded-lg text-white text-xs font-medium hover:shadow-md transition-all"
                :style="{ backgroundColor: currentService?.couleur || '#5B7C99' }"
              >
                {{ showAddModal ? 'Créer' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModals"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <div class="p-4">
          <h2 class="text-lg font-semibold text-gray-800 mb-3">Confirmer la suppression</h2>
          <p class="text-sm text-gray-600 mb-4">
            Êtes-vous sûr de vouloir supprimer le sous-service
            <strong class="text-gray-800">"{{ selectedSubService?.nom_tache }}"</strong> ?
            <br /><br />
            Cette action est irréversible.
          </p>
          <div class="flex gap-2">
            <button
              @click="closeModals"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs font-medium hover:bg-gray-50 transition-colors"
            >
              Annuler
            </button>
            <button
              @click="deleteSubService"
              class="flex-1 px-3 py-2 rounded-lg text-white text-xs font-medium bg-red-600 hover:bg-red-700 transition-colors"
            >
              Supprimer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Service Modal -->
    <div
      v-if="showAddServiceModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeAddServiceModal"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div
          class="p-4 flex items-center justify-between"
          style="background-color: #7FB3D3; color: white;"
        >
          <h2 class="text-lg font-semibold">Ajouter un Service</h2>
          <button
            @click="closeAddServiceModal"
            class="p-1.5 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors"
          >
            <X :size="20" />
          </button>
        </div>

        <!-- Form -->
        <div class="flex-1 overflow-y-auto p-4">
          <form @submit.prevent="saveService" class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Nom du service <span class="text-red-500">*</span>
              </label>
              <input
                v-model="serviceForm.nom_service"
                type="text"
                required
                maxlength="100"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Ex: Jardinage, Ménage, Plomberie..."
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Description
              </label>
              <textarea
                v-model="serviceForm.description"
                rows="4"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Description détaillée du service"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Statut
              </label>
              <select
                v-model="serviceForm.status"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
              </select>
            </div>

            <div class="flex gap-2 pt-3">
              <button
                type="button"
                @click="closeAddServiceModal"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-gray-700 text-xs font-medium hover:bg-gray-50 transition-colors"
              >
                Annuler
              </button>
              <button
                type="submit"
                class="flex-1 px-3 py-2 rounded-lg text-white text-xs font-medium hover:shadow-md transition-all"
                style="background-color: #7FB3D3"
              >
                Créer le service
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Package, Activity, Star, Clock, X, Users, Briefcase, TrendingUp, Plus, Edit, Trash2 } from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useNotifications } from '@/composables/useNotifications'

const emit = defineEmits(['back', 'demandes-updated'])
const { success, error, confirm: confirmDialog } = useNotifications()

const services = ref([])
const loading = ref(false)
const showStatsModal = ref(false)
const loadingStats = ref(false)
const serviceStats = ref(null)

// Sub-services management
const showSubServicesModal = ref(false)
const currentService = ref(null)
const subServices = ref([])
const loadingSubServices = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedSubService = ref(null)
const subServiceForm = ref({
  nom_tache: '',
  description: '',
  status: 'actif',
  image_url: ''
})

// Service management
const showAddServiceModal = ref(false)
const serviceForm = ref({
  nom_service: '',
  description: '',
  status: 'active'
})

// Pagination pour les services
const currentPage = ref(1)
const itemsPerPage = ref(6)

// Pagination pour les sous-services
const currentSubServicesPage = ref(1)
const subServicesPerPage = 4

// Pagination computed properties pour les services
const totalPages = computed(() => {
  return Math.ceil(services.value.length / itemsPerPage.value)
})

const startIndex = computed(() => {
  return (currentPage.value - 1) * itemsPerPage.value
})

const endIndex = computed(() => {
  return Math.min(startIndex.value + itemsPerPage.value, services.value.length)
})

const paginatedServices = computed(() => {
  return services.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    // Si moins de 7 pages, afficher toutes
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Sinon, afficher intelligemment
    if (current <= 3) {
      // Début
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 2) {
      // Fin
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) {
        pages.push(i)
      }
    } else {
      // Milieu
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

// Palette de couleurs prédéfinies pour les services
// Note: '#5B7C99' (Ménage) et '#92B08B' (Jardinage) sont exclus car déjà utilisées
// Exclure aussi leurs dérivés (bleus gris et verts similaires)
const serviceColorPalette = [
  '#F4A261',  // Orange
  '#E76F51',  // Rouge corail
  '#E9C46A',  // Jaune
  '#EF476F',  // Rose
  '#F77F00',  // Orange foncé
  '#D62828',  // Rouge
  '#FCBF49',  // Jaune doré
  '#FFD166',  // Jaune pâle
  '#F38181',  // Rose saumon
  '#AA96DA',  // Violet
  '#FCBAD3',  // Rose pâle
  '#FFFFD2',  // Jaune très clair
  '#264653'   // Bleu foncé (différent du bleu gris)
]

/**
 * Génère une couleur unique qui n'est pas déjà utilisée par les autres services
 */
const generateUniqueServiceColor = () => {
  // Récupérer toutes les couleurs déjà utilisées depuis les services chargés
  const usedColorsFromServices = services.value.map(service => service.couleur).filter(Boolean)
  
  // Récupérer toutes les couleurs stockées dans localStorage
  const storedColors = getAllStoredColors()
  const usedColorsFromStorage = Object.values(storedColors).filter(Boolean)
  
  // Combiner les deux listes et supprimer les doublons
  const usedColors = [...new Set([...usedColorsFromServices, ...usedColorsFromStorage])]
  
  // Chercher la première couleur disponible dans la palette
  for (const color of serviceColorPalette) {
    if (!usedColors.includes(color)) {
      return color
    }
  }
  
  // Si toutes les couleurs de la palette sont utilisées, générer une couleur aléatoire
  // qui soit suffisamment différente des couleurs existantes
  let newColor
  let attempts = 0
  const maxAttempts = 50
  
  do {
    // Générer une couleur HSL avec une saturation et luminosité agréables
    const hue = Math.floor(Math.random() * 360)
    const saturation = 45 + Math.floor(Math.random() * 30) // Entre 45% et 75%
    const lightness = 40 + Math.floor(Math.random() * 20) // Entre 40% et 60%
    
    // Convertir HSL en hexadécimal
    newColor = hslToHex(hue, saturation, lightness)
    attempts++
  } while (usedColors.includes(newColor) && attempts < maxAttempts)
  
  // Si on n'a pas trouvé de couleur unique après plusieurs tentatives,
  // retourner une couleur par défaut avec une variation
  if (attempts >= maxAttempts || usedColors.includes(newColor)) {
    const baseHue = Math.floor(Math.random() * 360)
    return hslToHex(baseHue, 50, 50)
  }
  
  return newColor
}

/**
 * Convertit HSL en hexadécimal
 */
const hslToHex = (h, s, l) => {
  s /= 100
  l /= 100
  
  const c = (1 - Math.abs(2 * l - 1)) * s
  const x = c * (1 - Math.abs((h / 60) % 2 - 1))
  const m = l - c / 2
  
  let r = 0, g = 0, b = 0
  
  if (0 <= h && h < 60) {
    r = c; g = x; b = 0
  } else if (60 <= h && h < 120) {
    r = x; g = c; b = 0
  } else if (120 <= h && h < 180) {
    r = 0; g = c; b = x
  } else if (180 <= h && h < 240) {
    r = 0; g = x; b = c
  } else if (240 <= h && h < 300) {
    r = x; g = 0; b = c
  } else if (300 <= h && h < 360) {
    r = c; g = 0; b = x
  }
  
  r = Math.round((r + m) * 255)
  g = Math.round((g + m) * 255)
  b = Math.round((b + m) * 255)
  
  return '#' + [r, g, b].map(x => {
    const hex = x.toString(16)
    return hex.length === 1 ? '0' + hex : hex
  }).join('')
}

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
 * Stocke la couleur d'un service dans localStorage
 */
const storeServiceColor = (serviceId, color) => {
  try {
    const storedColors = JSON.parse(localStorage.getItem('serviceColors') || '{}')
    storedColors[serviceId] = color
    localStorage.setItem('serviceColors', JSON.stringify(storedColors))
  } catch (e) {
    console.error('Erreur lors du stockage de la couleur:', e)
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

// Methods
const loadServices = async () => {
  try {
    loading.value = true
    const response = await adminService.getServices()
    // Handle paginated response structure (new) or direct array (old for compatibility)
    const loadedServices = response.data?.data || response.data || []
    
    // Récupérer les couleurs stockées
    const storedColors = getAllStoredColors()
    
    // Mapper les services avec leurs couleurs stockées ou générer de nouvelles
    services.value = loadedServices.map(service => {
      // Vérifier si une couleur est déjà stockée pour ce service
      const storedColor = storedColors[service.id]
      
      if (storedColor) {
        // Utiliser la couleur stockée
        return {
          ...service,
          couleur: storedColor
        }
      } else {
        // Si le service a déjà une couleur (depuis le backend)
        if (service.couleur && service.couleur !== '#808080') {
          // Stocker la couleur si ce n'est pas la couleur par défaut du backend
          storeServiceColor(service.id, service.couleur)
          return {
            ...service,
            couleur: service.couleur
          }
        } else {
          // Si pas de couleur ou couleur par défaut (#808080), générer une nouvelle couleur unique
          const newColor = generateUniqueServiceColor()
          storeServiceColor(service.id, newColor)
          return {
            ...service,
            couleur: newColor
          }
        }
      }
    })
    
    // Réinitialiser la pagination après chargement
    currentPage.value = 1
  } catch (error) {
    console.error('Erreur chargement services:', error)
    services.value = []
  } finally {
    loading.value = false
  }
}

const toggleService = async (serviceParam) => {
  // Gérer le cas où on reçoit l'objet service ou juste l'ID
  let currentService
  if (typeof serviceParam === 'object' && serviceParam !== null) {
    currentService = serviceParam
  } else {
    // Si c'est juste un ID, chercher le service dans la liste
    currentService = services.value.find(s => s.id === serviceParam)
  }
  
  if (!currentService) {
    error('Service introuvable')
    return
  }
  
  const serviceId = currentService.id
  const serviceName = currentService.nom || 'ce service'
  const isCurrentlyActive = currentService.isActive ?? true
  
  // Confirmation avant désactivation
  if (isCurrentlyActive) {
    const confirmMessage = `Êtes-vous sûr de vouloir désactiver le service "${serviceName}" ?\n\n` +
      `Effets de la désactivation :\n` +
      `• Le service ne sera plus visible pour les clients\n` +
      `• Les nouvelles demandes ne pourront plus être créées\n` +
      `• Les missions en cours continueront normalement`
    
    const confirmed = await confirmDialog(confirmMessage, 'Désactiver le service')
    if (!confirmed) {
      return // L'utilisateur a annulé
    }
  } else {
    // Confirmation pour réactivation (plus simple)
    const confirmed = await confirmDialog(`Voulez-vous réactiver le service "${serviceName}" ?`, 'Réactiver le service')
    if (!confirmed) {
      return
    }
  }
  
  try {
    const response = await adminService.toggleServiceStatus(serviceId)
    
    if (response.data.error) {
      throw new Error(response.data.message || response.data.error)
    }
    
    // Afficher un message de succès
    const successMessage = isCurrentlyActive 
      ? `Le service "${serviceName}" a été désactivé avec succès.`
      : `Le service "${serviceName}" a été réactivé avec succès.`
    
    success(successMessage)
    
    await loadServices()
    
    // Émettre un événement pour rafraîchir les demandes si nécessaire
    // Car la désactivation/réactivation d'un service peut affecter les demandes en attente
    emit('demandes-updated')
  } catch (err) {
    console.error('Erreur changement statut service:', err)
    const errorMessage = err.response?.data?.message || err.response?.data?.error || err.message || 'Erreur lors du changement de statut du service'
    error(errorMessage)
  }
}

const viewServiceStats = async (service) => {
  showStatsModal.value = true
  loadingStats.value = true
  serviceStats.value = null
  
  try {
    const response = await adminService.getServiceStats(service.id)
    
    if (response.data.error) {
      throw new Error(response.data.message || response.data.error)
    }
    
    serviceStats.value = response.data
    
    // S'assurer que revenusParTache est toujours défini comme un tableau
    if (!serviceStats.value.revenusParTache) {
      serviceStats.value.revenusParTache = []
    }
    
    // Toujours prioriser la couleur stockée dans localStorage ou dans les services chargés
    // pour garantir que les nouveaux services avec leurs couleurs uniques sont affichés correctement
    const loadedService = services.value.find(s => s.id === service.id)
    const storedColor = getStoredServiceColor(service.id)
    
    // Priorité : 1) Service chargé, 2) localStorage, 3) Service passé en paramètre, 4) Backend, 5) Défaut
    if (loadedService?.couleur) {
      serviceStats.value.service.couleur = loadedService.couleur
    } else if (storedColor) {
      serviceStats.value.service.couleur = storedColor
    } else if (service.couleur) {
      serviceStats.value.service.couleur = service.couleur
    } else if (!serviceStats.value.service.couleur || serviceStats.value.service.couleur === '#808080') {
      // Si le backend retourne la couleur par défaut (#808080) ou aucune couleur,
      // générer une nouvelle couleur unique et la stocker
      const newColor = generateUniqueServiceColor()
      storeServiceColor(service.id, newColor)
      serviceStats.value.service.couleur = newColor
    }
  } catch (error) {
    console.error('Erreur chargement statistiques:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Erreur lors du chargement des statistiques'
    error(errorMessage)
    showStatsModal.value = false
  } finally {
    loadingStats.value = false
  }
}

const formatCurrency = (amount) => {
  if (!amount || amount === 0) return '0 DH'
  // Le montant est déjà en dirhams (DH)
  const dh = amount
  if (dh >= 1000) {
    return new Intl.NumberFormat('fr-FR', {
      style: 'decimal',
      minimumFractionDigits: 1,
      maximumFractionDigits: 1
    }).format(dh / 1000) + 'k DH'
  }
  return new Intl.NumberFormat('fr-FR', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(dh) + ' DH'
}

const getInitials = (nom, prenom) => {
  return ((prenom?.[0] || '') + (nom?.[0] || '')).toUpperCase()
}

// Fonction pour obtenir les couleurs spécifiques selon le service
const getServiceColors = (serviceName) => {
  if (!serviceName) return null
  const serviceNameLower = String(serviceName).toLowerCase().trim()
  
  // Détection pour Jardinage (insensible à la casse et aux espaces)
  if (serviceNameLower === 'jardinage' || serviceNameLower.includes('jardinage')) {
    return {
      primary: '#86af88',      // Vert clair pour texte et éléments (au lieu de vert foncé)
      header: '#d7e4d2',      // Vert clair pour header
      button: '#86af88',      // Vert clair pour boutons
      light: '#d7e4d2',       // Vert clair pour fonds
      accent: '#86af88'       // Accent principal en vert clair
    }
  }
  
  // Détection pour Ménage (insensible à la casse et aux espaces)
  if (serviceNameLower === 'ménage' || serviceNameLower.includes('ménage') || serviceNameLower === 'menage' || serviceNameLower.includes('menage')) {
    return {
      primary: '#67839b',      // Bleu gris pour texte et éléments
      header: '#b8c5d1',      // Bleu gris clair pour header
      button: '#67839b',      // Bleu gris pour boutons
      light: '#b8c5d1',       // Bleu gris clair pour fonds
      accent: '#67839b'       // Accent principal en bleu gris
    }
  }
  
  // Couleurs par défaut (utiliser la couleur du service)
  return null
}

// Computed pour obtenir la couleur à utiliser
// Pour Jardinage, utiliser les couleurs spécifiques, sinon utiliser service.couleur
const serviceColor = computed(() => {
  if (!serviceStats.value?.service) return '#5B7C99'
  const customColors = getServiceColors(serviceStats.value.service.nom_service || serviceStats.value.service.nom)
  // Pour Jardinage et Ménage, utiliser la couleur primaire spécifique
  if (customColors) {
    return customColors.primary
  }
  // Sinon utiliser directement la couleur du service comme dans la carte
  // Si la couleur n'est pas présente, essayer de la récupérer depuis les services chargés
  if (!serviceStats.value.service.couleur) {
    const loadedService = services.value.find(s => s.id === serviceStats.value.service.id)
    if (loadedService?.couleur) {
      return loadedService.couleur
    }
    // Sinon depuis localStorage
    const storedColor = getStoredServiceColor(serviceStats.value.service.id)
    if (storedColor) {
      return storedColor
    }
  }
  return serviceStats.value.service.couleur || '#5B7C99'
})

// Computed pour obtenir la couleur du header
const headerColor = computed(() => {
  if (!serviceStats.value?.service) return '#5B7C99'
  const customColors = getServiceColors(serviceStats.value.service.nom_service || serviceStats.value.service.nom)
  if (customColors) {
    return customColors.header
  }
  // Récupérer la couleur du service
  const color = serviceStats.value.service.couleur || 
                services.value.find(s => s.id === serviceStats.value.service.id)?.couleur ||
                getStoredServiceColor(serviceStats.value.service.id) ||
                '#5B7C99'
  return color
})

// Computed pour obtenir la couleur de fond claire
const lightColor = computed(() => {
  if (!serviceStats.value?.service) return '#5B7C9915'
  const serviceName = serviceStats.value.service.nom_service || serviceStats.value.service.nom
  const customColors = getServiceColors(serviceName)
  if (customColors) {
    // Pour Jardinage, utiliser le vert clair avec opacité
    if (serviceName.toLowerCase().includes('jardinage')) {
    return 'rgba(215, 228, 210, 0.25)' // #d7e4d2 avec 25% d'opacité
    }
    // Pour Ménage, utiliser le bleu gris clair avec opacité
    if (serviceName.toLowerCase().includes('ménage') || serviceName.toLowerCase().includes('menage')) {
      return 'rgba(184, 197, 209, 0.25)' // #b8c5d1 avec 25% d'opacité
    }
  }
  // Récupérer la couleur du service
  const color = serviceStats.value.service.couleur || 
                services.value.find(s => s.id === serviceStats.value.service.id)?.couleur ||
                getStoredServiceColor(serviceStats.value.service.id) ||
                '#5B7C99'
  return `${color}15`
})

// Computed pour obtenir la couleur de croissance positive (utiliser la couleur du service)
const positiveGrowthColor = computed(() => {
  if (!serviceStats.value?.service) return '#16a34a' // green-600 en hex
  const customColors = getServiceColors(serviceStats.value.service.nom_service || serviceStats.value.service.nom)
  if (customColors) {
    // Pour Jardinage et Ménage, utiliser la couleur primaire spécifique
    return customColors.primary
  }
  // Pour les nouveaux services, utiliser leur couleur unique
  const color = serviceStats.value.service.couleur || 
                services.value.find(s => s.id === serviceStats.value.service.id)?.couleur ||
                getStoredServiceColor(serviceStats.value.service.id) ||
                '#16a34a'
  return color
})

/**
 * Obtient le style pour la section "Revenus par tâche"
 */
const getRevenusParTacheStyle = () => {
  // Style par défaut sans backgroundColor
  return {
    padding: '1.5rem',
    borderRadius: '0.75rem',
    marginTop: '1.5rem'
  }
}

// Computed pour vérifier si c'est Jardinage
const isJardinage = computed(() => {
  if (!serviceStats.value?.service?.nom) {
    return false
  }
  const serviceName = String(serviceStats.value.service.nom_service || serviceStats.value.service.nom).toLowerCase().trim()
  return serviceName === 'jardinage' || serviceName.includes('jardinage')
})

// Computed pour vérifier si c'est Ménage
const isMenage = computed(() => {
  if (!serviceStats.value?.service?.nom) {
    return false
  }
  const serviceName = String(serviceStats.value.service.nom_service || serviceStats.value.service.nom).toLowerCase().trim()
  return serviceName === 'ménage' || serviceName.includes('ménage') || serviceName === 'menage' || serviceName.includes('menage')
})

const manageSubServices = async (service) => {
  currentService.value = service
  showSubServicesModal.value = true
  await loadSubServices(service.id)
}

const loadSubServices = async (serviceId) => {
  try {
    loadingSubServices.value = true
    const response = await adminService.getServiceTaches(serviceId)
    subServices.value = response.data.taches || []
    // Réinitialiser la page quand on charge de nouveaux sous-services
    currentSubServicesPage.value = 1
  } catch (error) {
    console.error('Erreur chargement sous-services:', error)
    error(error.response?.data?.message || 'Erreur lors du chargement des sous-services')
    subServices.value = []
  } finally {
    loadingSubServices.value = false
  }
}

// Pagination pour les sous-services
const paginatedSubServices = computed(() => {
  if (!subServices.value || subServices.value.length === 0) return []
  
  const start = (currentSubServicesPage.value - 1) * subServicesPerPage
  const end = start + subServicesPerPage
  return subServices.value.slice(start, end)
})

// Calculer le nombre total de pages pour les sous-services
const totalSubServicesPages = computed(() => {
  if (!subServices.value || subServices.value.length === 0) return 0
  return Math.ceil(subServices.value.length / subServicesPerPage)
})

// Réinitialiser la page quand on change de service
watch(() => currentService.value?.id, () => {
  currentSubServicesPage.value = 1
})

const openAddModal = () => {
  subServiceForm.value = {
    nom_tache: '',
    description: '',
    status: 'actif',
    image_url: ''
  }
  showAddModal.value = true
}

const openEditModal = (subService) => {
  selectedSubService.value = subService
  subServiceForm.value = {
    nom_tache: subService.nom_tache,
    description: subService.description || '',
    status: subService.status || 'actif',
    image_url: subService.image_url || ''
  }
  showEditModal.value = true
}

const openDeleteModal = (subService) => {
  selectedSubService.value = subService
  showDeleteModal.value = true
}

const closeModals = () => {
  // Réinitialiser la page des sous-services quand on ferme le modal
  currentSubServicesPage.value = 1
  showAddModal.value = false
  showEditModal.value = false
  showDeleteModal.value = false
  selectedSubService.value = null
}

const saveSubService = async () => {
  try {
    if (!subServiceForm.value.nom_tache.trim()) {
      error('Le nom du sous-service est requis')
      return
    }

    if (showAddModal.value) {
      await adminService.createTache(currentService.value.id, subServiceForm.value)
      success('Sous-service créé avec succès')
    } else {
      await adminService.updateTache(selectedSubService.value.id, subServiceForm.value)
      success('Sous-service modifié avec succès')
    }

    closeModals()
    await loadSubServices(currentService.value.id)
  } catch (error) {
    console.error('Erreur sauvegarde sous-service:', error)
    error(error.response?.data?.message || error.response?.data?.error || 'Erreur lors de la sauvegarde')
  }
}

const deleteSubService = async () => {
  try {
    await adminService.deleteTache(selectedSubService.value.id)
    success('Sous-service supprimé avec succès')
    closeModals()
    await loadSubServices(currentService.value.id)
  } catch (error) {
    console.error('Erreur suppression sous-service:', error)
    error(error.response?.data?.message || error.response?.data?.error || 'Erreur lors de la suppression')
  }
}

const openAddServiceModal = () => {
  serviceForm.value = {
    nom_service: '',
    description: '',
    status: 'active'
  }
  showAddServiceModal.value = true
}

const closeAddServiceModal = () => {
  showAddServiceModal.value = false
  serviceForm.value = {
    nom_service: '',
    description: '',
    status: 'active'
  }
}

const saveService = async () => {
  try {
    if (!serviceForm.value.nom_service.trim()) {
      error('Le nom du service est requis')
      return
    }

    // Générer une couleur unique pour le nouveau service
    const uniqueColor = generateUniqueServiceColor()
    
    // Ajouter la couleur au formulaire avant l'envoi
    const serviceData = {
      ...serviceForm.value,
      couleur: uniqueColor
    }

    const response = await adminService.createService(serviceData)
    
    // Stocker la couleur générée pour le nouveau service
    if (response.data?.service?.id) {
      storeServiceColor(response.data.service.id, uniqueColor)
    }
    
    success('Service créé avec succès')
    closeAddServiceModal()
    await loadServices()
  } catch (error) {
    console.error('Erreur création service:', error)
    error(error.response?.data?.message || error.response?.data?.error || 'Erreur lors de la création du service')
  }
}

onMounted(async () => {
  await loadServices()
})
</script>