<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="bg-blue-600 text-white p-6 rounded-t-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
          <img
            :src="intervenant.image"
            :alt="intervenant.name"
            class="w-16 h-16 rounded-full object-cover border-2 border-white"
          />
          <div>
            <h2 class="text-2xl font-bold">Choisir une tâche</h2>
            <p class="text-blue-100">pour {{ intervenant.name }}</p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="text-white hover:text-gray-200 transition-colors"
        >
          <X :size="24" />
        </button>
      </div>

      <!-- Progress Steps -->
      <div class="p-6 border-b">
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

      <!-- Intervenant Info Bar -->
      <div class="bg-green-50 p-4 flex items-center gap-4 border-b">
        <img
          :src="intervenant.image"
          :alt="intervenant.name"
          class="w-12 h-12 rounded-full object-cover border-2"
          style="border-color: #92b08b"
        />
        <div class="flex-1">
          <h3 class="font-bold text-lg" style="color: #2f4f4f">{{ intervenant.name }}</h3>
          <div class="flex items-center gap-4 mt-1">
            <div class="flex items-center gap-1">
              <Star :size="16" fill="#FEE347" color="#FEE347" />
              <span class="font-semibold">{{ intervenant.averageRating || 'N/A' }}</span>
            </div>
            <span class="px-3 py-1 rounded-full text-sm text-white" style="background-color: #1a5fa3">
              {{ intervenant.hourlyRate ? `${intervenant.hourlyRate} DH/h` : 'N/A' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Step Content -->
      <div class="p-6">
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

        <!-- Step 3: Details -->
        <div v-if="currentStep === 3" class="space-y-6">
          <!-- Urgency Level -->
          <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
            <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <AlertCircle :size="20" class="text-orange-500" />
              Niveau d'urgence
            </h4>
            <div class="flex gap-4">
              <button
                v-for="urgency in urgencyLevels"
                :key="urgency.value"
                @click="bookingData.urgency = urgency.value"
                class="flex-1 p-4 border-2 rounded-lg transition-all"
                :class="
                  bookingData.urgency === urgency.value
                    ? urgency.class
                    : 'border-gray-200 bg-white'
                "
              >
                <component :is="urgency.icon" :size="20" class="mb-2" :class="urgency.iconClass" />
                <span class="font-medium">{{ urgency.label }}</span>
              </button>
            </div>
          </div>

          <!-- Address -->
          <div class="bg-white rounded-lg p-6 border-2 border-gray-200">
            <h4 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #2f4f4f">
              <MapPin :size="20" style="color: #92b08b" />
              Adresse du service
            </h4>
            <div class="space-y-4">
              <div>
                <input
                  v-model="bookingData.address"
                  type="text"
                  placeholder="Numéro, rue, immeuble..."
                  class="w-full px-4 py-2 border-2 rounded-lg focus:ring-2 focus:ring-green-500"
                  style="border-color: #92b08b"
                />
              </div>
              <div>
                <label class="block mb-2 font-medium">Quartier</label>
                <select
                  v-model="bookingData.quartier"
                  class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                >
                  <option value="">Sélectionnez un quartier</option>
                  <option value="Tetouan Centre">Tetouan Centre</option>
                  <option value="Martil">Martil</option>
                  <option value="Fnideq">Fnideq</option>
                </select>
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
                  {{ intervenant.hourlyRate || 'N/A' }} DH/h
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

        <!-- Step 4: Date Selection -->
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

        <!-- Step 5: Time Slot Selection -->
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
              
              <!-- Indicateurs visuels -->
              <X v-if="!slot.available" :size="16" class="absolute top-2 right-2 text-red-500" />
              <Check v-if="isSlotSelected(slot)" :size="16" class="absolute top-2 right-2 text-blue-500" />
              
              <!-- Indicateur de blocage pour durée -->
              <div v-if="slot.available && !canSelectSlot(slot)" 
                   class="absolute inset-0 bg-red-100 bg-opacity-50 rounded-lg flex items-center justify-center">
                <X :size="20" class="text-red-600" />
              </div>
              
              <!-- Tooltip pour infos -->
              <div v-if="slot.available" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                <div class="bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
                  <div v-if="slot.blockedReason">{{ slot.blockedReason }}</div>
                  <div v-else>Cliquez pour sélectionner</div>
                </div>
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
      <div class="p-6 border-t bg-gray-50 flex justify-between">
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
          :disabled="!canProceed || (currentStep === 4 && !dayAvailabilityResult?.hasEnoughTime)"
          class="px-6 py-3 rounded-lg text-white font-semibold transition-all flex items-center gap-2"
          :class="canProceed && !(currentStep === 4 && !dayAvailabilityResult?.hasEnoughTime) ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed'"
        >
          {{ currentStep === 5 ? 'Envoyer la demande' : 'Continuer →' }}
          <ArrowRight v-if="currentStep < 5" :size="20" />
          <Check v-else :size="20" />
        </button>
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
import api from '@/services/api';

export default {
  name: 'BookingModal',
  components: {
    X, Calendar, Clock, FileText, Check, Star, MapPin, AlertCircle,
    Package, Info, Camera, Upload, DollarSign, ArrowLeft, ArrowRight
  },
  props: {
    intervenant: {
      type: Object,
      required: true
    },
    clientId: {
      type: Number,
      required: true
    },
    preselectedService: {
      type: Object,
      default: null
    },
    preselectedTask: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'success', 'signup-success'],
  data() {
    return {
      currentStep: 1,
      services: [],
      tasks: [],
      selectedService: null,
      selectedTask: null,
      bookingData: {
        date: '',
        time: '',
        urgency: 'normal',
        address: '',
        quartier: '',
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
      dayAvailabilityResult: null,
      dayCheckLoading: false,
      urgencyLevels: [
        {
          value: 'normal',
          label: 'Normal',
          icon: 'Clock',
          class: 'border-green-500 bg-green-50',
          iconClass: 'text-green-600'
        },
        {
          value: 'urgent',
          label: 'Urgent (24-48h)',
          icon: 'AlertCircle',
          class: 'border-orange-500 bg-orange-50',
          iconClass: 'text-orange-600'
        },
        {
          value: 'emergency',
          label: 'Urgence (Aujourd\'hui)',
          icon: 'AlertCircle',
          class: 'border-red-500 bg-red-50',
          iconClass: 'text-red-600'
        }
      ],
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
                 this.bookingData.quartier !== '' &&
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
      return Math.max(hours, 1); // Minimum 1 heure
    },
    finalCost() {
      const rate = Number(this.intervenant.hourlyRate || 0);
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
    await this.loadServices();
    
    // Handle preselection
    if (this.preselectedService) {
      // Find the service in our loaded list to ensure it's valid, or just use the prop
      const matchingService = this.services.find(s => s.id === this.preselectedService.id);
      if (matchingService) {
        await this.selectService(matchingService);
        
        if (this.preselectedTask) {
           // We need to find the task in the tasks list loaded by selectService
           const matchingTask = this.tasks.find(t => t.id === this.preselectedTask.id);
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
        // Revalider la disponibilité si la durée change
        if (this.bookingData.date) {
          this.checkDayAvailability();
        }
      }
    }
  },
  methods: {
    async loadServices() {
      try {
        const response = await bookingService.getIntervenantServices(this.intervenant.id);
        this.services = response.data.data || response.data || [];
        console.log('Loaded services:', this.services);
      } catch (error) {
        console.error('Error loading services:', error);
        this.services = [
          { id: 1, nom_service: 'Jardinage', taches_count: 3 },
          { id: 2, nom_service: 'Ménage', taches_count: 2 }
        ];
      }
    },
    async selectService(service) {
      this.selectedService = service;
      try {
        const response = await bookingService.getServiceTaches(service.id, this.intervenant.id);
        this.tasks = response.data.data || response.data || [];
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
    
    // Vérification intelligente de la journée
    async checkDayAvailability() {
      if (!this.bookingData.date || this.estimatedHours <= 0) {
        this.dayAvailabilityResult = null;
        return;
      }
      
      this.dayCheckLoading = true;
      
      try {
        // 1. Charger les disponibilités de l'intervenant
        const response = await bookingService.getIntervenantDisponibilites(
          this.intervenant.id,
          this.bookingData.date
        );
        
        // Extract the disponibilites array from the nested response
        const disponibilites = response.data?.data || response.data || [];
        console.log('📅 Disponibilites received:', disponibilites);
        
        // 2. Générer tous les créneaux de la journée
        const allSlots = this.generateTimeSlots();
        
        // 3. Marquer les créneaux disponibles
        const markedSlots = allSlots.map(slot => ({
          ...slot,
          available: this.checkSlotAvailability(slot.time, disponibilites)
        }));
        
        this.timeSlots = markedSlots;
        
        // 4. Trouver les créneaux de départ possibles
        const possibleStartSlots = this.findPossibleStartSlots(markedSlots);
        
        // 5. Préparer le résultat
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
        
        console.log('Day availability check:', this.dayAvailabilityResult);
        
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
    
    // Générer tous les créneaux horaires de la journée
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
    
    // Vérifier la disponibilité d'un créneau
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
    
    // Convertir l'heure en minutes pour comparaison
    timeToMinutes(timeStr) {
      const [hours, minutes] = timeStr.split(':').map(Number);
      return hours * 60 + (minutes || 0);
    },
    
    // Trouver les créneaux de départ possibles
    findPossibleStartSlots(slots) {
      const possibleStarts = [];
      
      for (let i = 0; i <= slots.length - this.estimatedHours; i++) {
        let allAvailable = true;
        
        // Vérifier les X créneaux consécutifs
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
    
    // Vérifier si un créneau peut être sélectionné comme point de départ
    canSelectSlot(slot) {
      if (!slot.available) return false;
      
      const slotIndex = this.timeSlots.findIndex(s => s.time === slot.time);
      if (slotIndex === -1) return false;
      
      // Vérifier si tous les créneaux suivants sont disponibles
      for (let i = 1; i < this.estimatedHours; i++) {
        const nextSlot = this.timeSlots[slotIndex + i];
        if (!nextSlot || !nextSlot.available) {
          return false;
        }
      }
      
      return true;
    },
    
    // Obtenir la classe CSS pour un créneau
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
    
    // Vérifier si un créneau est sélectionné
    isSlotSelected(slot) {
      return this.selectedTimeSlots.some(s => s.time === slot.time);
    },
    
    // Sélectionner un créneau de départ
    selectTimeSlot(slot) {
      if (!this.canSelectSlot(slot)) return;
      
      const slotIndex = this.timeSlots.findIndex(s => s.time === slot.time);
      if (slotIndex === -1) return;
      
      // Sélectionner tous les créneaux nécessaires
      this.selectedTimeSlots = [];
      for (let i = 0; i < this.estimatedHours; i++) {
        const timeSlot = this.timeSlots[slotIndex + i];
        if (timeSlot) {
          this.selectedTimeSlots.push(timeSlot);
        }
      }
      
      // Stocker l'heure de début
      this.bookingData.time = slot.time;
      
      console.log('Selected time slots:', this.selectedTimeSlots);
    },
    
    async loadConstraints() {
      try {
        const response = await api.get(`taches/${this.selectedTask?.id}/contraintes`);
        console.log('📦 Raw constraints response:', response);
        
        this.constraints = response.data || [];
        console.log('✅ Constraints loaded:', this.constraints);
        
        // Initialiser les valeurs
        this.constraints.forEach(c => {
          this.constraintsValues[c.id] = 0;
        });
      } catch (error) {
        console.error('❌ Error loading constraints:', error);
        this.constraints = [];
      }
    },
    
    async loadMaterials() {
      try {
        const response = await api.get(`taches/${this.selectedTask?.id}/materiels`);
        this.materials = response.data.data || response.data || [];
      } catch (error) {
        this.materials = [
          { id: 1, nom_materiel: 'Taille-haie électrique ou thermique', cost: 0 },
          { id: 2, nom_materiel: 'Échelle', cost: 20 },
          { id: 3, nom_materiel: 'Bâche de protection', cost: 0 },
          { id: 4, nom_materiel: 'Gants de protection', cost: 5 },
          { id: 5, nom_materiel: 'Lunettes de protection', cost: 5 }
        ];
      }
    },
    
    // Mettre à jour l'estimation de durée
    updateDurationEstimation() {
      // Force le recalcul
      this.$forceUpdate();
      
      // Si on est déjà à l'étape Date, revalider
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
      
      // Charger les matériaux quand on passe aux détails
      if (this.currentStep === 2 && this.selectedTask) {
        this.loadMaterials();
      }
      
      // Vérifier la disponibilité quand on passe à la date
      if (this.currentStep === 3 && this.estimatedHours > 0) {
        // Réinitialiser la date si elle était déjà choisie
        this.bookingData.date = '';
        this.dayAvailabilityResult = null;
      }
      
      // Charger les créneaux quand on passe à l'horaire
      if (this.currentStep === 4 && this.bookingData.date && this.dayAvailabilityResult?.hasEnoughTime) {
        // Les créneaux sont déjà chargés par checkDayAvailability()
        this.selectedTimeSlots = []; // Réinitialiser la sélection
      }
      
      // Soumettre la demande
      if (this.currentStep === 5) {
        this.submitBooking();
        return;
      }
      
      this.currentStep++;
    },
    
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
        
        // Si on revient à l'étape Date, revalider si on a une date
        if (this.currentStep === 4 && this.bookingData.date) {
          this.checkDayAvailability();
        }
      }
    },
    
    async submitBooking() {
      this.loading = true;
      
      try {
        const formData = new FormData();
        
        // Données de base
        formData.append('client_id', this.clientId);
        formData.append('intervenant_id', this.intervenant.id);
        formData.append('tache_id', this.selectedTask.id);
        
        // Date et heure complètes
        const startTime = this.selectedTimeSlots[0]?.time || '08:00';
        const dateTime = `${this.bookingData.date} ${startTime}:00`;
        formData.append('date_intervention', dateTime);
        
        // Durée en heures (décimale)
        formData.append('duration_hours', this.estimatedHours.toFixed(2));
        
        // Autres informations
        formData.append('address', this.bookingData.address);
        formData.append('ville', this.bookingData.quartier);
        formData.append('status', 'en_attente');
        formData.append('description', this.bookingData.description || '');
        
        // Contraintes (optionnel, pourrait être stocké ailleurs)
        formData.append('constraints', JSON.stringify(this.constraintsValues));
        
        // Photos
        this.bookingData.photos.forEach((photo) => {
          formData.append('photos[]', photo.file);
        });

        console.log('Submitting booking with data:', {
          client_id: this.clientId,
          intervenant_id: this.intervenant.id,
          tache_id: this.selectedTask.id,
          date_intervention: dateTime,
          duration_hours: this.estimatedHours,
          address: this.bookingData.address,
          ville: this.bookingData.quartier
        });

        const response = await bookingService.createIntervention(formData);
        console.log('Booking response:', response);
        
        this.$emit('success');
        alert('Demande de service envoyée avec succès !');
        this.$emit('close');
        
      } catch (error) {
        console.error('Error submitting booking:', error);
        
        let errorMessage = 'Erreur lors de l\'envoi de la demande. Veuillez réessayer.';
        
        // Handle custom rejected object from api.js OR standard axios error
        const errorData = error.data || error.response?.data;
        const errors = error.errors || errorData?.errors;
        const message = error.message || errorData?.message;

        if (errors) {
          // Gestion des erreurs de validation (Array of arrays or Array of strings)
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
    }
  }
};
</script>

<style scoped>
/* Styles pour les indicateurs de durée */
.duration-indicator {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: bold;
}

/* Animation de chargement */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Tooltip */
.group:hover .group-hover\:block {
  display: block !important;
}
</style>