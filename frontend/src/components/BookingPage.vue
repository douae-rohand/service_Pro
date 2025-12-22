<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header (Non-sticky) -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
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
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Steps -->
    <div class="bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-gray-100">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-20 overflow-x-auto no-scrollbar">
          <div v-for="step in 6" :key="step" class="flex items-center shrink-0">
            <div 
              class="flex items-center gap-3 px-4 py-2 rounded-xl transition-all duration-300"
              :class="currentStep === step ? 'bg-[#E8F5E9] text-[#6B8E6D]' : 'text-gray-400'"
            >
              <div 
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300"
                :class="currentStep > step ? 'bg-[#92B08B] text-white' : currentStep === step ? 'bg-[#92B08B] text-white shadow-lg shadow-[#92B08B]/20' : 'bg-gray-100 text-gray-400'"
              >
                <Check v-if="currentStep > step" :size="16" />
                <span v-else class="text-xs font-bold">{{ step }}</span>
              </div>
              <span class="font-semibold text-sm whitespace-nowrap">
                {{ ['Service', 'Tâche', 'Détails', 'Date', 'Horaire', 'Récapitulatif'][step-1] }}
              </span>
            </div>
            <div 
              v-if="step < 6" 
              class="w-8 h-px bg-gray-100 mx-2"
              :class="{ 'bg-[#E8F5E9]': currentStep > step }"
            ></div>
          </div>
        </div>
      </div>
    </div>


    <!-- Step Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Step 1: Choose Service -->
      <div v-if="currentStep === 1" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Quel service souhaitez-vous ?</h3>
          <p class="text-gray-500">Sélectionnez le type de prestation pour continuer votre réservation.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="service in services"
            :key="service.id"
            @click="selectService(service)"
            class="group relative p-6 bg-white rounded-3xl border-2 transition-all duration-300 cursor-pointer overflow-hidden"
            :class="selectedService?.id === service.id ? 'border-[#92B08B] bg-[#E8F5E9]/30' : 'border-gray-100 hover:border-[#92B08B]/30 hover:shadow-xl hover:shadow-[#92B08B]/5'"
          >
            <div class="relative z-10">
              <div 
                class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300"
                :class="selectedService?.id === service.id ? 'bg-[#92B08B] text-white shadow-lg shadow-[#92B08B]/20' : 'bg-gray-50 text-gray-400 group-hover:bg-[#E8F5E9] group-hover:text-[#92B08B]'"
              >
                <Check v-if="selectedService?.id === service.id" :size="24" />
                <Package v-else :size="24" />
              </div>
              <h4 class="font-black text-xl mb-2 transition-colors" :class="selectedService?.id === service.id ? 'text-[#6B8E6D]' : 'text-gray-800'">
                {{ service.nom_service }}
              </h4>
              <p class="text-sm text-gray-500 leading-relaxed">
                {{ service.taches_count || 0 }} options disponibles pour ce service.
              </p>
            </div>
            
            <!-- Shadow effect on selection -->
            <div 
              v-if="selectedService?.id === service.id" 
              class="absolute -right-8 -bottom-8 w-32 h-32 bg-[#92B08B]/10 rounded-full blur-3xl transition-all duration-500 scale-150"
            ></div>
          </div>
        </div>
      </div>

      <!-- Step 2: Choose Task -->
      <div v-if="currentStep === 2" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Plus précisément ?</h3>
          <p class="text-gray-500">Choisissez la tâche spécifique à accomplir par l'intervenant.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="task in tasks"
            :key="task.id"
            @click="selectTask(task)"
            class="group p-5 bg-white border-2 rounded-2xl transition-all duration-300 text-left relative overflow-hidden"
            :class="selectedTask?.id === task.id ? 'border-green-500 bg-green-50/30' : 'border-gray-100 hover:border-green-200 hover:shadow-lg hover:shadow-green-900/5'"
          >
            <div class="flex items-center gap-4 relative z-10">
              <div 
                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all duration-300"
                :class="selectedTask?.id === task.id ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-gray-50 text-gray-400 group-hover:bg-green-100 group-hover:text-green-600'"
              >
                <Check v-if="selectedTask?.id === task.id" :size="20" />
                <span v-else class="text-xs font-bold">{{ task.nom_tache.charAt(0) }}</span>
              </div>
              <span class="font-bold text-gray-800 transition-colors" :class="{ 'text-green-900': selectedTask?.id === task.id }">
                {{ task.nom_tache }}
              </span>
            </div>
          </button>
        </div>
      </div>

      <!-- Step 3: Details (same as BookingModal) -->
      <div v-if="currentStep === 3" class="space-y-6">
        <!-- Address & City -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-3 text-gray-800">
            <div class="w-8 h-8 rounded-lg bg-[#E8F5E9] flex items-center justify-center">
              <MapPin :size="20" class="text-[#92B08B]" />
            </div>
            Adresse & Ville du service
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Adresse exacte</label>
              <input
                v-model="bookingData.address"
                type="text"
                placeholder="Numéro, rue, immeuble..."
                class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#92B08B]/20 focus:border-[#92B08B] transition-all outline-none"
              />
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Ville</label>
              <input
                v-model="bookingData.ville"
                type="text"
                placeholder="Ex: Tétouan, Martil..."
                class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#92B08B]/20 focus:border-[#92B08B] transition-all outline-none"
              />
            </div>
          </div>
        </div>

        <!-- Specific Information & Constraints -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-3 text-gray-800">
            <div class="w-8 h-8 rounded-lg bg-[#b8c5d1] flex items-center justify-center">
               <Clock :size="20" class="text-[#4682B4]" />
            </div>
            Informations spécifiques
          </h4>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
              <label class="block text-sm font-semibold text-gray-700">Contraintes de la tâche</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="c in constraints" :key="c.id" class="p-4 bg-gray-50/50 border border-gray-100 rounded-xl">
                  <div class="flex items-center justify-between mb-3">
                    <span class="font-bold text-sm text-gray-700">{{ c.nom }}</span>
                    <span class="text-[10px] font-medium px-2 py-0.5 bg-white rounded-full text-gray-500 border border-gray-100">
                      {{ c.unite }}
                    </span>
                  </div>
                  <input
                    v-model.number="constraintsValues[c.id]"
                    type="number"
                    min="0"
                    placeholder="0"
                    class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#92B08B]/20 focus:border-[#92B08B] transition-all outline-none text-right font-mono"
                    @input="updateDurationEstimation"
                  />
                  <p class="mt-2 text-[10px] text-gray-500 flex items-center gap-1">
                    <Info :size="10" />
                    {{ c.unite === 'm²' || c.unite === 'm2' ? '20 m² ≈ 1h' : `Seuil: ${c.seuil} ${c.unite} ≈ 1h` }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Durée estimée Panel -->
            <div class="bg-gradient-to-br from-[#4682B4] to-[#36648B] rounded-2xl p-6 text-white shadow-lg shadow-[#4682B4]/20 flex flex-col justify-center items-center text-center relative overflow-hidden">
              <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
              <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-[#b8c5d1]/20 rounded-full blur-2xl"></div>
              
              <Clock :size="32" class="mb-4 text-[#b8c5d1]" />
              <div class="text-4xl font-extrabold mb-1">{{ estimatedHours }}</div>
              <div class="text-[#b8c5d1] font-medium uppercase tracking-wider text-xs">Heures estimées</div>
              
              <div v-if="estimatedHours > 8" class="mt-4 p-2 bg-white/20 backdrop-blur-md rounded-lg flex items-start gap-2 text-left">
                <AlertCircle :size="14" class="shrink-0 mt-0.5" />
                <p class="text-[10px] leading-tight">Attention: Durée importante. Disponibilités à vérifier.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Description détaillée -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
          <h4 class="text-lg font-bold mb-4 flex items-center gap-3 text-gray-800">
            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
               <FileText :size="20" class="text-purple-600" />
            </div>
            Description détaillée (optionnel)
          </h4>
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700">
              Décrivez précisément votre besoin
            </label>
            <textarea
              v-model="bookingData.description"
              rows="4"
              placeholder="Ex: J'ai besoin d'une aide pour nettoyer mon salon et ma cuisine. Le salon fait environ 20m² et la cuisine 15m²..."
              class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all outline-none resize-none"
              maxlength="500"
            ></textarea>
            <div class="flex justify-between items-center text-xs">
              <p class="text-gray-500 flex items-center gap-1">
                <AlertCircle :size="12" />
                ⚠️ Ne partagez pas vos coordonnées personnelles (téléphone, email, réseaux sociaux)
              </p>
              <span class="text-gray-400 font-mono">
                {{ (bookingData.description || '').length }}/500
              </span>
            </div>
          </div>
        </div>

        <!-- Materials -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
          <div class="flex items-center justify-between mb-6">
            <h4 class="text-lg font-bold flex items-center gap-3 text-gray-800">
              <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <Package :size="20" class="text-[#4682B4]" />
              </div>
              Matériel
            </h4>
            <div class="flex items-center gap-2 text-sm">
               <span class="text-gray-500 italic">Vous fournissez:</span>
               <span class="font-bold text-[#4682B4] px-2 py-0.5 bg-blue-50 rounded-full border border-blue-100">
                {{ bookingData.materials.length }}/{{ materials.length }}
               </span>
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label
              v-for="material in materials"
              :key="material.id"
              class="group relative flex items-center gap-4 p-4 bg-gray-50/50 border border-gray-100 rounded-xl cursor-pointer transition-all duration-300 hover:bg-white hover:border-[#4682B4]/30 hover:shadow-md hover:-translate-y-1"
              :class="{ 'bg-white border-[#4682B4]/20 shadow-sm': bookingData.materials.includes(material.id) }"
            >
              <div class="relative flex items-center justify-center w-6 h-6">
                <input
                  v-model="bookingData.materials"
                  type="checkbox"
                  :value="material.id"
                  class="peer appearance-none w-6 h-6 border-2 border-gray-300 rounded-lg checked:bg-[#4682B4] checked:border-[#4682B4] transition-all cursor-pointer"
                />
                <Check :size="14" class="absolute text-white scale-0 peer-checked:scale-100 transition-transform pointer-events-none" />
              </div>
              <div class="flex-1">
                <p class="text-sm font-bold text-gray-700 group-hover:text-[#4682B4] transition-colors duration-300 hover:underline decoration-skip-ink">{{ material.nom_materiel }}</p>
                <p class="text-[10px] text-gray-500">
                  {{ bookingData.materials.includes(material.id) ? 'Fourni par vous' : 'Fourni par l\'intervenant' }}
                </p>
              </div>
              <div class="shrink-0">
                <span 
                  class="text-xs font-bold px-2 py-1 rounded-lg border transition-all duration-300"
                  :class="bookingData.materials.includes(material.id) ? 'text-gray-400 bg-gray-50 border-gray-100' : 'text-[#4682B4] bg-blue-50 border-blue-100'"
                >
                  {{ bookingData.materials.includes(material.id) ? '0 DH' : `+${material.cost ?? material.pivot?.prix_materiel ?? 0} DH` }}
                </span>
              </div>
            </label>
          </div>
        </div>


        <!-- Cost Estimation Box (Horizontal Style) - Only show after Step 3 -->
        <div v-if="currentStep > 3" class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl overflow-hidden max-w-4xl mx-auto flex flex-col md:flex-row transition-all duration-500 hover:shadow-blue-900/10">
          <!-- Left side: Brand Summary -->
          <div class="bg-[#4682B4] p-10 text-white md:w-1/3 flex flex-col justify-center items-center text-center relative overflow-hidden">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-blue-900/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
              <h4 class="text-3xl font-black mb-3 tracking-tight">Récapitulatif</h4>
              <div class="h-1 w-12 bg-[#92B08B] mx-auto rounded-full mb-4"></div>
              <p class="text-xs text-blue-100 uppercase tracking-[0.2em] font-bold opacity-80">Estimation des coûts</p>
            </div>
          </div>

          <!-- Right side: Detailed Breakdown -->
          <div class="flex-1 p-8 sm:p-10 relative">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-6">
              <!-- Item 1 -->
              <div class="flex flex-col gap-1 border-b border-dashed border-gray-100 pb-3">
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Tarif horaire</span>
                <span class="text-lg font-black text-gray-800">{{ intervenant?.hourlyRate || '0' }} <span class="text-xs font-bold text-gray-400">DH/h</span></span>
              </div>
              
              <!-- Item 2 -->
              <div class="flex flex-col gap-1 border-b border-dashed border-gray-200 pb-3">
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Durée estimée</span>
                <span class="text-lg font-black text-gray-800">x {{ estimatedHours }} <span class="text-xs font-bold text-gray-400">Heures</span></span>
              </div>

              <!-- Item 3 -->
              <div class="flex flex-col gap-1 border-b border-dashed border-gray-100 pb-3">
                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Main d'œuvre</span>
                <span class="text-lg font-black text-gray-800">{{ intervenant?.hourlyRate * estimatedHours }} <span class="text-xs font-bold text-gray-400">DH</span></span>
              </div>

              <!-- Item 4 -->
              <div class="flex flex-col gap-1 border-b border-dashed border-gray-200 pb-3">
                <div class="flex justify-between items-start">
                  <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Matériel</span>
                  <span class="text-[8px] font-bold px-1.5 py-0.5 bg-blue-50 text-[#4682B4] rounded-md border border-blue-100">EXTRA</span>
                </div>
                <span class="text-lg font-black text-[#4682B4]">+ {{ materialsCost }} <span class="text-xs font-bold text-gray-400">DH</span></span>
              </div>
            </div>

            <!-- Total Section -->
            <div class="mt-10 pt-8 border-t-2 border-gray-50 flex flex-col sm:flex-row justify-between items-center gap-6">
              <div class="text-center sm:text-left">
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Final Estimé</p>
                <div class="flex items-baseline gap-2">
                  <span class="text-5xl font-black text-gray-900 tracking-tighter">{{ finalCost }}</span>
                  <span class="text-xl font-bold text-gray-400">DH</span>
                </div>
              </div>
              
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#92B08B]/10 flex items-center justify-center text-[#92B08B] shadow-inner">
                  <Check :size="32" stroke-width="3" />
                </div>
              </div>
            </div>

            <!-- Warning Notice -->
            <div class="mt-8 p-4 bg-yellow-50/50 rounded-2xl border border-yellow-100/50 flex gap-3">
              <AlertCircle :size="18" class="text-yellow-600 shrink-0 mt-0.5" />
              <p class="text-[10px] text-yellow-800 leading-relaxed font-medium">
                Le coût final sera validé par l'intervenant après examen de la demande. Vous ne paierez qu'une fois la mission terminée.
              </p>
            </div>
          </div>
        </div>

        <!-- Alerte détection instantanée -->
        <div 
          v-if="personalInfoDetected" 
          class="bg-red-50 border-2 border-red-200 rounded-3xl p-6 shadow-lg animate-in slide-in-from-top-2 duration-300"
        >
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-red-500 rounded-2xl flex items-center justify-center shrink-0 shadow-lg">
              <AlertCircle :size="24" class="text-white" />
            </div>
            <div class="flex-1">
              <h4 class="text-lg font-black text-red-900 mb-1">
                🚫 Action requise
              </h4>
              <p class="text-sm text-red-700 font-bold mb-2">
                {{ personalInfoDetected }}
              </p>
              <p class="text-xs text-red-600 leading-relaxed font-medium">
                Pour des raisons de sécurité, les numéros de téléphone, emails et réseaux sociaux ne sont pas autorisés. 
                Veuillez retirer ces informations pour continuer.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 4: Date Selection -->
      <div v-if="currentStep === 4" class="space-y-8 animate-in fade-in slide-in-from-left-4 duration-500">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Quand doit-on intervenir ?</h3>
          <p class="text-gray-500">Sélectionnez une date pour vérifier les disponibilités de l'intervenant.</p>
        </div>
        
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 border border-gray-100 shadow-xl max-w-lg mx-auto">
          <div class="flex items-center gap-3 mb-6 bg-[#b8c5d1]/30 p-4 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-[#4682B4] text-white flex items-center justify-center shadow-lg shadow-[#4682B4]/20">
              <Calendar :size="20" />
            </div>
            <div>
              <p class="text-xs text-[#4682B4] font-bold uppercase tracking-wider">Date souhaitée</p>
              <p class="text-sm text-gray-700 font-medium">{{ bookingData.date ? formatDate(bookingData.date) : 'Aucune date choisie' }}</p>
            </div>
          </div>
          
          <input
            v-model="bookingData.date"
            type="date"
            :min="minDate"
            class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-[#4682B4]/10 focus:border-[#4682B4] transition-all outline-none font-bold text-lg text-center"
            @change="checkDayAvailability"
          />
        </div>

        <!-- Validation de la journée -->
        <div v-if="bookingData.date" class="max-w-lg mx-auto mt-8">
          <div v-if="dayCheckLoading" class="flex flex-col items-center py-8">
            <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="mt-4 text-sm font-bold text-gray-500">Analyse du planning...</p>
          </div>
          
          <div v-else-if="dayAvailabilityResult" class="animate-in zoom-in-95 duration-300">
            <div 
              class="rounded-3xl p-6 border transition-all"
              :class="dayAvailabilityResult.hasEnoughTime ? 'bg-green-50/50 border-green-100' : 'bg-red-50/50 border-red-100'"
            >
              <div class="flex items-start gap-4">
                <div 
                  class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                  :class="dayAvailabilityResult.hasEnoughTime ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
                >
                  <Check v-if="dayAvailabilityResult.hasEnoughTime" :size="20" />
                  <X v-else :size="20" />
                </div>
                <div class="flex-1">
                  <p class="font-black text-lg" :class="dayAvailabilityResult.hasEnoughTime ? 'text-green-900' : 'text-red-900'">
                    {{ dayAvailabilityResult.hasEnoughTime ? 'C\'est parfait !' : 'Oups, petit souci...' }}
                  </p>
                  <p class="text-sm mt-1 leading-relaxed" :class="dayAvailabilityResult.hasEnoughTime ? 'text-green-700' : 'text-red-700'">
                    {{ dayAvailabilityResult.message }}
                  </p>
                </div>
              </div>
              
              <div v-if="dayAvailabilityResult.availableSlots?.length > 0" class="mt-6 pt-6 border-t border-[#92B08B]/10">
                 <p class="text-[10px] font-bold text-[#92B08B] uppercase mb-3 text-center">Horaires de début conseillés</p>
                 <div class="flex flex-wrap justify-center gap-2">
                    <span v-for="slot in dayAvailabilityResult.availableSlots" :key="slot" class="px-3 py-1 bg-white border border-[#92B08B]/20 rounded-lg text-xs font-bold text-[#92B08B] shadow-sm">
                      {{ slot }}
                    </span>
                 </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 5: Time Slot Selection -->
      <div v-if="currentStep === 5" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-extrabold text-gray-900 mb-2">À quelle heure ?</h3>
          <p class="text-gray-500">Choisissez l'heure de début. Nous réserverons automatiquement {{ estimatedHours }} heure(s) consécutives.</p>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
            <button
              v-for="slot in timeSlots"
              :key="slot.time"
              @click="selectTimeSlot(slot)"
              :disabled="!slot.available || !canSelectSlot(slot)"
              class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300"
              :class="getSlotClass(slot)"
            >
              <span class="text-lg font-black">{{ slot.time }}</span>
              <span v-if="isSlotSelected(slot)" class="text-[10px] font-bold uppercase mt-1">Début</span>
              
              <div v-if="isSlotSelected(slot)" class="absolute -top-2 -right-2 w-6 h-6 bg-[#4682B4] text-white rounded-lg flex items-center justify-center shadow-lg animate-in zoom-in duration-300">
                <Check :size="14" />
              </div>

              <div v-if="!slot.available || !canSelectSlot(slot)" class="absolute inset-0 bg-white/40 backdrop-blur-[1px] rounded-2xl flex items-center justify-center pointer-events-none">
                <X :size="20" class="text-red-500/50" />
              </div>
            </button>
          </div>
        </div>

        <!-- Selected Slots Summary -->
        <div v-if="selectedTimeSlots.length > 0" class="max-w-lg mx-auto bg-green-900 rounded-3xl p-6 text-white shadow-2xl animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="flex items-center gap-4 mb-4">
             <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                <Clock :size="24" class="text-green-400" />
             </div>
             <div>
                <p class="text-[10px] text-green-300 font-bold uppercase tracking-widest leading-none">Plage horaire réservée</p>
                <p class="text-xl font-black">{{ selectedTimeSlots[0]?.time }} — {{ selectedTimeSlots[selectedTimeSlots.length-1]?.time.split(':')[0] }}:{{ selectedTimeSlots[selectedTimeSlots.length-1]?.time.split(':')[1] === '00' ? '59' : '59' }}</p>
             </div>
          </div>
          <div class="flex flex-wrap gap-2 pt-4 border-t border-white/10">
             <span v-for="s in selectedTimeSlots" :key="s.time" class="text-xs font-bold px-3 py-1 bg-white/10 rounded-full border border-white/5">
               {{ s.time }}
             </span>
          </div>
        </div>

        <div v-if="slotSelectionError" class="max-w-lg mx-auto bg-red-50 border border-red-100 rounded-2xl p-4 flex items-start gap-3 animate-head-shake">
          <AlertCircle :size="18" class="text-red-600 shrink-0 mt-0.5" />
          <p class="text-xs text-red-700 font-medium">{{ slotSelectionError }}</p>
        </div>
      </div>

      <!-- Step 6: Confirmation Recap -->
      <div v-if="currentStep === 6" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="text-center max-w-2xl mx-auto">
          <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Vérifiez vos informations</h3>
          <p class="text-gray-500">Dernière étape avant d'envoyer votre demande de réservation à {{ intervenant?.name }}.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Left Column: Details -->
          <div class="space-y-6">
            <!-- Service Info -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
              <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Service & Tâche</h4>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#E8F5E9] flex items-center justify-center text-[#92B08B]">
                  <Package :size="24" />
                </div>
                <div>
                  <p class="font-bold text-gray-900">{{ selectedService?.nom_service }}</p>
                  <p class="text-sm text-gray-500">{{ selectedTask?.nom_tache }}</p>
                </div>
              </div>
            </div>

            <!-- Place & Time -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
              <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Lieu & Date</h4>
              <div class="space-y-4">
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-[#4682B4] shrink-0">
                    <MapPin :size="20" />
                  </div>
                  <div>
                    <p class="font-bold text-gray-900">{{ bookingData.address }}</p>
                    <p class="text-sm text-gray-500">{{ bookingData.ville }}</p>
                  </div>
                </div>
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 shrink-0">
                    <Calendar :size="20" />
                  </div>
                  <div>
                    <p class="font-bold text-gray-900">{{ formatDate(bookingData.date) }}</p>
                    <p class="text-sm text-gray-500">À partir de {{ bookingData.time }} ({{ estimatedHours }}h estimées)</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Cost Recap -->
          <div class="space-y-6">
             <div class="bg-[#4682B4] rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <h4 class="text-xl font-black mb-6 flex items-center gap-2">
                  <DollarSign :size="24" />
                  Estimation finale
                </h4>
                
                <div class="space-y-4 mb-8">
                  <div class="flex justify-between items-center text-blue-100">
                    <span>Main d'œuvre ({{ estimatedHours }}h)</span>
                    <span class="font-bold">{{ intervenant?.hourlyRate * estimatedHours }} DH</span>
                  </div>
                  <div class="flex justify-between items-center text-blue-100">
                    <span>Matériel</span>
                    <span class="font-bold">{{ materialsCost }} DH</span>
                  </div>
                  <div class="pt-4 border-t border-white/20 flex justify-between items-end">
                    <span class="text-blue-50 font-bold">Total Estimé</span>
                    <div class="text-right">
                      <span class="text-4xl font-black">{{ finalCost }}</span>
                      <span class="text-xl ml-1 font-bold">DH</span>
                    </div>
                  </div>
                </div>

                <div class="bg-white/10 rounded-xl p-4 flex gap-3 text-xs leading-relaxed text-blue-50">
                  <Info :size="16" class="shrink-0" />
                  <p>Le prix final est basé sur l'estimation de durée. Il peut être ajusté par l'intervenant après discussion.</p>
                </div>
             </div>

             <!-- Materials Summary -->
             <div v-if="bookingData.materials.length > 0" class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Matériel que vous fournissez</h4>
                <div class="flex flex-wrap gap-2">
                  <span v-for="matId in bookingData.materials" :key="matId" class="px-3 py-1 bg-white border border-gray-200 rounded-full text-xs text-gray-600 font-medium">
                    {{ materials.find(m => m.id === matId)?.nom_materiel }}
                  </span>
                </div>
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
            :class="canProceed && !(currentStep === 4 && !dayAvailabilityResult?.hasEnoughTime) && !loading ? 'bg-[#92B08B] hover:opacity-90' : 'bg-gray-400 cursor-not-allowed'"
          >
            <span v-if="loading">Envoi en cours...</span>
            <span v-else>{{ currentStep === 6 ? 'Confirmer la réservation' : 'Continuer →' }}</span>
            <ArrowRight v-if="currentStep < 6 && !loading" :size="20" />
            <Check v-else-if="currentStep === 6 && !loading" :size="20" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  X, Calendar, Clock, FileText, Check, Star, MapPin, AlertCircle,
  Package, Info, DollarSign, ArrowLeft, ArrowRight
} from 'lucide-vue-next';
import bookingService from '@/services/bookingService';
import authService from '@/services/authService';
import api from '@/services/api';

export default {
  name: 'BookingPage',
  components: {
    X, Calendar, Clock, FileText, Check, Star, MapPin, AlertCircle,
    Package, Info, DollarSign, ArrowLeft, ArrowRight
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
        description: ''
      },
      timeSlots: [],
      availableSlots: [],
      selectedTimeSlots: [],
      materials: [],
      constraints: [],
      constraintsValues: {},
      dayAvailabilityResult: null,
      dayCheckLoading: false,
      loading: false,
      slotSelectionError: null,
      reservedSlots: [],
      existingInterventions: []
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
                 this.estimatedHours > 0 &&
                 this.personalInfoDetected === null; // Block if personal info detected
        case 4:
          return this.bookingData.date !== '' && 
                 this.dayAvailabilityResult?.hasEnoughTime === true;
        case 5:
          return this.selectedTimeSlots.length === this.estimatedHours;
        case 6:
          return true;
        default:
          return false;
      }
    },
    materialsCost() {
      const selectedMaterialIds = this.bookingData.materials;
      return this.materials
        .filter(m => !selectedMaterialIds.includes(m.id))
        .reduce((sum, m) => {
          const cost = Number(m.cost ?? m.pivot?.prix_materiel ?? 0);
          return sum + cost;
        }, 0);
    },
    estimatedHours() {
      let hours = 0;
      this.constraints.forEach(c => {
        const v = Number(this.constraintsValues[c.id] || 0);
        const seuil = Number(c.seuil || 0);
        const unite = String(c.unite || '').toLowerCase();
        
        if (v > 0) {
          // Pour les contraintes d'espace (m²), utiliser 20m² = 1hr
          if (unite === 'm²' || unite === 'm2') {
            hours += Math.ceil(v / 20);
          } else if (seuil > 0) {
            // Pour les autres contraintes, utiliser le seuil existant
            hours += Math.ceil(v / seuil);
          }
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
    },
    personalInfoDetected() {
      // Lazy load validation utils
      const validateUserInput = (text, fieldName) => {
        if (!text) return { valid: true };
        
        // Phone patterns
        const phonePatterns = [
          /\b0[567]\d{8}\b/g,
          /\b\+?212\s?[567]\d{8}\b/g,
          /\b0[567][\s.-]?\d{2}[\s.-]?\d{2}[\s.-]?\d{2}[\s.-]?\d{2}\b/g,
          /\b\d{10}\b/g,
        ];
        
        // Email pattern
        const emailPattern = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
        
        // Social media patterns
        const socialPatterns = [
          /@[a-zA-Z0-9_]{2,}/g,
          /\b(facebook|fb|insta|instagram|whatsapp|telegram|snapchat|tiktok)[\s.:]/gi,
          /\bwa\.me\//gi,
        ];
        
        // Check phones
        if (phonePatterns.some(p => p.test(text))) {
          return { valid: false, reason: `⚠️ Numéro de téléphone détecté dans ${fieldName}` };
        }
        
        // Check email
        if (emailPattern.test(text)) {
          return { valid: false, reason: `⚠️ Email détecté dans ${fieldName}` };
        }
        
        // Check social media
        if (socialPatterns.some(p => p.test(text))) {
          return { valid: false, reason: `⚠️ Réseau social détecté dans ${fieldName}` };
        }
        
        return { valid: true };
      };
      
      // Validate all constraint values
      for (const [key, value] of Object.entries(this.constraintsValues)) {
        if (typeof value === 'string' && value.trim()) {
          const validation = validateUserInput(value, 'les détails');
          if (!validation.valid) {
            return validation.reason;
          }
        }
      }
      
      // Validate address
      const addressValidation = validateUserInput(this.bookingData.address, 'l\'adresse');
      if (!addressValidation.valid) {
        return addressValidation.reason;
      }
      
      // Validate ville
      const villeValidation = validateUserInput(this.bookingData.ville, 'la ville');
      if (!villeValidation.valid) {
        return villeValidation.reason;
      }
      
      // Validate description
      if (this.bookingData.description && this.bookingData.description.trim()) {
        const descValidation = validateUserInput(this.bookingData.description, 'la description');
        if (!descValidation.valid) {
          return descValidation.reason;
        }
      }
      
      return null; // No personal info detected
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
    
    // Initialiser l'adresse et la ville depuis le profil client (depuis la base de données)
    if (user.client) {
      // L'adresse et la ville sont dans la table client
      if (user.client.address) {
        this.bookingData.address = user.client.address;
      }
      if (user.client.ville) {
        this.bookingData.ville = user.client.ville;
      }
    } else {
      // Fallback si pas de client mais adresse dans utilisateur
      if (user.address) this.bookingData.address = user.address;
      if (user.ville) this.bookingData.ville = user.ville;
    }
    
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
      // Récupérer le tarif spécifique de l'intervenant pour cette tâche
      if (this.intervenant) {
        const pivot = task.intervenants?.find(i => i.id === this.intervenant.id)?.pivot || 
                      task.intervenant_tache || 
                      task.pivot;
        
        if (pivot && pivot.prix_tache) {
          this.intervenant.hourlyRate = Number(pivot.prix_tache);
        } else if (task.tarif_tache) {
          this.intervenant.hourlyRate = Number(task.tarif_tache);
        }
      }
      
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
      this.slotSelectionError = null;
      
      try {
        // Récupérer les disponibilités
        const disponibilitesResponse = await bookingService.getIntervenantDisponibilites(
          this.intervenant.id,
          this.bookingData.date
        );
        
        // Récupérer les interventions existantes pour cette date
        let interventionsData = [];
        try {
          const interventionsResponse = await api.get(`intervenants/${this.intervenant.id}/interventions`);
          const allInterventions = interventionsResponse.data?.data || interventionsResponse.data || [];
          
          // Filtrer les interventions pour la date sélectionnée
          const selectedDate = new Date(this.bookingData.date);
          interventionsData = allInterventions.filter(intervention => {
            if (!intervention.date_intervention) return false;
            const interventionDate = new Date(intervention.date_intervention);
            return interventionDate.toDateString() === selectedDate.toDateString();
          });
        } catch (error) {
          // Si l'endpoint n'existe pas, on continue sans interventions
          console.log('Interventions endpoint not available, continuing without reserved slots check');
        }
        
        const disponibilites = disponibilitesResponse.data?.data || disponibilitesResponse.data || [];
        this.existingInterventions = interventionsData;
        
        // Calculer les créneaux réservés
        this.reservedSlots = this.calculateReservedSlots(this.existingInterventions);
        
        const allSlots = this.generateTimeSlots();
        const markedSlots = allSlots.map(slot => ({
          ...slot,
          available: this.checkSlotAvailability(slot.time, disponibilites),
          reserved: this.isSlotReserved(slot.time)
        }));
        
        this.timeSlots = markedSlots;
        
        // Calculer les heures libres disponibles
        const freeHours = this.calculateFreeHours(markedSlots);
        const possibleStartSlots = this.findPossibleStartSlots(markedSlots);
        const hasEnoughTime = possibleStartSlots.length > 0;
        
        this.dayAvailabilityResult = {
          hasEnoughTime,
          freeHours,
          message: hasEnoughTime 
            ? `Disponibilité confirmée pour ${this.estimatedHours} heure(s) le ${this.formatDate(this.bookingData.date)}`
            : `Pas assez de créneaux disponibles pour ${this.estimatedHours} heure(s) le ${this.formatDate(this.bookingData.date)}`,
          details: hasEnoughTime
            ? `${possibleStartSlots.length} créneau(x) de départ possible(s)`
            : freeHours > 0
              ? `Cette journée n'a que ${freeHours} heure(s) libre(s) disponible(s), mais vous avez besoin de ${this.estimatedHours} heure(s). Veuillez choisir une autre date.`
              : `Aucune heure libre disponible ce jour. Veuillez choisir une autre date.`,
          availableSlots: hasEnoughTime ? possibleStartSlots.map(s => s.time) : []
        };
        
      } catch (error) {
        console.error('Error checking day availability:', error);
        this.dayAvailabilityResult = {
          hasEnoughTime: false,
          freeHours: 0,
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
      // Permettre la sélection même si certains créneaux sont réservés (on vérifiera lors du clic)
      if (!slot.available) return false;
      
      const slotIndex = this.timeSlots.findIndex(s => s.time === slot.time);
      if (slotIndex === -1) return false;
      
      // Vérifier qu'il y a assez de créneaux (même s'ils sont réservés, on affichera un message)
      for (let i = 1; i < this.estimatedHours; i++) {
        const nextSlot = this.timeSlots[slotIndex + i];
        if (!nextSlot) {
          return false;
        }
      }
      
      return true;
    },
    calculateReservedSlots(interventions) {
      const reserved = [];
      
      interventions.forEach(intervention => {
        if (!intervention.date_intervention) return;
        
        const interventionDate = new Date(intervention.date_intervention);
        const selectedDate = new Date(this.bookingData.date);
        
        // Vérifier si l'intervention est le même jour
        if (interventionDate.toDateString() === selectedDate.toDateString()) {
          const startTime = interventionDate.toTimeString().substring(0, 5); // HH:MM
          const duration = Number(intervention.duration_hours || 1);
          
          // Ajouter tous les créneaux réservés
          const startMinutes = this.timeToMinutes(startTime);
          for (let i = 0; i < Math.ceil(duration); i++) {
            const reservedTime = this.minutesToTime(startMinutes + (i * 60));
            reserved.push(reservedTime);
          }
        }
      });
      
      return reserved;
    },
    isSlotReserved(time) {
      return this.reservedSlots.includes(time);
    },
    calculateFreeHours(slots) {
      let freeHours = 0;
      let consecutiveFree = 0;
      
      slots.forEach(slot => {
        if (slot.available && !slot.reserved) {
          consecutiveFree++;
        } else {
          if (consecutiveFree > freeHours) {
            freeHours = consecutiveFree;
          }
          consecutiveFree = 0;
        }
      });
      
      // Vérifier le dernier segment
      if (consecutiveFree > freeHours) {
        freeHours = consecutiveFree;
      }
      
      return freeHours;
    },
    minutesToTime(minutes) {
      const hours = Math.floor(minutes / 60);
      const mins = minutes % 60;
      return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
    },
    getSlotClass(slot) {
      if (this.isSlotReserved(slot.time)) {
        return 'border-red-300 bg-red-100 opacity-75 cursor-pointer';
      }
      
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
      // Permettre la sélection même si certains créneaux sont réservés
      const slotIndex = this.timeSlots.findIndex(s => s.time === slot.time);
      if (slotIndex === -1) return;
      
      // Vérifier si tous les créneaux nécessaires sont disponibles
      const requiredSlots = [];
      const reservedInSelection = [];
      
      for (let i = 0; i < this.estimatedHours; i++) {
        const timeSlot = this.timeSlots[slotIndex + i];
        if (!timeSlot) {
          this.slotSelectionError = `Créneau ${slot.time} : Pas assez de créneaux disponibles pour ${this.estimatedHours} heure(s).`;
          this.selectedTimeSlots = [];
          return;
        }
        
        requiredSlots.push(timeSlot);
        
        // Vérifier si ce créneau est réservé
        if (this.isSlotReserved(timeSlot.time)) {
          reservedInSelection.push(timeSlot.time);
        }
      }
      
      // Si certains créneaux sont réservés, afficher un message d'erreur
      if (reservedInSelection.length > 0) {
        this.slotSelectionError = `Les créneaux suivants sont déjà réservés : ${reservedInSelection.join(', ')}. Veuillez choisir d'autres heures.`;
        this.selectedTimeSlots = [];
        return;
      }
      
      // Si tous les créneaux sont disponibles, sélectionner
      if (requiredSlots.every(s => s.available)) {
        this.selectedTimeSlots = requiredSlots;
        this.bookingData.time = slot.time;
        this.slotSelectionError = null;
      } else {
        const unavailableSlots = requiredSlots.filter(s => !s.available).map(s => s.time);
        this.slotSelectionError = `Les créneaux suivants ne sont pas disponibles : ${unavailableSlots.join(', ')}. Veuillez choisir d'autres heures.`;
        this.selectedTimeSlots = [];
      }
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
        const response = await api.get(`taches/${this.selectedTask?.id}/materiels`, {
          params: { intervenantId: this.intervenant?.id }
        });
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
      
      if (this.currentStep === 6) {
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
        // ======================================
        // VALIDATION: Check for personal information in constraints
        // ====================================== 
        const { validateUserInput } = await import('@/utils/inputValidation.js');
        
        // Validate all constraint values
        for (const [key, value] of Object.entries(this.constraintsValues)) {
          if (typeof value === 'string' && value.trim()) {
            const validation = validateUserInput(value, 'les détails de la demande');
            if (!validation.valid) {
              alert(validation.reason + '\n\n🔒 Pour votre sécurité et celle des intervenants, tous les échanges doivent se faire via notre plateforme sécurisée.');
              this.loading = false;
              return; // Block the booking
            }
          }
        }
        
        // Validate address field
        const addressValidation = validateUserInput(this.bookingData.address, 'l\'adresse');
        if (!addressValidation.valid) {
          alert(addressValidation.reason);
          this.loading = false;
          return;
        }
        
        // Validate ville field
        const villeValidation = validateUserInput(this.bookingData.ville, 'la ville');
        if (!villeValidation.valid) {
          alert(villeValidation.reason);
          this.loading = false;
          return;
        }
        
        // Validate description field
        if (this.bookingData.description && this.bookingData.description.trim()) {
          const descriptionValidation = validateUserInput(this.bookingData.description, 'la description');
          if (!descriptionValidation.valid) {
            alert(descriptionValidation.reason + '\n\n🔒 Pour votre sécurité et celle des intervenants, tous les échanges doivent se faire via notre plateforme sécurisée.');
            this.loading = false;
            return;
          }
        }
        
        // ======================================
        // If validation passes, proceed with booking
        // ======================================
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
        formData.append('status', 'en attend');
        formData.append('description', this.bookingData.description || '');
        formData.append('constraints', JSON.stringify(this.constraintsValues));
        
        console.log('Sending formData:', Object.fromEntries(formData.entries()));

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

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>

