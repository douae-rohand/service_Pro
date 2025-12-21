<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-sm print:bg-white print:p-0 print:absolute print:inset-0 print:block">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh] print:shadow-none print:max-h-none print:max-w-none print:h-auto print:rounded-none">
      
      <!-- Toolbar (Hidden when printing) -->
      <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-gray-50 print:hidden">
        <h2 class="font-bold text-gray-800 flex items-center gap-2">
          <FileText :size="20" class="text-[#305C7D]" />
          Facture #{{ invoiceNumber }}
        </h2>
        <div class="flex gap-2">
          <button 
            @click="printInvoice"
            class="flex items-center gap-2 px-4 py-2 bg-[#305C7D] text-white rounded-lg text-sm font-medium hover:bg-[#204058] transition-colors"
          >
            <Printer :size="16" />
            Imprimer
          </button>
          <button 
            @click="$emit('close')"
            class="p-2 text-gray-500 hover:bg-gray-200 rounded-lg transition-colors"
          >
            <X :size="20" />
          </button>
        </div>
      </div>

      <!-- Invoice Content -->
      <div class="p-8 overflow-y-auto flex-1 print:p-0 print:overflow-visible">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8 print:mb-4">
          <div>
            <div class="text-2xl font-bold text-[#305C7D] mb-1 print:text-black print:text-xl">ServicePro</div>
            <div class="text-sm text-gray-500 print:text-xs">
              Plateforme de services à domicile<br>
              Tétouan, Maroc<br>
              contact@servicepro.com
            </div>
          </div>
          <div class="text-right">
            <h1 class="text-3xl font-bold text-gray-900 mb-1 print:text-xl">FACTURE</h1>
            <div class="text-sm text-gray-500 print:text-xs">Date: {{ formatDate(invoice.date) }}</div>
            <div class="text-sm text-gray-500 print:text-xs">N°: {{ invoiceNumber }}</div>
          </div>
        </div>

        <!-- Client & Provider -->
        <div class="flex justify-between mb-8 pb-8 border-b border-gray-100 print:mb-4 print:pb-4 print:border-gray-200">
          <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 print:text-black">Facturé à</h3>
            <div class="font-bold text-gray-800">{{ clientName }}</div>
            <div class="text-sm text-gray-600 max-w-[200px]">{{ demand.address }}</div>
            <div class="text-sm text-gray-600">{{ demand.quartier }}</div>
          </div>
          <div class="text-right">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 print:text-black">Prestataire</h3>
            <div class="font-bold text-gray-800">{{ demand.intervenant.name }}</div>
            <div class="text-sm text-gray-600">{{ demand.service }}</div>
          </div>
        </div>

        <!-- Items Table -->
        <table class="w-full mb-8">
          <thead>
            <tr class="text-left border-b-2 border-gray-100">
              <th class="py-3 font-bold text-gray-600">Description</th>
              <th class="py-3 font-bold text-gray-600 text-right">Montant</th>
            </tr>
          </thead>
          <tbody class="text-gray-700">
            <tr class="border-b border-gray-50">
              <td class="py-4">
                <div class="font-medium">Service de {{ demand.service }}</div>
                <div class="text-sm text-gray-500">{{ demand.task }} - Durée: {{ invoice.actualDuration || 'Standard' }}</div>
              </td>
              <td class="py-4 text-right">{{ formatPrice(invoice.laborCost) }} DH</td>
            </tr>
            <tr v-if="invoice.materialsCost > 0" class="border-b border-gray-50">
              <td class="py-4">
                <div class="font-medium">Frais de matériel</div>
                <div class="text-sm text-gray-500">Fourni par l'intervenant</div>
              </td>
              <td class="py-4 text-right">{{ formatPrice(invoice.materialsCost) }} DH</td>
            </tr>
          </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-12">
          <div class="w-64 space-y-3">
            <div class="flex justify-between text-gray-600">
              <span>Sous-total</span>
              <span>{{ formatPrice(totalAmount) }} DH</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>TVA (0%)</span>
              <span>0.00 DH</span>
            </div>
            <div class="flex justify-between font-bold text-xl text-[#305C7D] pt-3 border-t-2 border-gray-100">
              <span>Total</span>
              <span>{{ formatPrice(totalAmount) }} DH</span>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-400 mt-12 pt-8 border-t border-gray-100 print:hidden">
          <p>Merci de votre confiance !</p>
          <p class="mt-1">Cette facture a été générée automatiquement par ServicePro.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { FileText, Printer, X } from 'lucide-vue-next';
import authService from '@/services/authService';

export default {
  name: 'InvoiceModal',
  components: {
    FileText,
    Printer,
    X
  },
  props: {
    invoice: {
      type: Object,
      required: true
    },
    demand: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  computed: {
    clientName() {
      const user = authService.getUserSync();
      if (!user) return 'Client';
      return `${user.prenom || ''} ${user.nom || ''}`.trim() || user.name || user.email;
    },
    invoiceNumber() {
      const datePart = new Date(this.invoice.date).toISOString().slice(0,10).replace(/-/g, '');
      return `INV-${datePart}-${this.demand.id}`;
    },
    totalAmount() {
      return (Number(this.invoice.laborCost) || 0) + (Number(this.invoice.materialsCost) || 0);
    }
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });
    },
    formatPrice(price) {
      const num = Number(price) || 0;
      return num.toFixed(2);
    },
    printInvoice() {
      window.print();
    }
  }
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  .fixed, .fixed * {
    visibility: visible;
  }
  .fixed {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 20px !important;
    background: white !important;
  }
  .print\:hidden {
    display: none !important;
  }
}
</style>
