<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { BanknotesIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    invoice: Object,
});

const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'check',
    reference_number: '',
    notes: '',
});

const formatCurrency = (amount) => {
    if (!amount) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'draft': 'bg-gray-500',
        'sent': 'bg-blue-500',
        'partial': 'bg-yellow-500',
        'paid': 'bg-green-500',
        'cancelled': 'bg-red-500',
    };
    return badges[status] || 'bg-gray-500';
};

const updateStatus = (newStatus) => {
    if (confirm(`Update invoice status to ${newStatus}?`)) {
        router.patch(route('finance.invoices.update-status', props.invoice.id), {
            status: newStatus,
        });
    }
};

const recordPayment = () => {
    paymentForm.post(route('finance.invoices.record-payment', props.invoice.id), {
        onSuccess: () => {
            paymentForm.reset();
            showPaymentModal.value = false;
        },
    });
};

const breadcrumbItems = [
    { label: 'Finance' },
    { label: 'Invoices', href: route('finance.invoices.index') },
    { label: props.invoice.invoice_number },
];
</script>

<template>
  <AppLayout :title="`Invoice: ${invoice.invoice_number}`">
    <Breadcrumb :items="breadcrumbItems" />

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Invoice {{ invoice.invoice_number }}
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          {{ invoice.project?.job_number }} - {{ invoice.customer?.name }}
        </p>
      </div>
      <div class="mt-4 md:mt-0 flex gap-3">
        <span
          class="px-4 py-2 rounded-full text-xs uppercase font-bold text-white"
          :class="getStatusBadge(invoice.status)"
        >
          {{ invoice.status }}
        </span>
        <button
          v-if="invoice.status !== 'paid'"
          class="btn-primary flex items-center gap-2"
          @click="showPaymentModal = true"
        >
          <BanknotesIcon class="h-5 w-5" />
          Record Payment
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Invoice Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Invoice Info -->
        <div class="card-glow">
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              Invoice Details
            </h3>
            <div class="grid grid-cols-2 gap-6">
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Invoice Date
                </div>
                <div class="text-white font-mono">
                  {{ formatDate(invoice.invoice_date) }}
                </div>
              </div>
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Due Date
                </div>
                <div class="text-white font-mono">
                  {{ formatDate(invoice.due_date) }}
                </div>
              </div>
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Project
                </div>
                <div class="text-white">
                  {{ invoice.project?.job_number }} - {{ invoice.project?.name }}
                </div>
              </div>
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Customer
                </div>
                <div class="text-white">
                  {{ invoice.customer?.name }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="card-glow">
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              Line Items
            </h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-steel-700">
                <thead>
                  <tr class="text-left text-xs uppercase tracking-wider text-steel-400">
                    <th class="pb-3 font-mono">
                      Description
                    </th>
                    <th class="pb-3 font-mono text-right">
                      Quantity
                    </th>
                    <th class="pb-3 font-mono text-right">
                      Unit Price
                    </th>
                    <th class="pb-3 font-mono text-right">
                      Total
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-steel-800">
                  <tr
                    v-for="line in invoice.line_items"
                    :key="line.id"
                  >
                    <td class="py-3 text-steel-300">
                      {{ line.description }}
                      <div
                        v-if="line.load"
                        class="text-xs text-steel-500 mt-1"
                      >
                        Load: {{ line.load.load_number }}
                      </div>
                    </td>
                    <td class="py-3 text-right text-white font-mono">
                      {{ line.quantity }}
                    </td>
                    <td class="py-3 text-right text-white font-mono">
                      {{ formatCurrency(line.unit_price) }}
                    </td>
                    <td class="py-3 text-right text-white font-mono">
                      {{ formatCurrency(line.line_total) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="border-t-2 border-steel-700">
                  <tr>
                    <td
                      colspan="3"
                      class="py-3 text-right text-steel-400 uppercase text-sm font-bold font-mono"
                    >
                      Subtotal
                    </td>
                    <td class="py-3 text-right text-white font-mono font-bold">
                      {{ formatCurrency(invoice.subtotal_amount) }}
                    </td>
                  </tr>
                  <tr v-if="invoice.tax_amount">
                    <td
                      colspan="3"
                      class="py-3 text-right text-steel-400 uppercase text-sm font-bold font-mono"
                    >
                      Tax
                    </td>
                    <td class="py-3 text-right text-white font-mono font-bold">
                      {{ formatCurrency(invoice.tax_amount) }}
                    </td>
                  </tr>
                  <tr class="text-lg">
                    <td
                      colspan="3"
                      class="py-3 text-right text-forge-400 uppercase font-bold font-mono"
                    >
                      Total
                    </td>
                    <td class="py-3 text-right text-forge-400 font-mono font-bold">
                      {{ formatCurrency(invoice.total_amount) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="lg:col-span-1 space-y-6">
        <!-- Payment Summary -->
        <div class="card-glow">
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              Payment Summary
            </h3>
            <div class="space-y-4">
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Invoice Total
                </div>
                <div class="text-2xl font-bold text-white font-mono">
                  {{ formatCurrency(invoice.total_amount) }}
                </div>
              </div>
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Amount Paid
                </div>
                <div class="text-2xl font-bold text-green-400 font-mono">
                  {{ formatCurrency(invoice.paid_amount) }}
                </div>
              </div>
              <div class="pt-4 border-t border-steel-700">
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Balance Due
                </div>
                <div class="text-3xl font-bold text-forge-400 font-mono">
                  {{ formatCurrency(invoice.total_amount - invoice.paid_amount) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment History -->
        <div
          v-if="invoice.payments && invoice.payments.length"
          class="card-glow"
        >
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              Payment History
            </h3>
            <div class="space-y-3">
              <div
                v-for="payment in invoice.payments"
                :key="payment.id"
                class="pb-3 border-b border-steel-800 last:border-0"
              >
                <div class="flex justify-between items-start mb-1">
                  <div class="text-white font-mono">
                    {{ formatCurrency(payment.amount) }}
                  </div>
                  <div class="text-xs text-steel-400 font-mono">
                    {{ formatDate(payment.payment_date) }}
                  </div>
                </div>
                <div class="text-xs text-steel-500">
                  {{ payment.payment_method }}
                  <span v-if="payment.reference_number"> - {{ payment.reference_number }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="card-glow">
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              Actions
            </h3>
            <div class="space-y-2">
              <button
                v-if="invoice.status === 'draft'"
                class="btn-secondary w-full"
                @click="updateStatus('sent')"
              >
                Mark as Sent
              </button>
              <button class="btn-secondary w-full">
                Download PDF
              </button>
              <button class="btn-secondary w-full">
                Email Invoice
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
      @click.self="showPaymentModal = false"
    >
      <div class="card-industrial max-w-2xl w-full mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-white">
            Record Payment
          </h3>
          <button
            class="text-steel-400 hover:text-white"
            @click="showPaymentModal = false"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <form @submit.prevent="recordPayment">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-bold text-steel-400 mb-2">
                Amount
              </label>
              <input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                min="0.01"
                class="input w-full"
                required
              >
            </div>

            <div>
              <label class="block text-sm font-bold text-steel-400 mb-2">
                Payment Date
              </label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                class="input w-full"
                required
              >
            </div>

            <div>
              <label class="block text-sm font-bold text-steel-400 mb-2">
                Payment Method
              </label>
              <select
                v-model="paymentForm.payment_method"
                class="input w-full"
                required
              >
                <option value="check">
                  Check
                </option>
                <option value="wire">
                  Wire Transfer
                </option>
                <option value="ach">
                  ACH
                </option>
                <option value="credit_card">
                  Credit Card
                </option>
                <option value="cash">
                  Cash
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-bold text-steel-400 mb-2">
                Reference Number
              </label>
              <input
                v-model="paymentForm.reference_number"
                type="text"
                class="input w-full"
                placeholder="Check #, Transaction ID, etc."
              >
            </div>

            <div>
              <label class="block text-sm font-bold text-steel-400 mb-2">
                Notes
              </label>
              <textarea
                v-model="paymentForm.notes"
                class="input w-full"
                rows="3"
              />
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button
              type="button"
              class="btn-secondary"
              @click="showPaymentModal = false"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-primary"
              :disabled="paymentForm.processing"
            >
              Record Payment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
