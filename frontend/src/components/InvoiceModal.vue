<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-sm">
    <div class="bg-white rounded shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[95vh]">
      
      <!-- Toolbar -->
      <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-gray-50 no-print">
        <div></div>
        <div class="flex gap-2">
          <button 
            @click="downloadPDF"
            :disabled="isDownloading"
            class="flex items-center gap-2 px-4 py-2 bg-[#305C7D] text-white rounded hover:bg-[#1A5FA3] transition-colors disabled:opacity-50"
          >
            <Download :size="16" v-if="!isDownloading" />
            <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ isDownloading ? 'Génération...' : 'Télécharger PDF' }}
          </button>
          <button 
            @click="$emit('close')"
            class="p-2 text-gray-500 hover:bg-gray-200 rounded transition-colors"
          >
            <X :size="20" />
          </button>
        </div>
      </div>

      <!-- Invoice Content - Printable Area -->
      <div id="invoice-content" class="p-12 overflow-y-auto flex-1 bg-white relative">
        <!-- Title -->
        <div class="text-center mb-6">
          <h1 class="text-3xl font-bold text-[#305C7D] mb-4 uppercase tracking-wide">FACTURE</h1>
          <p class="text-sm text-gray-600">Référence : {{ invoiceReference }} | Date : {{ formatDate(invoice.date) }}</p>
        </div>

        <!-- Separator -->
        <div class="h-0.5 bg-[#305C7D] w-3/4 mx-auto mb-12 opacity-80"></div>

        <!-- Parties -->
        <div class="flex justify-between mb-12">
          <!-- Intervenant -->
          <div>
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">INTERVENANT</h3>
            <div class="font-bold text-gray-800 text-lg mb-1">{{ demand.intervenant.name }}</div>
            <div class="text-sm text-gray-600">{{ demand.intervenant.email || 'contact@verdenet.com' }}</div>
          </div>
          
          <!-- Client -->
          <div class="text-right">
             <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">CLIENT</h3>
             <div class="font-bold text-gray-800 text-lg mb-1">{{ clientName }}</div>
             <div class="text-sm text-gray-600">{{ paymentCity }}</div>
          </div>
        </div>

        <!-- Intervention Details -->
        <div class="mb-10">
          <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 pl-1">DÉTAILS DE L'INTERVENTION</h3>
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-50 border border-gray-200">
                <th class="py-3 px-4 text-left font-bold text-gray-700 text-sm border-r border-gray-200 w-1/4">Service</th>
                <th class="py-3 px-4 text-left font-bold text-gray-700 text-sm border-r border-gray-200 w-1/3">Tâche</th>
                <th class="py-3 px-4 text-left font-bold text-gray-700 text-sm border-r border-gray-200">Date</th>
                <th class="py-3 px-4 text-left font-bold text-gray-700 text-sm">Durée</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border border-gray-200">
                 <td class="py-3 px-4 text-sm text-gray-700 border-r border-gray-200">{{ demand.service }}</td>
                 <td class="py-3 px-4 text-sm text-gray-700 border-r border-gray-200">{{ demand.task }}</td>
                 <td class="py-3 px-4 text-sm text-gray-700 border-r border-gray-200">{{ formatDate(demand.date) }}</td>
                 <td class="py-3 px-4 text-sm text-gray-700">{{ formattedDuration }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Costs Details -->
        <div class="mb-12">
           <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 pl-1">DÉTAIL DES COÛTS (HT)</h3>
           <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-50 border border-gray-200">
                <th class="py-3 px-4 text-left font-bold text-gray-700 text-sm">Description</th>
                <th class="py-3 px-4 text-right font-bold text-gray-700 text-sm w-1/4">Montant HT</th>
              </tr>
            </thead>
             <tbody>
                <tr class="border-b border-gray-200">
                   <td class="py-3 px-4 text-sm text-gray-700">Main d'œuvre ({{ formattedDuration }} x {{ hourlyRate }} MAD/h)</td>
                   <td class="py-3 px-4 text-right text-sm text-gray-700">{{ formatPrice(invoice.laborCost) }} MAD</td>
                </tr>
                <tr v-if="invoice.materialsCost > 0" class="border-b border-gray-200">
                   <td class="py-3 px-4 text-sm text-gray-700">Matériel : {{ materialsDescription }}</td>
                   <td class="py-3 px-4 text-right text-sm text-gray-700">{{ formatPrice(invoice.materialsCost) }} MAD</td>
                </tr>
                 <!-- Ligne vide pour espacement ou autres frais si nécessaire -->
                <tr class="border-b border-gray-200" v-if="!invoice.materialsCost">
                   <td class="py-3 px-4 text-sm text-gray-700">Matériel</td>
                   <td class="py-3 px-4 text-right text-sm text-gray-700">0.00 MAD</td>
                </tr>
             </tbody>
           </table>
        </div>

        <!-- Footer Totals -->
        <div class="flex justify-end mb-16">
           <div class="w-1/2">
             <table class="w-full border-collapse">
               <tbody>
                 <tr class="border border-gray-200">
                    <td class="py-3 px-4 text-right text-lg font-bold text-gray-800 bg-gray-50 border-r border-gray-200 align-middle">
                      Total à<br>Percevoir
                    </td>
                    <td class="py-3 px-4 text-right text-xl font-bold text-[#305C7D] align-middle">{{ formatPrice(totalNet) }} MAD</td>
                 </tr>
               </tbody>
             </table>
           </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center text-[10px] text-gray-400 mt-auto pt-8">
           Cette fiche de payement est générée automatiquement par la plateforme Verde Net.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Download, X } from 'lucide-vue-next';
import authService from '@/services/authService';

export default {
  name: 'InvoiceModal',
  components: {
    Download,
    X
  },
  props: {
    invoice: { type: Object, required: true },
    demand: { type: Object, required: true }
  },
  emits: ['close'],
  data() {
    return {
      isDownloading: false
    };
  },
  computed: {
    clientName() {
      const user = authService.getUserSync();
      if (!user) return 'Client';
      return `${user.prenom || ''} ${user.nom || ''}`.trim() || user.name || 'Client';
    },
    paymentCity() {
      // Assuming dynamic city or default
      return this.demand.quartier || 'Tétouan';
    },
    invoiceReference() {
       // Use num_facture from backend if available, otherwise generate one
       if (this.invoice.num_facture) {
         return `FACT-${this.invoice.num_facture}`;
       }
       const datePart = new Date(this.invoice.date).toISOString().slice(0,10).replace(/-/g, '');
       return `PAY-${this.demand.id}-${datePart}0059`;
    },
    formattedDuration() {
      const duration = this.invoice.actualDuration || 'N/A';
      // If it's already formatted (e.g., "2.5 heures" from backend), return as is
      if (typeof duration === 'string' && duration.includes('heures')) {
        return duration;
      }
      if (typeof duration === 'number') {
        return `${duration.toFixed(1)} heures`;
      }
      // If it's a string like "2.5 heures", return as is
      return duration;
    },
    hourlyRate() {
       // Calculate hourly rate: laborCost / duration
       let hours = 1;
       const duration = this.invoice.actualDuration || '1 heures';
       
       // Parse duration string like "2.5 heures" or "2 heures"
       if (typeof duration === 'string') {
         const match = duration.match(/([\d.]+)/);
         if (match) {
           hours = parseFloat(match[1]);
         }
       } else if (typeof duration === 'number') {
         hours = duration;
       }
       
       if (hours === 0) hours = 1;
       
       return (this.invoice.laborCost / hours).toFixed(2);
    },
    materialsDescription() {
        // Use materialsDescription from backend if available, otherwise use materialsProvided array
        if (this.invoice.materialsDescription) {
          return this.invoice.materialsDescription;
        }
        if (this.invoice.materialsProvided && this.invoice.materialsProvided.length > 0) {
          return this.invoice.materialsProvided.join(', ');
        }
        return 'Aucun matériel';
    },
    totalBrut() {
      return (Number(this.invoice.laborCost) || 0) + (Number(this.invoice.materialsCost) || 0);
    },
   
    totalNet() {
      return this.totalBrut;
    }
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    },
    formatPrice(price) {
      return (Number(price) || 0).toFixed(2);
    },
    async downloadPDF() {
      this.isDownloading = true;

      // Dynamically load html2pdf if not present
      if (!window.html2pdf) {
        await new Promise((resolve, reject) => {
          const script = document.createElement('script');
          script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
          script.onload = resolve;
          script.onerror = reject;
          document.head.appendChild(script);
        });
      }

      const element = document.getElementById('invoice-content');
      const opt = {
        margin: [10, 10, 10, 10], // top, left, bottom, right
        filename: `fiche_payement_${this.invoiceReference}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      try {
        await window.html2pdf().set(opt).from(element).save();
      } catch (error) {
        console.error('PDF Generation Error:', error);
        alert('Erreur lors de la génération du PDF.');
      } finally {
        this.isDownloading = false;
      }
    }
  }
};
</script>

<style scoped>
/* Ensure the modal content mimics an A4 page standard width relative to viewport */
#invoice-content {
  font-family: 'Inter', sans-serif;
}
</style>
