<template>
  <div 
    v-if="isOpen && intervenantData"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    @click.self="$emit('close')"
  >
    <div class="bg-white rounded-xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <h2 class="text-lg font-semibold" style="color: #2F4F4F">Profil de l'Intervenant</h2>
        <button 
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <X :size="20" />
        </button>
      </div>

      <!-- Profile Header -->
      <div class="p-4 border-b">
        <div class="flex items-start gap-3">
          <!-- Profile Photo -->
          <div class="relative">
            <div 
              class="w-12 h-12 rounded-full flex items-center justify-center text-xl text-gray-400 border-3"
              :style="{ 
                backgroundColor: '#E5E7EB',
                borderColor: getServiceColor(),
                borderWidth: '3px'
              }"
            >
              <User :size="24" />
            </div>
            <!-- Status Badge -->
            <div 
              class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full border-3 border-white flex items-center justify-center"
              :style="{ backgroundColor: intervenantData.statut === 'actif' ? '#4CAF50' : '#94A3B8', borderWidth: '3px' }"
            >
              <Check v-if="intervenantData.statut === 'actif'" :size="10" color="white" />
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1">
            <h3 class="text-base font-semibold mb-1" style="color: #2F4F4F">
              {{ intervenantData.prenom }} {{ intervenantData.nom }}
            </h3>
            <div class="flex items-center gap-1.5 text-xs text-gray-600 mb-1.5">
              <MapPin :size="12" />
              <span :class="intervenantData.adresse ? '' : 'text-gray-400 italic'">
                {{ intervenantData.adresse || 'Adresse non renseignée' }}
              </span>
            </div>
            <div class="flex items-center gap-1.5">
              <Star 
                v-for="i in 5" 
                :key="i"
                :size="14" 
                :fill="i <= Math.round(intervenantData.note) ? '#FFC107' : 'none'"
                :stroke="i <= Math.round(intervenantData.note) ? '#FFC107' : '#E5E7EB'"
              />
              <span class="text-base font-semibold ml-1.5">{{ intervenantData.note }}</span>
              <span class="text-xs text-gray-500">({{ intervenantData.nbAvis }} avis)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex border-b px-4">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-3 py-2 text-xs font-medium transition-colors relative"
          :style="{
            color: activeTab === tab.id ? '#2F4F4F' : '#9CA3AF',
            borderBottom: activeTab === tab.id ? '2px solid #2F4F4F' : '2px solid transparent'
          }"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-4">
        <!-- Loading State (minimal) -->
        <div v-if="loading" class="text-center py-4">
          <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
        </div>

        <!-- À propos Tab -->
        <div v-if="!loading && activeTab === 'apropos'">
          <!-- Bio -->
          <div class="mb-4">
            <p class="text-xs text-gray-700 leading-relaxed">
              {{ intervenantData.bio || `Bonjour ! Je m'appelle ${intervenantData.prenom} et je suis professionnel(le) du ${getMainService()} depuis plusieurs années. Passionné(e) par mon métier, j'accorde une attention particulière aux détails pour vous offrir un intérieur impeccable. Mon expérience m'a permis de développer des méthodes efficaces et respectueuses de l'environnement.` }}
            </p>
          </div>

          <!-- Contact Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div class="flex items-start gap-2 p-3 rounded-lg w-full" style="background-color: #F9FAFB">
              <Mail :size="14" style="color: #5B7C99" class="mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 mb-0.5">Email</p>
                <p class="text-xs font-medium" style="color: #2F4F4F">{{ intervenantData.email }}</p>
              </div>
            </div>
            <div class="flex items-start gap-2 p-3 rounded-lg w-full" style="background-color: #F9FAFB">
              <Phone :size="14" style="color: #5B7C99" class="mt-0.5" />
              <div>
                <p class="text-xs text-gray-500 mb-0.5">Téléphone</p>
                <p class="text-xs font-medium" style="color: #2F4F4F">{{ intervenantData.telephone }}</p>
              </div>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-4 rounded-lg" style="background-color: rgba(143, 188, 143, 0.1)">
              <p class="text-xs text-gray-500 mb-1">Missions réalisées</p>
              <p class="text-2xl font-bold" style="color: #92B08B">{{ intervenantData.missions }}</p>
            </div>
            <div class="text-center p-4 rounded-lg" style="background-color: rgba(91, 124, 153, 0.1)">
              <p class="text-xs text-gray-500 mb-1">Tâches proposées</p>
              <p class="text-2xl font-bold" style="color: #5B7C99">{{ intervenantData.taches ? intervenantData.taches.length : 0 }}</p>
            </div>
            <div class="text-center p-4 rounded-lg" style="background-color: rgba(255, 193, 7, 0.1)">
              <p class="text-xs text-gray-500 mb-1">Note moyenne</p>
              <p class="text-2xl font-bold" style="color: #FFC107">{{ intervenantData.note }}/5</p>
            </div>
          </div>
        </div>

        <!-- Tâches/Service Tab -->
        <div v-if="!loading && activeTab === 'taches'">
          <!-- Navigation entre services si plusieurs services -->
          <div 
            v-if="intervenantData.allServices && intervenantData.allServices.length > 1"
            class="mb-4 flex items-center justify-between"
          >
            <button
              @click="previousService"
              :disabled="currentServiceIndex === 0"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{
                backgroundColor: currentServiceIndex === 0 ? '#E5E7EB' : '#5B7C99',
                color: currentServiceIndex === 0 ? '#9CA3AF' : 'white'
              }"
            >
              ← Précédent
            </button>
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-600">
                Service {{ currentServiceIndex + 1 }} / {{ intervenantData.allServices.length }}
              </span>
              <div class="flex gap-1.5">
                <div
                  v-for="(service, index) in intervenantData.allServices"
                  :key="index"
                  @click="currentServiceIndex = index"
                  class="w-2 h-2 rounded-full cursor-pointer transition-all"
                  :style="{
                    backgroundColor: currentServiceIndex === index ? '#5B7C99' : '#D1D5DB',
                    width: currentServiceIndex === index ? '24px' : '8px'
                  }"
                ></div>
              </div>
            </div>
            <button
              @click="nextService"
              :disabled="currentServiceIndex === intervenantData.allServices.length - 1"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{
                backgroundColor: currentServiceIndex === intervenantData.allServices.length - 1 ? '#E5E7EB' : '#5B7C99',
                color: currentServiceIndex === intervenantData.allServices.length - 1 ? '#9CA3AF' : 'white'
              }"
            >
              Suivant →
            </button>
          </div>

          <!-- Affichage du service actuel avec plusieurs services -->
          <div 
            v-if="intervenantData.allServices && intervenantData.allServices.length > 0"
          >
            <div class="mb-3 p-2.5 rounded-lg" style="background-color: #F0F9F4">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                  <h4 class="font-semibold text-sm" style="color: #2F4F4F">
                    Service : {{ currentService }}
                  </h4>
                  <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <p class="text-xs text-gray-600">
                      {{ getTachesForService(currentService).length }} tâche(s) proposée(s) pour ce service
                    </p>
                    <span class="text-xs text-gray-500">•</span>
                    <p class="text-xs font-medium" style="color: #2F4F4F">
                      Expérience : {{ getExperienceForCurrentService() }}
                    </p>
                    <!-- Message si service archivé -->
                    <span v-if="isCurrentServiceArchived" class="text-xs text-gray-500 italic">
                      • L'intervenant a archivé ce service
                    </span>
                  </div>
                </div>
                <!-- Toggle Switch pour activer/désactiver le service -->
                <div class="flex flex-col items-end gap-1">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="isCurrentServiceActive"
                      @change="toggleServiceStatus"
                      :disabled="isTogglingService || isCurrentServiceArchived"
                      class="sr-only peer"
                    />
                    <div 
                      class="w-11 h-6 rounded-full transition-colors duration-200 peer-focus:outline-none"
                      :style="{
                        backgroundColor: isCurrentServiceActive ? '#4CAF50' : '#9CA3AF',
                        opacity: isCurrentServiceArchived ? 0.7 : 1
                      }"
                    >
                      <div 
                        class="w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform duration-200 mt-0.5 ml-0.5"
                        :style="{
                          transform: isCurrentServiceActive ? 'translateX(20px)' : 'translateX(0px)'
                        }"
                      ></div>
                    </div>
                  </label>
                  <span v-if="isCurrentServiceArchived" class="text-xs text-gray-500 italic text-right">
                    (Archivé)
                  </span>
                </div>
              </div>
              <!-- Présentation du service -->
              <div v-if="getPresentationForCurrentService()" class="mt-2 pt-2 border-t" style="border-color: #E5E7EB">
                <p class="text-xs text-gray-500 mb-1">Présentation :</p>
                <p class="text-xs text-gray-700 leading-relaxed italic">
                  "{{ getPresentationForCurrentService() }}"
                </p>
              </div>
            </div>

            <!-- Liste des tâches -->
              <div 
              class="space-y-3"
            >
              <div 
                v-for="tache in getTachesForService(currentService)"
                :key="tache.id"
                class="border-2 rounded-lg p-3"
                :style="{ borderColor: getServiceColor() }"
              >
                <h5 class="font-semibold mb-1.5 text-sm" style="color: #2F4F4F">{{ tache.nom }}</h5>
                <p v-if="tache.description" class="text-xs text-gray-600 mb-2">{{ tache.description }}</p>
                <p v-else class="text-xs text-gray-400 italic mb-2">Aucune description</p>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <Clock :size="12" />
                    <span v-if="tache.duree">{{ tache.duree }}</span>
                    <span v-else class="text-gray-400 italic">Durée non précisée</span>
                  </div>
                  <div class="text-lg font-bold" :style="{ color: getServiceColor() }">
                    {{ tache.tarif }}DH/h
                  </div>
                </div>
              </div>
              <div 
                v-if="getTachesForService(currentService).length === 0"
                class="text-center py-6 text-gray-500"
              >
                <p class="text-sm">Aucune tâche disponible pour ce service</p>
              </div>
            </div>
          </div>

          <!-- Fallback if no allServices - avec scrollbar aussi -->
          <div v-if="!intervenantData.allServices || intervenantData.allServices.length === 0">
            <div class="mb-3 p-2.5 rounded-lg" style="background-color: #F0F9F4">
              <h4 class="font-semibold text-sm" style="color: #2F4F4F">Tâches</h4>
              <p class="text-xs text-gray-600">Tâches proposées</p>
            </div>
            <div 
              class="space-y-3"
            >
              <div 
                v-for="tache in (intervenantData.taches || [])"
                :key="tache.id"
                class="border-2 rounded-lg p-3"
                :style="{ borderColor: getServiceColor() }"
              >
                <h5 class="font-semibold mb-1.5 text-sm" style="color: #2F4F4F">{{ tache.nom }}</h5>
                <p v-if="tache.description" class="text-xs text-gray-600 mb-2">{{ tache.description }}</p>
                <p v-else class="text-xs text-gray-400 italic mb-2">Aucune description</p>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <Clock :size="12" />
                    <span v-if="tache.duree">{{ tache.duree }}</span>
                    <span v-else class="text-gray-400 italic">Durée non précisée</span>
                  </div>
                  <div class="text-lg font-bold" :style="{ color: getServiceColor() }">
                    {{ tache.tarif }}DH/h
                  </div>
                </div>
              </div>
              <div 
                v-if="!intervenantData.taches || intervenantData.taches.length === 0"
                class="text-center py-6 text-gray-500"
              >
                <p class="text-sm">Aucune tâche disponible</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Disponibilités Tab -->
        <div v-if="!loading && activeTab === 'dispos'">
          <div class="grid md:grid-cols-2 gap-4">
            <!-- Disponibilités régulières regroupées par jour -->
            <div class="p-4 rounded-lg border" style="border-color: #E5E7EB">
              <div class="flex items-center gap-2 mb-3">
                <Clock :size="16" style="color: #5B7C99" />
                <h4 class="text-sm font-semibold" style="color: #2F4F4F">Disponibilités régulières</h4>
              </div>
              <div v-if="disponibilitesParJour.length" class="space-y-2">
                <div
                  v-for="(jour, idx) in disponibilitesParJour"
                  :key="`jour-${jour.jour}-${idx}`"
                  class="bg-gray-50 rounded-lg p-2.5 text-xs text-gray-700"
                >
                  <div
                    v-for="(slot, sIdx) in jour.slots"
                    :key="`slot-${jour.jour}-${sIdx}`"
                    class="flex items-center justify-between"
                  >
                    <span class="font-semibold" style="color: #2F4F4F">{{ jour.label }}</span>
                    <span>{{ slot.debut }}<span v-if="slot.fin"> - {{ slot.fin }}</span></span>
                  </div>
                </div>
              </div>
              <p v-else class="text-xs text-gray-400 italic">Aucune disponibilité régulière</p>
            </div>

            <!-- Disponibilités ponctuelles -->
            <div class="p-4 rounded-lg border" style="border-color: #E5E7EB">
              <div class="flex items-center gap-2 mb-3">
                <Clock :size="16" style="color: #92B08B" />
                <h4 class="text-sm font-semibold" style="color: #2F4F4F">Disponibilités ponctuelles</h4>
              </div>
              <div v-if="disponibilitesPonctuelles.length" class="space-y-2">
                <div
                  v-for="(d, idx) in disponibilitesPonctuelles.slice(0, dispoPerSection)"
                  :key="`ponc-${d.id || idx}`"
                  class="bg-gray-50 rounded-lg p-2.5 flex items-center justify-between text-xs"
                >
                  <span class="font-semibold" style="color: #2F4F4F">
                    {{ formatDateShort(d.date) || 'Date non précisée' }}
                  </span>
                  <span class="text-gray-700">
                    {{ formatHeure(d.heure_debut) }}<span v-if="d.heure_fin"> - {{ formatHeure(d.heure_fin) }}</span>
                  </span>
                </div>
              </div>
              <p v-else class="text-xs text-gray-400 italic">Aucune disponibilité ponctuelle</p>
            </div>
          </div>
        </div>

        <!-- Avis Tab -->
        <div v-if="!loading && activeTab === 'avis'">
          <!-- Rating Summary -->
          <div class="mb-4 p-4 rounded-lg" style="background-color: #F9FAFB">
            <div class="flex items-center gap-6">
              <div class="text-center">
                <p class="text-4xl font-bold mb-1.5" style="color: #2F4F4F">{{ intervenantData.note }}</p>
                <p class="text-xs text-gray-500">{{ intervenantData.nbAvis }} avis</p>
              </div>
              <div class="flex-1 space-y-1.5">
                <div v-for="i in 5" :key="i" class="flex items-center gap-2">
                  <span class="text-xs text-gray-600 w-6">{{ 6 - i }}★</span>
                  <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div 
                      class="h-full transition-all"
                      :style="{ 
                        width: getRatingPercentage(6 - i) + '%',
                        backgroundColor: '#5B7C99'
                      }"
                    ></div>
                  </div>
                  <span class="text-xs text-gray-500 w-10">{{ getRatingPercentage(6 - i) }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Reviews List -->
          <div v-if="avisList.length > 0" class="space-y-3">
            <div 
              v-for="(avis, index) in paginatedAvis"
              :key="index"
              class="border-b pb-3"
            >
              <div class="flex items-start justify-between mb-1.5">
                <div>
                  <p class="font-semibold text-sm" style="color: #2F4F4F">{{ avis.clientNom }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(avis.date) }}</p>
                </div>
                <div class="flex">
                  <Star 
                    v-for="i in 5" 
                    :key="i"
                    :size="14" 
                    :fill="i <= avis.note ? '#FFC107' : 'none'"
                    :stroke="i <= avis.note ? '#FFC107' : '#E5E7EB'"
                  />
                </div>
              </div>
              <p v-if="avis.commentaire" class="text-xs text-gray-700">"{{ avis.commentaire }}"</p>
            </div>
          </div>
          <div v-else class="text-center py-6 text-gray-500">
            <p class="text-sm">Aucun avis pour le moment</p>
          </div>

          <!-- Pagination Controls for Avis -->
          <div 
            v-if="avisList.length > 0 && totalAvisPages > 1"
            class="flex items-center justify-between mt-3 pt-3 border-t"
            style="border-color: #E5E7EB"
          >
            <button
              @click="currentAvisPage = currentAvisPage - 1"
              :disabled="currentAvisPage === 1"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{
                backgroundColor: currentAvisPage === 1 ? '#E5E7EB' : '#5B7C99',
                color: currentAvisPage === 1 ? '#9CA3AF' : 'white'
              }"
            >
              ← Précédent
            </button>
            
            <div class="flex items-center gap-2">
              <span class="text-xs" style="color: #6B7280">
                Page {{ currentAvisPage }} / {{ totalAvisPages }}
              </span>
              <div class="flex gap-1">
                <div
                  v-for="page in totalAvisPages"
                  :key="page"
                  @click="currentAvisPage = page"
                  class="w-2 h-2 rounded-full cursor-pointer transition-all"
                  :style="{
                    backgroundColor: currentAvisPage === page ? '#5B7C99' : '#D1D5DB',
                    width: currentAvisPage === page ? '24px' : '8px'
                  }"
                ></div>
              </div>
            </div>
            
            <button
              @click="currentAvisPage = currentAvisPage + 1"
              :disabled="currentAvisPage === totalAvisPages"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              :style="{
                backgroundColor: currentAvisPage === totalAvisPages ? '#E5E7EB' : '#5B7C99',
                color: currentAvisPage === totalAvisPages ? '#9CA3AF' : 'white'
              }"
            >
              Suivant →
            </button>
          </div>
        </div>

        <!-- Photos Tab -->
        <div v-if="!loading && activeTab === 'photos'">
          <div v-if="photosList.length === 0" class="text-center py-6 text-gray-500">
            <ImageIcon :size="48" class="mx-auto mb-3 text-gray-300" />
            <p class="text-sm">Aucune photo disponible</p>
          </div>
          <div v-else>
            <div class="grid grid-cols-3 gap-3">
            <div 
                v-for="(photo, index) in paginatedPhotos"
              :key="`photo-${photo.id}-${index}`"
              class="aspect-square rounded-lg overflow-hidden bg-gray-100 relative group cursor-pointer"
              @click="openPhotoModal(photo)"
            >
              <img 
                v-if="getPhotoUrl(photo.photo_path)"
                :src="getPhotoUrl(photo.photo_path)"
                :alt="photo.description || 'Photo d\'intervention'"
                class="w-full h-full object-cover"
                @error="(e) => handleImageError(e, photo)"
                @load="() => {}"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <ImageIcon :size="36" class="text-gray-300" />
              </div>
              <!-- Overlay avec description -->
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all flex items-end p-1.5">
                <p v-if="photo.description" class="text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                  {{ photo.description }}
                </p>
              </div>
              </div>
            </div>

            <!-- Pagination Controls for Photos -->
            <div 
              v-if="photosList.length > 0 && totalPhotosPages > 1"
              class="flex items-center justify-between mt-4 pt-3 border-t"
              style="border-color: #E5E7EB"
            >
              <button
                @click="currentPhotosPage = currentPhotosPage - 1"
                :disabled="currentPhotosPage === 1"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :style="{
                  backgroundColor: currentPhotosPage === 1 ? '#E5E7EB' : '#5B7C99',
                  color: currentPhotosPage === 1 ? '#9CA3AF' : 'white'
                }"
              >
                ← Précédent
              </button>
              
              <div class="flex items-center gap-2">
                <span class="text-xs" style="color: #6B7280">
                  Page {{ currentPhotosPage }} / {{ totalPhotosPages }}
                </span>
                <div class="flex gap-1">
                  <div
                    v-for="page in totalPhotosPages"
                    :key="page"
                    @click="currentPhotosPage = page"
                    class="w-2 h-2 rounded-full cursor-pointer transition-all"
                    :style="{
                      backgroundColor: currentPhotosPage === page ? '#5B7C99' : '#D1D5DB',
                      width: currentPhotosPage === page ? '24px' : '8px'
                    }"
                  ></div>
                </div>
              </div>
              
              <button
                @click="currentPhotosPage = currentPhotosPage + 1"
                :disabled="currentPhotosPage === totalPhotosPages"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :style="{
                  backgroundColor: currentPhotosPage === totalPhotosPages ? '#E5E7EB' : '#5B7C99',
                  color: currentPhotosPage === totalPhotosPages ? '#9CA3AF' : 'white'
                }"
              >
                Suivant →
              </button>
            </div>
          </div>
        </div>

        <!-- Documents Tab -->
        <div v-if="!loading && activeTab === 'documents'">
          <div v-if="documentsList.length === 0" class="text-center py-6 text-gray-500">
            <FileText :size="48" class="mx-auto mb-3 text-gray-300" />
            <p class="text-sm">Aucun document disponible</p>
          </div>
          <div v-else>
            <div class="space-y-2.5">
            <div 
                v-for="(doc, index) in paginatedDocuments"
              :key="`doc-${doc.id}-${index}`"
              class="flex items-center justify-between p-3 border-2 border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
            >
              <div class="flex items-center gap-2.5">
                <FileText :size="16" style="color: #92B08B" />
                <div>
                  <p class="font-medium text-xs" style="color: #2F4F4F">{{ doc.nom }}</p>
                  <p v-if="doc.date" class="text-xs text-gray-500">Ajouté le {{ formatDate(doc.date) }}</p>
                </div>
              </div>
              <button 
                @click="downloadJustificatif(doc)"
                class="px-3 py-1.5 rounded-lg text-xs font-medium text-white transition-colors hover:opacity-90"
                style="background-color: #5B7C99"
              >
                Télécharger
                </button>
              </div>
            </div>

            <!-- Pagination Controls for Documents -->
            <div 
              v-if="documentsList.length > 0 && totalDocumentsPages > 1"
              class="flex items-center justify-between mt-4 pt-3 border-t"
              style="border-color: #E5E7EB"
            >
              <button
                @click="currentDocumentsPage = currentDocumentsPage - 1"
                :disabled="currentDocumentsPage === 1"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :style="{
                  backgroundColor: currentDocumentsPage === 1 ? '#E5E7EB' : '#5B7C99',
                  color: currentDocumentsPage === 1 ? '#9CA3AF' : 'white'
                }"
              >
                ← Précédent
              </button>
              
              <div class="flex items-center gap-2">
                <span class="text-xs" style="color: #6B7280">
                  Page {{ currentDocumentsPage }} / {{ totalDocumentsPages }}
                </span>
                <div class="flex gap-1">
                  <div
                    v-for="page in totalDocumentsPages"
                    :key="page"
                    @click="currentDocumentsPage = page"
                    class="w-2 h-2 rounded-full cursor-pointer transition-all"
                    :style="{
                      backgroundColor: currentDocumentsPage === page ? '#5B7C99' : '#D1D5DB',
                      width: currentDocumentsPage === page ? '24px' : '8px'
                    }"
                  ></div>
                </div>
              </div>
              
              <button
                @click="currentDocumentsPage = currentDocumentsPage + 1"
                :disabled="currentDocumentsPage === totalDocumentsPages"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :style="{
                  backgroundColor: currentDocumentsPage === totalDocumentsPages ? '#E5E7EB' : '#5B7C99',
                  color: currentDocumentsPage === totalDocumentsPages ? '#9CA3AF' : 'white'
                }"
              >
                Suivant →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t flex justify-between items-center">
        <button
          @click="$emit('suspend', intervenantData.id)"
          class="px-4 py-2 rounded-lg text-xs font-medium transition-colors border-2"
          :style="{
            borderColor: intervenantData.statut === 'actif' ? '#EF4444' : '#4CAF50',
            color: intervenantData.statut === 'actif' ? '#EF4444' : '#4CAF50'
          }"
        >
          {{ intervenantData.statut === 'actif' ? 'Suspendre le compte' : 'Activer le compte' }}
        </button>
        <button
          @click="$emit('close')"
          class="px-4 py-2 rounded-lg text-xs font-medium text-white"
          style="background-color: #5B7C99"
        >
          Fermer
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import { 
  X, User, Check, Star, MapPin, Mail, Phone, Clock, 
  FileText, ImageIcon 
} from 'lucide-vue-next'
import adminService from '@/services/adminService'
import { useServiceColor } from '@/composables/useServiceColor'
import { useAdminRealtimeSync } from '@/composables/useAdminRealtimeSync'

const { getServiceColor: getServiceColorUtil } = useServiceColor()

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  intervenant: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'suspend'])
const { error: showError, success, info } = useNotifications()

const activeTab = ref('apropos')
const fullIntervenantData = ref(null)
const loading = ref(false)
const currentServiceIndex = ref(0)
const currentAvisPage = ref(1)
const avisPerPage = 4
const currentPhotosPage = ref(1)
const photosPerPage = 4
const currentDocumentsPage = ref(1)
const documentsPerPage = 4
const dispoPerSection = 10
const allServices = ref([])
const isTogglingService = ref(false)

const tabs = [
  { id: 'apropos', label: 'À propos' },
  { id: 'taches', label: 'Tâches/Service' },
  { id: 'dispos', label: 'Disponibilités' },
  { id: 'avis', label: 'Avis' },
  { id: 'photos', label: 'Photos' },
  { id: 'documents', label: 'Documents' }
]

const loadServices = async () => {
  try {
    const response = await adminService.getServices()
    // Handle paginated response structure (new) or direct array (old for compatibility)
    allServices.value = response.data?.data || response.data || []
  } catch (error) {
    console.error('Erreur chargement services:', error)
    allServices.value = []
  }
}

const fetchIntervenantDetails = async (id, options = {}) => {
  const { silent = false } = options
  try {
    if (!silent) {
      loading.value = true
    }
    const response = await adminService.getIntervenantDetails(id)
    
    if (silent && fullIntervenantData.value) {
      // Mise à jour intelligente : fusionner les données existantes avec les nouvelles
      const newData = response.data
      Object.keys(newData).forEach(key => {
        if (JSON.stringify(fullIntervenantData.value[key]) !== JSON.stringify(newData[key])) {
          fullIntervenantData.value[key] = newData[key]
        }
      })
    } else {
      fullIntervenantData.value = response.data
    }
    
    // Debug: Log the response to see if allServicesWithDetailsAll is present
    console.log('fetchIntervenantDetails response:', {
      hasAllServicesWithDetailsAll: !!response.data?.allServicesWithDetailsAll,
      allServicesWithDetailsAll: response.data?.allServicesWithDetailsAll,
      allServices: response.data?.allServices
    })
  } catch (error) {
    console.error('Erreur lors du chargement des détails:', error)
    if (!silent) {
      // Fallback to props data if API call fails
      fullIntervenantData.value = props.intervenant
    }
  } finally {
    if (!silent) {
      loading.value = false
    }
  }
}

// Fetch full intervenant details when modal opens
let isFetching = false
watch([() => props.isOpen, () => props.intervenant?.id], async ([isOpen, intervenantId]) => {
  if (isOpen && intervenantId && !isFetching) {
    isFetching = true
    await fetchIntervenantDetails(intervenantId)
    isFetching = false
  } else if (!isOpen) {
    // Réinitialiser quand le modal se ferme
    fullIntervenantData.value = null
    isFetching = false
  }
}, { immediate: true })

// Synchronisation en temps réel - Recharger les détails de l'intervenant toutes les 3 secondes quand le modal est ouvert
const { start: startSync, stop: stopSync } = useAdminRealtimeSync(
  async ({ silent = false }) => {
    if (props.isOpen && props.intervenant?.id && !isFetching) {
      await fetchIntervenantDetails(props.intervenant.id, { silent })
    }
  },
  { interval: 3000, loadOnMount: false, enabled: false }
)

// Démarrer/arrêter la synchronisation selon l'état du modal
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.intervenant?.id) {
    startSync()
  } else {
    stopSync()
  }
}, { immediate: true })

// Use fullIntervenantData if available, otherwise fallback to props.intervenant
const intervenantData = computed(() => {
  return fullIntervenantData.value || props.intervenant
})

const disponibilitesPonctuelles = computed(() => {
  if (!intervenantData.value || !intervenantData.value.disponibilites) return []
  return intervenantData.value.disponibilites.filter(d => d.type === 'ponctuelle')
})

const disponibilitesRegulieres = computed(() => {
  if (!intervenantData.value || !intervenantData.value.disponibilites) return []
  return intervenantData.value.disponibilites.filter(d => d.type === 'reguliere')
})

const disponibilitesParJour = computed(() => {
  if (!disponibilitesRegulieres.value.length) return []
  const joursOrder = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche']
  const label = {
    'lundi': 'Lundi',
    'mardi': 'Mardi',
    'mercredi': 'Mercredi',
    'jeudi': 'Jeudi',
    'vendredi': 'Vendredi',
    'samedi': 'Samedi',
    'dimanche': 'Dimanche'
  }
  const grouped = joursOrder.map(j => ({
    jour: j,
    label: label[j],
    slots: []
  }))
  disponibilitesRegulieres.value.forEach(d => {
    const target = grouped.find(g => g.jour === d.jour || g.jour === d.jours_semaine)
    if (target) {
      target.slots.push({
        debut: formatHeure(d.heure_debut),
        fin: formatHeure(d.heure_fin)
      })
    }
  })
  return grouped.filter(g => g.slots.length)
})

// Récupérer les photos depuis les données complètes (avec déduplication robuste)
const photosList = computed(() => {
  if (!intervenantData.value || !intervenantData.value.photos) return []
  
  // Déduplication stricte basée sur l'ID ET le photo_path
  const seenIds = new Set()
  const seenPaths = new Set()
  const uniquePhotos = []
  
  for (const photo of intervenantData.value.photos) {
    // Convertir l'ID en string pour éviter les problèmes de type
    const photoId = String(photo?.id || '')
    const photoPath = photo?.photo_path || ''
    
    // Vérifier par ID ET par photo_path
    const idSeen = photoId && seenIds.has(photoId)
    const pathSeen = photoPath && seenPaths.has(photoPath)
    
    if (!idSeen && !pathSeen) {
      if (photoId) seenIds.add(photoId)
      if (photoPath) seenPaths.add(photoPath)
      uniquePhotos.push(photo)
    }
  }
  
  return uniquePhotos
})

// Pagination pour les photos
const paginatedPhotos = computed(() => {
  if (!photosList.value || photosList.value.length === 0) return []
  
  const start = (currentPhotosPage.value - 1) * photosPerPage
  const end = start + photosPerPage
  return photosList.value.slice(start, end)
})

// Calculer le nombre total de pages pour les photos
const totalPhotosPages = computed(() => {
  if (!photosList.value || photosList.value.length === 0) return 0
  return Math.ceil(photosList.value.length / photosPerPage)
})

// Récupérer les justificatifs depuis les données complètes (avec déduplication robuste)
const documentsList = computed(() => {
  if (!intervenantData.value || !intervenantData.value.justificatifs) return []
  
  // Déduplication stricte basée sur l'ID ET la combinaison type + chemin_fichier
  const seenIds = new Set()
  const seenCombinations = new Set()
  const uniqueDocs = []
  
  for (const doc of intervenantData.value.justificatifs) {
    // Convertir l'ID en string pour éviter les problèmes de type
    const docId = String(doc?.id || '')
    const type = doc?.type || ''
    const cheminFichier = doc?.chemin_fichier || ''
    const combination = `${type}|${cheminFichier}`
    
    // Vérifier par ID ET par combinaison type + chemin
    const idSeen = docId && seenIds.has(docId)
    const combinationSeen = combination && seenCombinations.has(combination)
    
    if (!idSeen && !combinationSeen) {
      if (docId) seenIds.add(docId)
      if (combination) seenCombinations.add(combination)
      uniqueDocs.push(doc)
    }
  }
  
  return uniqueDocs.map(doc => ({
    id: doc.id,
    nom: doc.type,
    date: doc.created_at,
    chemin_fichier: doc.chemin_fichier
  }))
})

// Pagination pour les documents
const paginatedDocuments = computed(() => {
  if (!documentsList.value || documentsList.value.length === 0) return []
  
  const start = (currentDocumentsPage.value - 1) * documentsPerPage
  const end = start + documentsPerPage
  return documentsList.value.slice(start, end)
})

// Calculer le nombre total de pages pour les documents
const totalDocumentsPages = computed(() => {
  if (!documentsList.value || documentsList.value.length === 0) return 0
  return Math.ceil(documentsList.value.length / documentsPerPage)
})

// Récupérer les avis depuis les données complètes
const avisList = computed(() => {
  if (!intervenantData.value || !intervenantData.value.avis) return []
  return intervenantData.value.avis
})

// Pagination pour les avis
const paginatedAvis = computed(() => {
  if (!avisList.value || avisList.value.length === 0) return []
  
  const start = (currentAvisPage.value - 1) * avisPerPage
  const end = start + avisPerPage
  return avisList.value.slice(start, end)
})

// Calculer le nombre total de pages pour les avis
const totalAvisPages = computed(() => {
  if (!avisList.value || avisList.value.length === 0) return 0
  return Math.ceil(avisList.value.length / avisPerPage)
})

// Réinitialiser les pages quand l'onglet change ou quand l'intervenant change
watch([() => activeTab.value, () => props.intervenant?.id], () => {
  currentAvisPage.value = 1
  currentPhotosPage.value = 1
  currentDocumentsPage.value = 1
})

// Télécharger un justificatif
const downloadJustificatif = async (justificatif) => {
  try {
    // Utiliser la route API dédiée pour le téléchargement
    const response = await adminService.downloadJustificatif(justificatif.id)
    
    // Extraire le nom du fichier depuis le chemin ou utiliser le type comme nom
    const fileName = justificatif.chemin_fichier.split('/').pop() || `${justificatif.nom || 'document'}.pdf`
    
    // Créer un URL temporaire pour le blob
    const blobUrl = window.URL.createObjectURL(response.data)
    
    // Créer un élément <a> invisible pour déclencher le téléchargement
    const link = document.createElement('a')
    link.href = blobUrl
    link.download = fileName
    
    // Ajouter au DOM, cliquer, puis supprimer
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    // Libérer l'URL du blob après un court délai
    setTimeout(() => {
      window.URL.revokeObjectURL(blobUrl)
    }, 100)
    
    success('Document téléchargé avec succès')
  } catch (error) {
    console.error('Erreur détaillée lors du téléchargement:', error)
    
    // Message d'erreur plus informatif
    let errorMessage = 'Erreur lors du téléchargement du document.'
    
    if (error.response) {
      const status = error.response.status
      if (status === 404) {
        errorMessage = 'Le fichier demandé est introuvable sur le serveur.'
      } else if (status === 403 || status === 401) {
        errorMessage = 'Vous n\'êtes pas autorisé à télécharger ce fichier.'
      } else {
        errorMessage = `Erreur ${status}: ${error.response.data?.message || error.response.statusText}`
      }
    } else if (error.message) {
      if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
        errorMessage = 'Impossible de se connecter au serveur. Vérifiez votre connexion internet.'
      } else {
        errorMessage = `Erreur: ${error.message}`
      }
    }
    
    showError(errorMessage + ' Veuillez réessayer.')
  }
}

// Obtenir l'URL complète d'une photo
const getPhotoUrl = (photoPath) => {
  if (!photoPath) return null
  
  // Si le chemin commence déjà par http, retourner tel quel
  if (photoPath.startsWith('http')) {
    return photoPath
  }
  
  // Utiliser VITE_API_BASE_URL si défini, sinon extraire l'URL de base de VITE_API_URL
  let baseUrl = import.meta.env.VITE_API_BASE_URL
  
  if (!baseUrl) {
    // Extraire l'URL de base de VITE_API_URL (enlever /api à la fin)
    const apiUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
    baseUrl = apiUrl.replace(/\/api\/?$/, '')
  }
  
  // S'assurer que le chemin commence par /
  const cleanPath = photoPath.startsWith('/') ? photoPath : `/${photoPath}`
  
  return `${baseUrl}${cleanPath}`
}

const getServiceColor = () => {
  if (!intervenantData.value) return '#5B7C99'
  
  let serviceToCheck = intervenantData.value.service
  if (intervenantData.value.allServices && intervenantData.value.allServices.length > 0) {
    serviceToCheck = intervenantData.value.allServices[currentServiceIndex.value] || intervenantData.value.allServices[0]
  }
  
  // Trouver le service dans la liste chargée pour obtenir son ID
  const serviceData = allServices.value.find(s => (s.nom_service || s.nom) === serviceToCheck)
  
  return getServiceColorUtil(serviceToCheck, serviceData?.id, allServices.value)
}

const getMainService = () => {
  if (!intervenantData.value) return 'service'
  
  if (intervenantData.value.allServices && intervenantData.value.allServices.length > 0) {
    return intervenantData.value.allServices[0].toLowerCase()
  }
  
  return intervenantData.value.service?.toLowerCase() || 'service'
}

const getExperience = () => {
  // Si l'intervenant a une expérience renseignée, l'utiliser
  if (intervenantData.value && intervenantData.value.experience) {
    return intervenantData.value.experience
  }
  
  // Sinon, calculer depuis la date d'inscription (fallback)
  if (!intervenantData.value || !intervenantData.value.dateInscription) return 'Expérience non précisée'
  
  const inscriptionDate = new Date(intervenantData.value.dateInscription)
  const now = new Date()
  
  // Calculer la différence en années
  const years = now.getFullYear() - inscriptionDate.getFullYear()
  const months = now.getMonth() - inscriptionDate.getMonth()
  
  // Si le mois d'anniversaire n'est pas encore passé cette année, retirer 1 an
  let experienceYears = years
  if (months < 0 || (months === 0 && now.getDate() < inscriptionDate.getDate())) {
    experienceYears = years - 1
  }
  
  // Toujours afficher les années d'expérience (même si c'est 0)
  return `${experienceYears >= 0 ? experienceYears : 0} an${experienceYears > 1 ? 's' : ''} d'expérience`
}

// Service actuel basé sur l'index
const currentService = computed(() => {
  if (!intervenantData.value) return null
  
  // Priorité à allServices si disponible
  if (intervenantData.value.allServices && Array.isArray(intervenantData.value.allServices) && intervenantData.value.allServices.length > 0) {
    return intervenantData.value.allServices[currentServiceIndex.value] || intervenantData.value.allServices[0]
  }
  
  // Fallback : utiliser allServicesWithDetails si allServices n'est pas disponible
  if (intervenantData.value.allServicesWithDetails && Array.isArray(intervenantData.value.allServicesWithDetails) && intervenantData.value.allServicesWithDetails.length > 0) {
    const service = intervenantData.value.allServicesWithDetails[currentServiceIndex.value] || intervenantData.value.allServicesWithDetails[0]
    return service?.nom_service || service?.nom || null
  }
  
  return null
})

// Récupérer l'expérience pour le service actuel depuis allServicesWithDetails ou allServicesWithDetailsAll
const getExperienceForCurrentService = () => {
  if (!intervenantData.value) return 'Expérience non précisée'
  
  const currentServiceName = currentService.value
  if (!currentServiceName) {
    // Fallback si pas de service actuel
    if (intervenantData.value.experience && 
        intervenantData.value.experience !== '' && 
        intervenantData.value.experience !== null &&
        intervenantData.value.experience !== undefined &&
        intervenantData.value.experience !== 'Expérience non précisée') {
      return intervenantData.value.experience
    }
    return 'Expérience non précisée'
  }
  
  // Priorité : utiliser allServicesWithDetailsAll (TOUS les services) pour trouver l'expérience
  // Cela permet de récupérer l'expérience même si le service n'est pas actif
  let servicesToSearch = []
  
  if (intervenantData.value.allServicesWithDetailsAll && 
      Array.isArray(intervenantData.value.allServicesWithDetailsAll) && 
      intervenantData.value.allServicesWithDetailsAll.length > 0) {
    servicesToSearch = intervenantData.value.allServicesWithDetailsAll
  } else if (intervenantData.value.allServicesWithDetails && 
             Array.isArray(intervenantData.value.allServicesWithDetails) && 
             intervenantData.value.allServicesWithDetails.length > 0) {
    // Fallback : utiliser allServicesWithDetails si allServicesWithDetailsAll n'est pas disponible
    servicesToSearch = intervenantData.value.allServicesWithDetails
  }
  
  if (servicesToSearch.length > 0) {
    // Chercher le service par nom (comparaison insensible à la casse)
    const serviceDetails = servicesToSearch.find(s => {
      const serviceNom = s.nom_service || s.nom
      if (!s || !serviceNom || !currentServiceName) return false
      return serviceNom.trim().toLowerCase() === currentServiceName.trim().toLowerCase()
    })
    
    if (serviceDetails) {
      // Vérifier si l'expérience existe et n'est pas vide/null/undefined
      const experience = serviceDetails.experience
      if (experience && 
          experience !== '' && 
          experience !== null && 
          experience !== undefined &&
          experience !== 'Expérience non précisée') {
        return experience
      }
    }
  }
  
  // Fallback : utiliser l'expérience générale si elle existe et correspond au service actuel
  // (pour compatibilité avec l'ancien format ou quand les données ne sont pas encore chargées)
  if (intervenantData.value.experience && 
      intervenantData.value.experience !== '' && 
      intervenantData.value.experience !== null &&
      intervenantData.value.experience !== undefined &&
      intervenantData.value.experience !== 'Expérience non précisée' &&
      currentServiceIndex.value === 0) {
    return intervenantData.value.experience
  }
  
  return 'Expérience non précisée'
}

// Récupérer la présentation pour le service actuel depuis allServicesWithDetails
const getPresentationForCurrentService = () => {
  if (!intervenantData.value) return null
  
  const currentServiceName = currentService.value
  if (!currentServiceName) return null
  
  // Utiliser allServicesWithDetails (services actifs) pour la présentation
  // Car on affiche seulement les services actifs
  if (intervenantData.value.allServicesWithDetails && 
      Array.isArray(intervenantData.value.allServicesWithDetails) && 
      intervenantData.value.allServicesWithDetails.length > 0) {
    
    // Récupérer le service à l'index actuel (services actifs seulement)
    const serviceDetails = intervenantData.value.allServicesWithDetails[currentServiceIndex.value] || 
                          intervenantData.value.allServicesWithDetails[0]
    
    if (serviceDetails && serviceDetails.presentation) {
      return serviceDetails.presentation
    }
  }
  
  return null
}

// Réinitialiser l'index quand on change d'intervenant ou d'onglet
watch([() => props.intervenant?.id, () => activeTab.value], () => {
  currentServiceIndex.value = 0
})

// Navigation entre services
const nextService = () => {
  if (intervenantData.value && intervenantData.value.allServices) {
    if (currentServiceIndex.value < intervenantData.value.allServices.length - 1) {
      currentServiceIndex.value++
    }
  }
}

const previousService = () => {
  if (currentServiceIndex.value > 0) {
    currentServiceIndex.value--
  }
}

const getTachesForService = (service) => {
  if (!intervenantData.value || !intervenantData.value.taches) return []
  
  // Si un service spécifique est fourni, filtrer les tâches par service
  if (service && intervenantData.value.taches) {
    return intervenantData.value.taches.filter(tache => {
      // Le champ service contient le nom du service (nom_service)
      // Filtrer strictement : seulement les tâches qui correspondent au service
      return tache.service && tache.service === service
    })
  }
  
  // Retourner toutes les tâches si aucun service spécifique
  return intervenantData.value.taches
}

const getRatingPercentage = (star) => {
  if (!intervenantData.value || intervenantData.value.nbAvis === 0) return 0
  
  // Utiliser les vraies données de répartition depuis la BD
  if (intervenantData.value.repartitionNotes && intervenantData.value.repartitionNotes[star] !== undefined) {
    return intervenantData.value.repartitionNotes[star]
  }
  
  return 0
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatDateShort = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

const formatHeure = (timeString) => {
  if (!timeString) return ''
  // Accepte "HH:MM:SS" ou "YYYY-MM-DDTHH:MM:SS"
  const clean = timeString.includes('T') ? timeString.split('T')[1] : timeString
  const [h, m] = clean.split(':')
  const hh = h?.padStart(2, '0') || '00'
  const mm = m?.padStart(2, '0') || '00'
  return `${hh}:${mm}`
}

// Ouvrir une photo en modal (simple version pour l'instant)
const openPhotoModal = (photo) => {
  const photoUrl = getPhotoUrl(photo.photo_path)
  if (photoUrl) {
    window.open(photoUrl, '_blank')
  }
}

// Gérer les erreurs de chargement d'image
const handleImageError = (event, photo) => {
  // Afficher l'URL dans la console pour debug
  const imageUrl = event.target.src
  console.error('Erreur de chargement de l\'image:', imageUrl)
  console.error('Données de la photo:', photo)
  
  // Si l'image ne charge pas, afficher un placeholder avec l'URL
  const parent = event.target.parentElement
  if (parent && !parent.querySelector('.error-placeholder')) {
    const placeholder = document.createElement('div')
    placeholder.className = 'error-placeholder w-full h-full flex flex-col items-center justify-center bg-gray-100 p-2'
    placeholder.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <polyline points="21 15 16 10 5 21"/>
      </svg>
      <p class="text-xs text-gray-500 text-center break-all px-2">${imageUrl}</p>
    `
    parent.innerHTML = ''
    parent.appendChild(placeholder)
  }
}

// Obtenir le statut du service actuel
const currentServiceStatus = computed(() => {
  if (!intervenantData.value || !intervenantData.value.allServicesWithDetailsAll) {
    return null
  }
  
  // Vérifier que l'index est valide
  const index = currentServiceIndex.value
  if (index < 0 || index >= intervenantData.value.allServicesWithDetailsAll.length) {
    return null
  }
  
  // Utiliser l'index pour trouver le service correspondant dans allServicesWithDetailsAll
  // car allServices et allServicesWithDetailsAll ont le même ordre (construits depuis $tousServices)
  const serviceDetails = intervenantData.value.allServicesWithDetailsAll[index]
  
  // Retourner le statut si disponible
  return serviceDetails?.status || null
})

// Vérifier si le service actuel est actif ou archivé (toggle vert)
const isCurrentServiceActive = computed(() => {
  const status = currentServiceStatus.value
  // Le toggle est vert si le service est 'active' ou 'archive'
  return status === 'active' || status === 'archive'
})

// Vérifier si le service est archivé
const isCurrentServiceArchived = computed(() => {
  return currentServiceStatus.value === 'archive'
})

// Récupérer l'ID du service actuel
const getCurrentServiceId = () => {
  if (!intervenantData.value || !intervenantData.value.allServicesWithDetailsAll) {
    console.error('getCurrentServiceId: Missing data', {
      hasIntervenantData: !!intervenantData.value,
      hasAllServicesWithDetailsAll: !!intervenantData.value?.allServicesWithDetailsAll
    })
    return null
  }
  
  // Debug: Log current state
  console.log('getCurrentServiceId Debug:', {
    currentServiceIndex: currentServiceIndex.value,
    allServices: intervenantData.value.allServices,
    allServicesWithDetailsAll: intervenantData.value.allServicesWithDetailsAll,
    allServicesLength: intervenantData.value.allServices?.length,
    allServicesWithDetailsAllLength: intervenantData.value.allServicesWithDetailsAll?.length
  })
  
  // Vérifier que l'index est valide
  const index = currentServiceIndex.value
  if (index < 0 || index >= intervenantData.value.allServicesWithDetailsAll.length) {
    console.error('getCurrentServiceId: Invalid index', {
      index,
      maxIndex: intervenantData.value.allServicesWithDetailsAll.length - 1
    })
    return null
  }
  
  // Utiliser l'index pour trouver le service correspondant dans allServicesWithDetailsAll
  // car allServices et allServicesWithDetailsAll ont le même ordre (construits depuis $tousServices)
  const serviceDetails = intervenantData.value.allServicesWithDetailsAll[index]
  
  console.log('getCurrentServiceId: Service found', {
    index,
    serviceDetails
  })
  
  // Retourner l'ID si disponible
  return serviceDetails?.id || null
}

// Toggle le statut du service
const toggleServiceStatus = async () => {
  if (!intervenantData.value || !intervenantData.value.id) {
    showError('Erreur: données de l\'intervenant non disponibles')
    return
  }
  
  // Ne pas permettre le toggle si le service est archivé
  if (isCurrentServiceArchived.value) {
    info('Ce service est archivé par l\'intervenant. L\'admin ne peut pas le modifier.')
    return
  }
  
  const serviceId = getCurrentServiceId()
  if (!serviceId) {
    showError('Erreur: impossible de récupérer l\'ID du service')
    return
  }
  
  try {
    isTogglingService.value = true
    
    const response = await adminService.toggleIntervenantServiceStatus(
      intervenantData.value.id,
      serviceId
    )
    
    // Mettre à jour le statut dans allServicesWithDetailsAll immédiatement pour une réactivité instantanée
    if (intervenantData.value.allServicesWithDetailsAll) {
      const serviceIndex = intervenantData.value.allServicesWithDetailsAll.findIndex(s => {
        const serviceNom = s.nom_service || s.nom
        const currentServiceName = currentService.value
        if (!serviceNom || !currentServiceName) return false
        return serviceNom.trim().toLowerCase() === currentServiceName.trim().toLowerCase()
      })
      
      if (serviceIndex !== -1) {
        // Mettre à jour le statut avec la valeur retournée par le serveur
        intervenantData.value.allServicesWithDetailsAll[serviceIndex].status = response.data.status
      }
    }
    
    // Rafraîchir les données pour avoir les informations à jour depuis le serveur
    await fetchIntervenantDetails(intervenantData.value.id)
    
    success(response.data.message || 'Statut du service mis à jour avec succès')
  } catch (error) {
    console.error('Erreur lors du toggle du service:', error)
    showError(error.response?.data?.error || 'Erreur lors de la modification du statut du service')
  } finally {
    isTogglingService.value = false
  }
}

onMounted(async () => {
  await loadServices()
})
</script>

<style scoped>
/* Smooth animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.fixed > div {
  animation: fadeIn 0.2s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>
