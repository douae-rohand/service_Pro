<template>
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex items-start justify-between mb-8">
      <div class="flex items-start gap-6">
        <div class="relative">
          <img
            :src="profileImage"
            :alt="intervenant.name"
            class="w-24 h-24 rounded-full object-cover"
          />
          <!-- Future: Add image upload overlay here -->
        </div>
        
        <div class="flex-1">
          <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ intervenant.name }}</h2>
          
          <div v-if="!isEditing">
            <div class="flex flex-col gap-1 mb-3">
              <div class="flex items-center gap-2 text-gray-600">
                <MapPinIcon :size="16" />
                <span class="font-medium">Ville:</span>
                <span>{{ intervenant.location || 'Non renseignée' }}</span>
              </div>
            </div>
            <p class="text-gray-600 max-w-2xl">{{ intervenant.bio || 'Aucune biographie renseignée.' }}</p>
          </div>

          <div v-else class="space-y-4 max-w-lg mt-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ville / Localisation</label>
              <input 
                v-model="editForm.ville"
                type="text" 
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Biographie</label>
              <textarea 
                v-model="editForm.bio"
                rows="3"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <button 
        @click="toggleEdit"
        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors"
        :class="isEditing ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
      >
        <component :is="isEditing ? 'SaveIcon' : 'EditIcon'" :size="18" />
        <span>{{ isEditing ? 'Enregistrer' : 'Modifier' }}</span>
      </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-gray-50 p-4 rounded-xl">
        <div class="text-sm text-gray-500 mb-1">Note moyenne</div>
        <div class="flex items-center gap-2">
          <StarIcon class="text-yellow-400 fill-current" :size="20" />
          <span class="text-xl font-bold">{{ intervenantRating }}</span>
          <span class="text-sm text-gray-400">({{ intervenantReviewCount }} avis)</span>
        </div>
      </div>
      
      <div class="bg-gray-50 p-4 rounded-xl">
        <div class="text-sm text-gray-500 mb-1">Membre depuis</div>
        <div class="flex items-center gap-2">
          <CalendarIcon class="text-blue-500" :size="20" />
          <span class="text-lg font-medium">{{ intervenant.memberSince }}</span>
        </div>
      </div>
    </div>

    <!-- Experience per Service -->
    <div class="border-t pt-8">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Mes Services & Expérience</h3>
      
      <div v-if="intervenant.services && intervenant.services.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="(service, index) in (isEditing ? editForm.services : intervenant.services)" 
          :key="service.id"
          class="p-4 border rounded-xl hover:border-green-200 transition-colors bg-white group"
        >
          <div class="flex items-start justify-between">
            <div>
              <h4 class="font-semibold text-gray-900">{{ service.nom_service }}</h4>
              <p class="text-sm text-gray-500 mt-1">{{ service.description }}</p>
            </div>
            
            <div v-if="!isEditing" class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
              {{ formatExperience(service.pivot?.experience) }}
            </div>
            <div v-else class="flex items-center gap-2">
                <input 
                    v-model="editForm.services[index].experience"
                    type="number" 
                    step="0.5" 
                    min="0"
                    class="w-20 px-2 py-1 border rounded text-right focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
                <span class="text-sm text-gray-600">an(s)</span>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-8 text-gray-500 bg-gray-50 rounded-xl">
        Aucun service configuré pour le moment.
      </div>
    </div>
  </div>
</template>

<script>
import { 
  MapPin as MapPinIcon, 
  Star as StarIcon, 
  Edit2 as EditIcon, 
  Save as SaveIcon,
  Calendar as CalendarIcon
} from 'lucide-vue-next';
import api from '../../services/api'; 

export default {
  name: 'IntervenantProfileTab',
  components: { MapPinIcon, StarIcon, EditIcon, SaveIcon, CalendarIcon },
  props: {
    intervenant: { type: Object, required: true }
  },
  mounted() {
    console.log("IntervenantProfileTab mounted. Services:", this.intervenant.services);
  },
  data() {
    return {
      isEditing: false,
      editForm: {
        address: '',
        ville: '',
        bio: '',
        services: []
      }
    };
  },
  computed: {
    profileImage() { return this.intervenant.profileImage || 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop'; },
    intervenantRating() { return this.intervenant.average_rating || this.intervenant.rating || 0; },
    intervenantReviewCount() { return this.intervenant.review_count || this.intervenant.reviewCount || 0; }
  },
  methods: {
    formatExperience(years) {
      if (!years) return 'Débutant';
      const num = parseFloat(years);
      if (num < 1) return '< 1 an';
      return `${num} an${num > 1 ? 's' : ''}`;
    },
    toggleEdit() {
      if (this.isEditing) {
        this.saveProfile();
      } else {
        // Initialize form with deep copy of services to avoid mutating prop directly during edit
        this.editForm = {
          address: this.intervenant.address,
          ville: this.intervenant.ville,
          bio: this.intervenant.bio,
          services: this.intervenant.services.map(s => ({
              id: s.id,
              nom_service: s.nom_service,
              description: s.description,
              experience: s.pivot?.experience || 0
          }))
        };
        this.isEditing = true;
      }
    },
    async saveProfile() {
        try {
            // Prepare services payload
            const servicesPayload = this.editForm.services.map(s => ({
                id: s.id,
                experience: s.experience
            }));

            await api.put(`intervenants/${this.intervenant.id}`, {
                address: this.editForm.address,
                ville: this.editForm.ville,
                bio: this.editForm.bio,
                services: servicesPayload
            });
            
            // Update local object
            this.intervenant.address = this.editForm.address;
            this.intervenant.ville = this.editForm.ville;
            this.intervenant.location = this.editForm.ville || this.editForm.address; // Update display fallback
            this.intervenant.bio = this.editForm.bio;
            
            // Update local services experience (matching by ID)
            this.intervenant.services.forEach(s => {
                const updated = this.editForm.services.find(us => us.id === s.id);
                if (updated) {
                    if (!s.pivot) s.pivot = {};
                    s.pivot.experience = updated.experience;
                }
            });
            
            this.isEditing = false;
        } catch (error) {
            console.error('Error saving profile:', error);
            alert('Erreur lors de la sauvegarde du profil.');
        }
    }
  }
};
</script>
