<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header avec bouton retour -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center gap-4">
          <button
            @click="handleBack"
            class="flex items-center justify-center w-10 h-10 rounded-full transition-all hover:bg-gray-100"
          >
            <ArrowLeft :size="24" class="text-gray-600" />
          </button>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Réservation</h1>
            <p class="text-sm text-gray-500">pour {{ intervenant?.name || 'Intervenant' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Steps -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-center gap-4">
          <div class="flex items-center">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center text-white"
              :class="currentStep >= 1 ? 'bg-green-500' : 'bg-gray-300'"
            >
              <Check v-if="currentStep > 1" :size="20" />
              <Calendar v-else :size="20" />
            </div>
            <span class="ml-2 font-medium" :class="currentStep >= 1 ? 'text-green-600' : 'text-gray-400'">
              Service
            </span>
          </div>
          <div class="w-12 h-1" :class="currentStep >= 2 ? 'bg-green-500' : 'bg-gray-300'"></div>
          <div class="flex items-center">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center text-white"
              :class="currentStep >= 2 ? 'bg-green-500' : currentStep === 2 ? 'bg-blue-500' : 'bg-gray-300'"
            >
              <Check v-if="currentStep > 2" :size="20" />
              <Clock v-else :size="20" />
            </div>
            <span class="ml-2 font-medium" :class="currentStep >= 2 ? 'text-green-600' : currentStep === 2 ? 'text-blue-600' : 'text-gray-400'">
              Tâche
            </span>
          </div>
          <div class="w-12 h-1" :class="currentStep >= 3 ? 'bg-green-500' : 'bg-gray-300'"></div>
          <div class="flex items-center">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center text-white"
              :class="currentStep >= 3 ? 'bg-green-500' : currentStep === 3 ? 'bg-blue-500' : 'bg-gray-300'"
            >
              <Check v-if="currentStep > 3" :size="20" />
              <FileText v-else :size="20" />
            </div>
            <span class="ml-2 font-medium" :class="currentStep >= 3 ? 'text-green-600' : currentStep === 3 ? 'text-blue-600' : 'text-gray-400'">
              Détails
            </span>
          </div>
          <div class="w-12 h-1" :class="currentStep >= 4 ? 'bg-green-500' : 'bg-gray-300'"></div>
          <div class="flex items-center">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center text-white"
              :class="currentStep >= 4 ? 'bg-green-500' : currentStep === 4 ? 'bg-blue-500' : 'bg-gray-300'"
            >
              <Calendar :size="20" />
            </div>
            <span class="ml-2 font-medium" :class="currentStep >= 4 ? 'text-green-600' : currentStep === 4 ? 'text-blue-600' : 'text-gray-400'">
              Date
            </span>
          </div>
          <div class="w-12 h-1" :class="currentStep >= 5 ? 'bg-green-500' : 'bg-gray-300'"></div>
          <div class="flex items-center">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center text-white"
              :class="currentStep >= 5 ? 'bg-blue-500' : 'bg-gray-300'"
            >
              <Clock :size="20" />
            </div>
            <span class="ml-2 font-medium" :class="currentStep >= 5 ? 'text-blue-600' : 'text-gray-400'">
              Horaire
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Intervenant Info Bar -->
    <div class="bg-green-50 border-b border-green-100">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center gap-4">
          <img
            v-if="intervenant?.image"
            :src="intervenant.image"
            :alt="intervenant.name"
            class="w-12 h-12 rounded-full object-cover border-2"
            style="border-color: #92b08b"
          />
          <div class="flex-1">
            <h3 class="font-bold text-lg" style="color: #2f4f4f">{{ intervenant?.name || 'Intervenant' }}</h3>
            <div class="flex items-center gap-4 mt-1">
              <div class="flex items-center gap-1">
                <Star :size="16" fill="#FEE347" color="#FEE347" />
                <span class="font-semibold">{{ intervenant?.averageRating || 'N/A' }}</span>
              </div>
              <span class="px-3 py-1 rounded-full text-sm text-white" style="background-color: #1a5fa3">
                {{ intervenant?.hourlyRate ? `${intervenant.hourlyRate} DH/h` : 'N/A' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Step Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Step 1: Choose Service -->
      <div v-if="currentStep === 1" class="space-y-6">
        <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">Étape 1 : Choisissez le service</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="service in services"
            :key="service.id"
            @click="selectService(service)"
            class="p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
            :class="selectedService?.id === service.id ? 'border-green-500 bg-green-50' : 'border-gray-200'"
          >
            <h4 class="font-bold text-lg mb-2" :class="selectedService?.id === service.id ? 'text-green-600' : 'text-gray-700'">
              {{ service.nom_service }}
            </h4>
            <p class="text-sm text-gray-600">
              {{ service.taches_count || 0 }} tâches disponibles
            </p>
          </div>
        </div>
      </div>

      <!-- Step 2: Choose Task -->
      <div v-if="currentStep === 2" class="space-y-6">
        <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">Étape 2 : Choisissez la tâche</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button
            v-for="task in tasks"
            :key="task.id"
            @click="selectTask(task)"
            class="p-4 border-2 rounded-lg transition-all hover:shadow-md text-left"
            :class="selectedTask?.id === task.id ? 'border-green-500 bg-green-50' : 'border-gray-200'"
          >
            <span class="font-medium" :class="selectedTask?.id === task.id ? 'text-green-600' : 'text-gray-700'">
              {{ task.nom_tache }}
            </span>
          </button>
        </div>
      </div>

      <!-- Step 3: Details (same as BookingModal) -->
      <div v-if="currentStep === 3" class="space-y-6">
        <!-- Address & City -->
        <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
            <MapPin :size="20" style="color: #92b08b" />
            Adresse & Ville du service
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block mb-2 font-medium">Adresse</label>
              <input
                v-model="bookingData.address"
                type="text"
                placeholder="Numéro, rue, immeuble..."
                class="w-full px-4 py-2 border-2 rounded-lg focus:ring-2 focus:ring-green-500"
                style="border-color: #92b08b"
              />
            </div>
            <div>
              <label class="block mb-2 font-medium">Ville</label>
              <input
                v-model="bookingData.ville"
                type="text"
                placeholder="Ex: Tétouan, Martil..."
                class="w-full px-4 py-2 border-2 rounded-lg focus:ring-2 focus:ring-green-500"
                style="border-color: #92b08b"
              />
            </div>
          </div>
        </div>

        <!-- Specific Information & Constraints -->
        <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <h4 class="text-lg font-bold mb-4" style="color: #2f4f4f">Informations spécifiques</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block mb-2 font-medium">Contraintes de la tâche</label>
              <div class="space-y-3">
                <div v-for="c in constraints" :key="c.id" class="p-3 border-2 border-gray-200 rounded-lg">
                  <div class="flex items-center justify-between mb-2">
                    <span class="font-medium">{{ c.nom }} ({{ c.unite }})</span>
                    <span class="text-xs text-gray-600">Seuil: {{ c.seuil }} {{ c.unite }} ≈ 1h</span>
                  </div>
                  <input
                    v-model.number="constraintsValues[c.id]"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    @input="updateDurationEstimation"
                  />
                </div>
              </div>
            </div>
            
            <!-- Durée estimée -->
            <div class="bg-blue-50 rounded-lg p-4">
              <h5 class="font-bold mb-3 flex items-center gap-2">
                <Clock :size="18" class="text-blue-600" />
                Durée estimée
              </h5>
              <div class="text-center mb-2">
                <span class="text-3xl font-bold text-blue-700">{{ estimatedHours }}</span>
                <span class="text-lg text-gray-600 ml-1">heures</span>
              </div>
              <p class="text-sm text-gray-600 text-center">
                Cette durée déterminera le nombre de créneaux horaires nécessaires
              </p>
              <div v-if="estimatedHours > 8" class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 rounded p-3">
                <div class="flex items-start gap-2">
                  <AlertCircle :size="16" class="text-yellow-600 mt-0.5" />
                  <p class="text-sm">
                    ⚠️ Durée importante. Vérifiez que l'intervenant a assez de disponibilités.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Materials -->
        <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
            <Package :size="20" style="color: #7c3aed" />
            Matériel disponible
          </h4>
          <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-2">
              <Info :size="20" class="text-blue-600 mt-0.5" />
              <div class="text-sm">
                <p class="font-medium mb-1">Cochez le matériel que vous possédez déjà</p>
                <p>Le matériel non coché sera fourni par l'intervenant avec un coût supplémentaire</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <label
              v-for="material in materials"
              :key="material.id"
              class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50"
            >
              <input
                v-model="bookingData.materials"
                type="checkbox"
                :value="material.id"
                class="w-5 h-5"
              />
              <span class="flex-1">{{ material.nom_materiel }}</span>
              <span v-if="material.cost" class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-700">
                +{{ material.cost }} DH
              </span>
            </label>
          </div>
          <div class="mt-4 flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span>Matériel que vous fournissez:</span>
            <span class="flex items-center gap-2">
              <Check :size="16" class="text-green-600" />
              <span class="font-semibold">{{ bookingData.materials.length }}/{{ materials.length }}</span>
            </span>
          </div>
          <div class="mt-2 flex items-center justify-between p-3 bg-orange-50 rounded-lg">
            <span>Coût matériel à fournir:</span>
            <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 font-semibold">
              +{{ materialsCost }} DH
            </span>
          </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
            <FileText :size="20" class="text-yellow-500" />
            Description détaillée *
          </h4>
          <textarea
            v-model="bookingData.description"
            rows="4"
            placeholder="Décrivez en détail votre besoin, l'état actuel, les spécificités à prendre en compte..."
            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
          ></textarea>
          <div class="mt-2 flex items-start gap-2 text-sm text-gray-600">
            <Info :size="16" class="mt-0.5" />
            <span>Plus vous donnez de détails, mieux l'intervenant pourra estimer le temps nécessaire.</span>
          </div>
        </div>

        <!-- Photos -->
        <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
            <Camera :size="20" class="text-blue-500" />
            Photos du site (optionnel)
          </h4>
          <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-2">
              <Info :size="20" class="text-green-600 mt-0.5" />
              <p class="text-sm">
                Ajoutez des photos pour aider l'intervenant à mieux comprendre votre besoin
                Ex: état actuel du jardin, zones à nettoyer, problèmes spécifiques...
              </p>
            </div>
          </div>
          <div
            @click="triggerFileInput"
            @drop.prevent="handleFileDrop"
            @dragover.prevent
            class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center cursor-pointer hover:border-green-500 transition-colors"
          >
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/*"
              @change="handleFileSelect"
              class="hidden"
            />
            <Upload :size="48" class="mx-auto mb-4 text-gray-400" />
            <p class="text-gray-600">
              Cliquez pour ajouter des photos PNG, JPG jusqu'à 10MB (max 5 photos)
            </p>
          </div>
          <div v-if="bookingData.photos.length > 0" class="mt-4 grid grid-cols-5 gap-2">
            <div
              v-for="(photo, index) in bookingData.photos"
              :key="index"
              class="relative"
            >
              <img :src="photo.preview" alt="Preview" class="w-full h-24 object-cover rounded-lg" />
              <button
                @click="removePhoto(index)"
                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1"
              >
                <X :size="12" />
              </button>
            </div>
          </div>
        </div>

        <!-- Cost Estimation -->
        <div class="bg-blue-50 rounded-lg p-6 border-2 border-blue-200">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
            <DollarSign :size="20" class="text-orange-500" />
            Estimation du coût
          </h4>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span>Tarif horaire:</span>
              <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">
                {{ intervenant?.hourlyRate || 'N/A' }} DH/h
              </span>
            </div>
            <div class="flex justify-between items-center">
              <span>Durée estimée:</span>
              <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">{{ estimatedHours }} h</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Coût estimé:</span>
              <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">{{ finalCost }} DH</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-orange-600">Coût matériel:</span>
              <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 font-semibold">
                +{{ materialsCost }} DH
              </span>
            </div>
          </div>
          <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4">
            <div class="flex items-start gap-2">
              <AlertCircle :size="20" class="text-yellow-600 mt-0.5" />
              <p class="text-sm text-gray-700">
                La durée estimée et le coût final de la main d'œuvre seront confirmés par l'intervenant après analyse de votre demande.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 4: Date Selection (same as BookingModal) -->
      <div v-if="currentStep === 4" class="space-y-6">
        <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">
          Sélectionnez une date pour votre intervention
        </h3>
        
        <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <div class="flex items-center gap-2 mb-4">
            <Calendar :size="20" style="color: #1a5fa3" />
            <span class="font-medium">Date souhaitée</span>
          </div>
          <input
            v-model="bookingData.date"
            type="date"
            :min="minDate"
            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @change="checkDayAvailability"
          />
        </div>

        <!-- Validation de la journée -->
        <div v-if="bookingData.date" class="bg-white rounded-lg p-6 border-2 border-gray-200">
          <div class="flex items-center gap-3 mb-4">
            <Clock :size="20" class="text-blue-600" />
            <h4 class="text-lg font-bold" style="color: #2f4f4f">Vérification de disponibilité</h4>
          </div>
          
          <div v-if="dayCheckLoading" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Vérification des disponibilités...</p>
          </div>
          
          <div v-else-if="dayAvailabilityResult">
            <div class="space-y-4">
              <div :class="dayAvailabilityResult.hasEnoughTime ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'"
                   class="rounded-lg p-4">
                <div class="flex items-start gap-3">
                  <Check v-if="dayAvailabilityResult.hasEnoughTime" :size="20" class="text-green-600 mt-0.5" />
                  <X v-else :size="20" class="text-red-600 mt-0.5" />
                  <div>
                    <p class="font-medium" :class="dayAvailabilityResult.hasEnoughTime ? 'text-green-700' : 'text-red-700'">
                      {{ dayAvailabilityResult.message }}
                    </p>
                    <p v-if="dayAvailabilityResult.details" class="text-sm text-gray-600 mt-1">
                      {{ dayAvailabilityResult.details }}
                    </p>
                  </div>
                </div>
              </div>
              
              <div v-if="dayAvailabilityResult.availableSlots && dayAvailabilityResult.availableSlots.length > 0" 
                   class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm font-medium text-blue-800 mb-2">
                  Créneaux de départ possibles pour {{ estimatedHours }} heure(s) :
                </p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="slot in dayAvailabilityResult.availableSlots" 
                        :key="slot"
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    {{ slot }}
                  </span>
                </div>
              </div>
              
              <div v-if="!dayAvailabilityResult.hasEnoughTime" class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4">
                <div class="flex items-start gap-2">
                  <AlertCircle :size="20" class="text-yellow-600 mt-0.5" />
                  <div>
                    <p class="text-sm font-medium text-yellow-800">
                      Recommandation :
                    </p>
                    <p class="text-sm text-gray-700 mt-1">
                      Choisissez une autre date ou réduisez la durée estimée en ajustant les contraintes.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else-if="bookingData.date && !dayCheckLoading" class="text-center py-4 text-gray-500">
            <p>La date est sélectionnée. Les disponibilités seront vérifiées automatiquement.</p>
          </div>
        </div>
      </div>

      <!-- Step 5: Time Slot Selection (same as BookingModal) -->
      <div v-if="currentStep === 5" class="space-y-6">
        <h3 class="text-xl font-bold mb-4" style="color: #2f4f4f">
          Choisissez l'horaire pour le {{ formatDate(bookingData.date) }}
        </h3>
        
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
          <div class="flex items-center gap-3">
            <Info :size="20" class="text-blue-600" />
            <div>
              <p class="font-medium text-blue-800">
                Durée nécessaire : <span class="font-bold">{{ estimatedHours }} heure(s)</span>
              </p>
              <p class="text-sm text-blue-700 mt-1">
                Vous devez sélectionner un créneau de départ. Les {{ estimatedHours - 1 }} créneau(x) suivant(s) seront automatiquement réservés.
              </p>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
          <button
            v-for="slot in timeSlots"
            :key="slot.time"
            @click="selectTimeSlot(slot)"
            :disabled="!slot.available || !canSelectSlot(slot)"
            class="p-4 border-2 rounded-lg transition-all text-center relative group"
            :class="getSlotClass(slot)"
          >
            <span class="font-semibold">{{ slot.time }}</span>
            
            <X v-if="!slot.available" :size="16" class="absolute top-2 right-2 text-red-500" />
            <Check v-if="isSlotSelected(slot)" :size="16" class="absolute top-2 right-2 text-blue-500" />
            
            <div v-if="slot.available && !canSelectSlot(slot)" 
                 class="absolute inset-0 bg-red-100 bg-opacity-50 rounded-lg flex items-center justify-center">
              <X :size="20" class="text-red-600" />
            </div>
            
            <span v-if="!slot.available" class="block text-xs text-red-600 mt-1">Indisponible</span>
            <span v-else-if="!canSelectSlot(slot)" class="block text-xs text-red-600 mt-1">Pas assez de temps</span>
          </button>
        </div>
        
        <!-- Affichage des créneaux sélectionnés -->
        <div v-if="selectedTimeSlots.length > 0" class="bg-green-50 border-2 border-green-300 rounded-lg p-4">
          <div class="flex items-center gap-2 mb-2">
            <Check :size="20" class="text-green-600" />
            <span class="font-semibold">Créneaux sélectionnés :</span>
          </div>
          <div class="flex flex-wrap gap-2 mt-2">
            <span v-for="(slot, index) in selectedTimeSlots" 
                  :key="slot.time"
                  class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm flex items-center gap-1">
              {{ slot.time }}
              <span v-if="index === 0" class="text-xs bg-green-200 px-1 rounded">Début</span>
            </span>
          </div>
          <p class="text-sm text-gray-600 mt-2">
            Heure de début : <span class="font-semibold">{{ selectedTimeSlots[0]?.time }}</span> 
            • Durée totale : <span class="font-semibold">{{ estimatedHours }} heure(s)</span>
          </p>
        </div>
        
        <!-- Avertissement si pas assez de créneaux -->
        <div v-if="hasEnoughSlots === false && bookingData.date" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
          <div class="flex items-start gap-2">
            <AlertCircle :size="20" class="text-red-600 mt-0.5" />
            <div>
              <p class="font-medium text-red-700">
                Pas assez de créneaux disponibles
              </p>
              <p class="text-sm text-gray-700 mt-1">
                Cette journée n'a pas assez de créneaux consécutifs pour une durée de {{ estimatedHours }} heure(s).
                Veuillez choisir une autre date ou ajuster la durée.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Buttons -->
    <div class="bg-white border-t border-gray-200 sticky bottom-0 z-50 shadow-lg">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between">
          <button
            v-if="currentStep > 1"
            @click="previousStep"
            class="px-6 py-3 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition-all flex items-center gap-2"
          >
            <ArrowLeft :size="20" />
            Retour
          </button>
          <div v-else></div>
          <button
            @click="nextStep"
            :disabled="!canProceed || (currentStep === 4 && !dayAvailabilityResult?.hasEnoughTime) || loading"
            class="px-6 py-3 rounded-lg text-white font-semibold transition-all flex items-center gap-2"
            :class="canProceed && !(currentStep === 4 && !dayAvailabilityResult?.hasEnoughTime) && !loading ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed'"
          >
            <span v-if="loading">Envoi en cours...</span>
            <span v-else>{{ currentStep === 5 ? 'Envoyer la demande' : 'Continuer →' }}</span>
            <ArrowRight v-if="currentStep < 5 && !loading" :size="20" />
            <Check v-else-if="currentStep === 5 && !loading" :size="20" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  X, Calendar, Clock, FileText, Check, Star, MapPin, AlertCircle,
  Package, Info, Camera, Upload, DollarSign, ArrowLeft, ArrowRight
} from 'lucide-vue-next';
import bookingService from '@/services/bookingService';
import authService from '@/services/authService';
import api from '@/services/api';

export default {
  name: 'BookingPage',
  components: {
    X, Calendar, Clock, FileText, Check, Star, MapPin, AlertCircle,
    Package, Info, Camera, Upload, DollarSign, ArrowLeft, ArrowRight
  },
  emits: ['back', 'success', 'login-required'],
  data() {
    return {
      currentStep: 1,
      services: [],
      tasks: [],
      selectedService: null,
      selectedTask: null,
      intervenant: null,
      clientId: null,
      bookingData: {
        date: '',
        time: '',
        address: '',
        ville: '',
        materials: [],
        description: '',
        photos: []
      },
      timeSlots: [],
      availableSlots: [],
      selectedTimeSlots: [],
      materials: [],
      constraints: [],
      constraintsValues: {},
      informations: [],
      informationsValues: {},
      dayAvailabilityResult: null,
      dayCheckLoading: false,
      loading: false
    };
  },
  computed: {
    minDate() {
      const today = new Date();
      return today.toISOString().split('T')[0];
    },
    canProceed() {
      switch (this.currentStep) {
        case 1:
          return this.selectedService !== null;
        case 2:
          return this.selectedTask !== null;
        case 3:
          return this.bookingData.address.trim() !== '' &&
                 this.bookingData.ville.trim() !== '' &&
                 this.bookingData.description.trim() !== '' &&
                 this.estimatedHours > 0;
        case 4:
          return this.bookingData.date !== '' && 
                 this.dayAvailabilityResult?.hasEnoughTime === true;
        case 5:
          return this.selectedTimeSlots.length === this.estimatedHours;
        default:
          return false;
      }
    },
    materialsCost() {
      const selectedMaterialIds = this.bookingData.materials;
      return this.materials
        .filter(m => !selectedMaterialIds.includes(m.id))
        .reduce((sum, m) => sum + (m.cost ?? m.pivot?.prix_materiel ?? 0), 0);
    },
    estimatedHours() {
      let hours = 0;
      this.constraints.forEach(c => {
        const v = Number(this.constraintsValues[c.id] || 0);
        const seuil = Number(c.seuil || 0);
        if (seuil > 0 && v > 0) {
          hours += Math.ceil(v / seuil);
        }
      });
      return Math.max(hours, 1);
    },
    finalCost() {
      const rate = Number(this.intervenant?.hourlyRate || 0);
      return Math.round(rate * this.estimatedHours + this.materialsCost);
    },
    hasEnoughSlots() {
      if (!this.bookingData.date || this.estimatedHours <= 0) return null;
      
      const availableStartSlots = this.timeSlots.filter(slot => 
        slot.available && this.canSelectSlot(slot)
      );
      
      return availableStartSlots.length > 0;
    }
  },
  async mounted() {
    // Récupérer les paramètres depuis l'URL ou localStorage
    const params = new URLSearchParams(window.location.search);
    const intervenantId = params.get('intervenantId') || localStorage.getItem('booking_intervenantId');
    const preselectedServiceId = params.get('serviceId') || localStorage.getItem('booking_serviceId');
    const preselectedTaskId = params.get('taskId') || localStorage.getItem('booking_taskId');
    
    // Nettoyer localStorage après récupération
    if (localStorage.getItem('booking_intervenantId')) {
      localStorage.removeItem('booking_intervenantId');
      localStorage.removeItem('booking_serviceId');
      localStorage.removeItem('booking_taskId');
    }
    
    if (!intervenantId) {
      alert('Aucun intervenant sélectionné');
      this.$emit('back');
      return;
    }
    
    // Vérifier l'authentification
    if (!authService.isAuthenticated()) {
      // Stocker les paramètres pour après connexion
      localStorage.setItem('booking_intervenantId', intervenantId);
      if (preselectedServiceId) localStorage.setItem('booking_serviceId', preselectedServiceId);
      if (preselectedTaskId) localStorage.setItem('booking_taskId', preselectedTaskId);
      localStorage.setItem('redirect_after_login', 'booking');
      
      // Rediriger vers la connexion
      this.$emit('login-required');
      return;
    }
    
    // Récupérer les données utilisateur
    const user = authService.getUserSync();
    if (!user || !user.client) {
      alert('Vous devez être un client pour effectuer une réservation');
      this.$emit('back');
      return;
    }
    
    this.clientId = user.client.id || user.client_id || user.id;
    
    // Charger les données de l'intervenant
    await this.loadIntervenantData(intervenantId);
    await this.loadServices();
    
    // Initialiser l'adresse et la ville depuis le profil client
    if (user.address) this.bookingData.address = user.address;
    const city = user.ville || (user.client ? user.client.ville : null);
    if (city) this.bookingData.ville = city;
    
    // Handle preselection
    if (preselectedServiceId) {
      const matchingService = this.services.find(s => s.id == preselectedServiceId);
      if (matchingService) {
        await this.selectService(matchingService);
        
        if (preselectedTaskId) {
          const matchingTask = this.tasks.find(t => t.id == preselectedTaskId);
          if (matchingTask) {
            this.selectTask(matchingTask);
          }
        }
      }
    }
  },
  watch: {
    'bookingData.date'(newDate) {
      if (newDate && this.currentStep === 4) {
        this.checkDayAvailability();
      }
    },
    estimatedHours(newHours) {
      if (this.currentStep >= 4) {
        if (this.bookingData.date) {
          this.checkDayAvailability();
        }
      }
    }
  },
  methods: {
    async loadIntervenantData(intervenantId) {
      try {
        const response = await api.get(`intervenants/${intervenantId}`);
        const intervenantData = response.data?.data || response.data;
        this.intervenant = {
          id: intervenantData.id,
          name: `${intervenantData.utilisateur?.prenom || ''} ${intervenantData.utilisateur?.nom || ''}`.trim() || 'Intervenant',
          image: intervenantData.image_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(intervenantData.utilisateur?.prenom + ' ' + intervenantData.utilisateur?.nom)}&background=random&color=fff`,
          averageRating: intervenantData.average_rating || 0,
          hourlyRate: intervenantData.hourlyRate || 0
        };
      } catch (error) {
        console.error('Error loading intervenant:', error);
        alert('Erreur lors du chargement des données de l\'intervenant');
        this.$emit('back');
      }
    },
    async loadServices() {
      try {
        const response = await bookingService.getIntervenantServices(this.intervenant.id);
        this.services = response.data?.data ?? response.data ?? [];
        if (!Array.isArray(this.services)) this.services = [];
      } catch (error) {
        console.error('Error loading services:', error);
        this.services = [];
      }
    },
    async selectService(service) {
      this.selectedService = service;
      try {
        const response = await bookingService.getServiceTaches(service.id, this.intervenant.id);
        this.tasks = response.data?.data ?? response.data ?? [];
        if (!Array.isArray(this.tasks)) this.tasks = [];
        this.currentStep = 2;
      } catch (error) {
        console.error('Error loading tasks:', error);
      }
    },
    selectTask(task) {
      this.selectedTask = task;
      this.loadConstraints();
      this.loadMaterials();
      this.currentStep = 3;
    },
    async checkDayAvailability() {
      if (!this.bookingData.date || this.estimatedHours <= 0) {
        this.dayAvailabilityResult = null;
        return;
      }
      
      this.dayCheckLoading = true;
      
      try {
        const response = await bookingService.getIntervenantDisponibilites(
          this.intervenant.id,
          this.bookingData.date
        );
        
        const disponibilites = response.data?.data || response.data || [];
        
        const allSlots = this.generateTimeSlots();
        const markedSlots = allSlots.map(slot => ({
          ...slot,
          available: this.checkSlotAvailability(slot.time, disponibilites)
        }));
        
        this.timeSlots = markedSlots;
        const possibleStartSlots = this.findPossibleStartSlots(markedSlots);
        const hasEnoughTime = possibleStartSlots.length > 0;
        
        this.dayAvailabilityResult = {
          hasEnoughTime,
          message: hasEnoughTime 
            ? `✅ Disponibilité confirmée pour ${this.estimatedHours} heure(s) le ${this.formatDate(this.bookingData.date)}`
            : `❌ Pas assez de créneaux disponibles pour ${this.estimatedHours} heure(s) le ${this.formatDate(this.bookingData.date)}`,
          details: hasEnoughTime
            ? `${possibleStartSlots.length} créneau(x) de départ possible(s)`
            : `Essayez une autre date ou réduisez la durée estimée`,
          availableSlots: hasEnoughTime ? possibleStartSlots.map(s => s.time) : []
        };
        
      } catch (error) {
        console.error('Error checking day availability:', error);
        this.dayAvailabilityResult = {
          hasEnoughTime: false,
          message: 'Erreur lors de la vérification des disponibilités',
          details: 'Veuillez réessayer ou contacter le support'
        };
      } finally {
        this.dayCheckLoading = false;
      }
    },
    generateTimeSlots() {
      const slots = [];
      for (let hour = 8; hour <= 18; hour++) {
        slots.push({
          time: `${hour.toString().padStart(2, '0')}:00`,
          available: false,
          blockedReason: null
        });
      }
      return slots;
    },
    checkSlotAvailability(time, disponibilites) {
      if (!disponibilites || disponibilites.length === 0) return true;
      
      const dateStr = this.bookingData.date;
      if (!dateStr) return false;
      
      const weekday = new Date(dateStr).toLocaleDateString('fr-FR', { weekday: 'long' }).toLowerCase();
      
      return disponibilites.some(d => {
        const start = d.heure_debut || d.heureDebut;
        const end = d.heure_fin || d.heureFin;
        
        if (!start || !end) return false;
        
        const timeValue = this.timeToMinutes(time);
        const startValue = this.timeToMinutes(start);
        const endValue = this.timeToMinutes(end);
        
        const within = timeValue >= startValue && timeValue < endValue;
        
        if (String(d.type).toLowerCase() === 'reguliere' && d.jours_semaine) {
          return within && String(d.jours_semaine).toLowerCase() === weekday;
        }
        
        if (String(d.type).toLowerCase() === 'ponctuelle' && d.date_specific) {
          const disponibiliteDate = new Date(d.date_specific).toISOString().split('T')[0];
          return within && disponibiliteDate === dateStr;
        }
        
        return false;
      });
    },
    timeToMinutes(timeStr) {
      const [hours, minutes] = timeStr.split(':').map(Number);
      return hours * 60 + (minutes || 0);
    },
    findPossibleStartSlots(slots) {
      const possibleStarts = [];
      
      for (let i = 0; i <= slots.length - this.estimatedHours; i++) {
        let allAvailable = true;
        
        for (let j = 0; j < this.estimatedHours; j++) {
          if (!slots[i + j] || !slots[i + j].available) {
            allAvailable = false;
            break;
          }
        }
        
        if (allAvailable) {
          possibleStarts.push(slots[i]);
        }
      }
      
      return possibleStarts;
    },
    canSelectSlot(slot) {
      if (!slot.available) return false;
      
      const slotIndex = this.timeSlots.findIndex(s => s.time === slot.time);
      if (slotIndex === -1) return false;
      
      for (let i = 1; i < this.estimatedHours; i++) {
        const nextSlot = this.timeSlots[slotIndex + i];
        if (!nextSlot || !nextSlot.available) {
          return false;
        }
      }
      
      return true;
    },
    getSlotClass(slot) {
      if (!slot.available) {
        return 'border-red-200 bg-red-50 opacity-50 cursor-not-allowed';
      }
      
      if (this.isSlotSelected(slot)) {
        return 'border-blue-500 bg-blue-50';
      }
      
      if (this.canSelectSlot(slot)) {
        return 'border-gray-200 hover:border-green-500 hover:bg-green-50 cursor-pointer';
      }
      
      return 'border-gray-200 bg-gray-100 opacity-50 cursor-not-allowed';
    },
    isSlotSelected(slot) {
      return this.selectedTimeSlots.some(s => s.time === slot.time);
    },
    selectTimeSlot(slot) {
      if (!this.canSelectSlot(slot)) return;
      
      const slotIndex = this.timeSlots.findIndex(s => s.time === slot.time);
      if (slotIndex === -1) return;
      
      this.selectedTimeSlots = [];
      for (let i = 0; i < this.estimatedHours; i++) {
        const timeSlot = this.timeSlots[slotIndex + i];
        if (timeSlot) {
          this.selectedTimeSlots.push(timeSlot);
        }
      }
      
      this.bookingData.time = slot.time;
    },
    async loadConstraints() {
      try {
        const response = await api.get(`taches/${this.selectedTask?.id}/contraintes`);
        this.constraints = response.data?.data ?? response.data ?? [];
        
        if (!Array.isArray(this.constraints)) {
          this.constraints = [];
        }
        
        this.constraints.forEach(c => {
          this.constraintsValues[c.id] = 0;
        });
      } catch (error) {
        console.error('Error loading constraints:', error);
        this.constraints = [];
      }
    },
    async loadMaterials() {
      try {
        const response = await api.get(`taches/${this.selectedTask?.id}/materiels`);
        this.materials = response.data?.data ?? response.data ?? [];
        if (!Array.isArray(this.materials)) this.materials = [];
      } catch (error) {
        this.materials = [];
      }
    },
    updateDurationEstimation() {
      this.$forceUpdate();
      
      if (this.currentStep >= 4 && this.bookingData.date) {
        this.checkDayAvailability();
      }
    },
    triggerFileInput() {
      this.$refs.fileInput?.click();
    },
    handleFileSelect(event) {
      const files = Array.from(event.target.files).slice(0, 5);
      files.forEach(file => {
        if (file.size <= 10 * 1024 * 1024) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.bookingData.photos.push({
              file,
              preview: e.target.result
            });
          };
          reader.readAsDataURL(file);
        }
      });
    },
    handleFileDrop(event) {
      const files = Array.from(event.dataTransfer.files).slice(0, 5);
      files.forEach(file => {
        if (file.type.startsWith('image/') && file.size <= 10 * 1024 * 1024) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.bookingData.photos.push({
              file,
              preview: e.target.result
            });
          };
          reader.readAsDataURL(file);
        }
      });
    },
    removePhoto(index) {
      this.bookingData.photos.splice(index, 1);
    },
    nextStep() {
      if (!this.canProceed) return;
      
      if (this.currentStep === 2 && this.selectedTask) {
        this.loadMaterials();
      }
      
      if (this.currentStep === 3 && this.estimatedHours > 0) {
        this.bookingData.date = '';
        this.dayAvailabilityResult = null;
      }
      
      if (this.currentStep === 4 && this.bookingData.date && this.dayAvailabilityResult?.hasEnoughTime) {
        this.selectedTimeSlots = [];
      }
      
      if (this.currentStep === 5) {
        this.submitBooking();
        return;
      }
      
      this.currentStep++;
    },
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
        
        if (this.currentStep === 4 && this.bookingData.date) {
          this.checkDayAvailability();
        }
      }
    },
    async submitBooking() {
      this.loading = true;
      
      try {
        const formData = new FormData();
        
        formData.append('client_id', this.clientId);
        formData.append('intervenant_id', this.intervenant.id);
        formData.append('tache_id', this.selectedTask.id);
        
        const startTime = this.selectedTimeSlots[0]?.time || '08:00';
        const dateTime = `${this.bookingData.date} ${startTime}:00`;
        formData.append('date_intervention', dateTime);
        
        formData.append('duration_hours', this.estimatedHours.toFixed(2));
        formData.append('address', this.bookingData.address);
        formData.append('ville', this.bookingData.ville);
        formData.append('materials_cost', this.materialsCost);
        
        const providedMaterials = this.materials
          .filter(m => !this.bookingData.materials.includes(m.id))
          .map(m => m.id);
        formData.append('provided_materials', JSON.stringify(providedMaterials));
        formData.append('status', 'en_attente');
        formData.append('description', this.bookingData.description || '');
        formData.append('constraints', JSON.stringify(this.constraintsValues));
        
        this.bookingData.photos.forEach((photo) => {
          formData.append('photos[]', photo.file);
        });

        const response = await bookingService.createIntervention(formData);
        
        alert('Demande de service envoyée avec succès !');
        // Émettre seulement success, App.vue gérera la redirection
        this.$emit('success');
        
      } catch (error) {
        console.error('Error submitting booking:', error);
        
        let errorMessage = 'Erreur lors de l\'envoi de la demande. Veuillez réessayer.';
        
        const errorData = error.data || error.response?.data;
        const errors = error.errors || errorData?.errors;
        const message = error.message || errorData?.message;

        if (errors) {
          const errorList = Object.values(errors).flat();
          errorMessage = errorList.join('\n');
        } else if (message) {
          errorMessage = message;
        } else if (error.error) {
          errorMessage = error.error;
        }
        
        alert(errorMessage);
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    },
    handleBack() {
      this.$emit('back');
    }
  }
};
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>

