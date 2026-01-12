<!-- resources/js/Pages/PurchaseOrders/Show.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    purchaseOrder: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
</script>

<template>
  <AppLayout :title="`PO: ${purchaseOrder.po_number}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            :href="route('purchase-orders.index')"
            class="text-steel-400 hover:text-white mr-4"
          >
            &larr; Back
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-white leading-tight">
              Purchase Order: {{ purchaseOrder.po_number }}
            </h2>
            <p class="text-sm text-steel-400">
              Vendor: {{ purchaseOrder.vendor.name }}
            </p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            :href="route('purchase-orders.receive', purchaseOrder.id)"
            class="btn-forge"
          >
            Receive Materials
          </Link>
          <Link
            :href="route('purchase-orders.edit', purchaseOrder.id)"
            class="btn-secondary"
          >
            Edit PO
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card-industrial">
          <dt class="text-xs font-bold text-steel-500 uppercase">
            Vendor
          </dt>
          <dd class="text-white font-semibold">
            {{ purchaseOrder.vendor.name }}
          </dd>
          
          <dt class="text-xs font-bold text-steel-500 uppercase mt-4">
            Order Date
          </dt>
          <dd class="text-white font-mono">
            {{ formatDate(purchaseOrder.order_date) }}
          </dd>

          <dt class="text-xs font-bold text-steel-500 uppercase mt-4">
            Expected Date
          </dt>
          <dd class="text-white font-mono">
            {{ formatDate(purchaseOrder.expected_date) }}
          </dd>
        </div>

        <div class="card-industrial">
          <dt class="text-xs font-bold text-steel-500 uppercase">
            Project
          </dt>
          <dd class="text-white font-semibold">
            {{ purchaseOrder.project?.job_number || 'Stock Order' }}
          </dd>
          <dd class="text-xs text-steel-400">
            {{ purchaseOrder.project?.name }}
          </dd>
          
          <dt class="text-xs font-bold text-steel-500 uppercase mt-4">
            Status
          </dt>
          <dd class="mt-1">
            <span
              class="badge uppercase text-xs"
              :class="{
                'bg-steel-700': purchaseOrder.status === 'draft',
                'bg-blue-900': purchaseOrder.status === 'sent',
                'bg-green-900': purchaseOrder.status === 'received',
              }"
            >
              {{ purchaseOrder.status }}
            </span>
          </dd>
        </div>

        <div class="card-industrial">
          <dt class="text-xs font-bold text-steel-500 uppercase text-right">
            Totals
          </dt>
          <div class="flex justify-between mt-2">
            <span class="text-steel-400 text-sm">Subtotal:</span>
            <span class="text-white font-mono">{{ formatCurrency(purchaseOrder.subtotal) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-steel-400 text-sm">Tax:</span>
            <span class="text-white font-mono">{{ formatCurrency(purchaseOrder.tax) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-steel-400 text-sm">Freight:</span>
            <span class="text-white font-mono">{{ formatCurrency(purchaseOrder.freight) }}</span>
          </div>
          <div class="border-t border-steel-700 mt-2 pt-2 flex justify-between">
            <span class="text-white font-bold">TOTAL:</span>
            <span class="text-forge-400 font-bold font-mono">{{ formatCurrency(purchaseOrder.total) }}</span>
          </div>
        </div>
      </div>

      <div class="card-industrial">
        <h3 class="text-sm font-bold text-white uppercase tracking-widest mb-4">
          Line Items
        </h3>
        <div class="overflow-x-auto">
          <table class="table-industrial">
            <thead>
              <tr>
                <th class="text-left font-bold">
                  Item
                </th>
                <th class="text-left font-bold">
                  Grade
                </th>
                <th class="text-right font-bold tracking-widest">
                  Ordered
                </th>
                <th class="text-right font-bold tracking-widest">
                  Received
                </th>
                <th class="text-right font-bold tracking-widest">
                  Unit Price
                </th>
                <th class="text-right font-bold tracking-widest">
                  Extended
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="line in purchaseOrder.lines"
                :key="line.id"
              >
                <td class="font-mono">
                  <div class="text-white font-bold">
                    {{ line.type }} {{ line.size }}
                  </div>
                  <div class="text-[10px] text-steel-500">
                    Length: {{ line.length }}'
                  </div>
                </td>
                <td class="text-steel-400 font-mono text-xs">
                  {{ line.grade }}
                </td>
                <td class="text-right font-mono">
                  {{ line.quantity }}
                </td>
                <td
                  class="text-right font-mono"
                  :class="line.quantity_received >= line.quantity ? 'text-green-400' : 'text-forge-400'"
                >
                  {{ line.quantity_received }}
                </td>
                <td class="text-right font-mono">
                  {{ formatCurrency(line.unit_price) }}
                </td>
                <td class="text-right font-mono text-white font-bold">
                  {{ formatCurrency(line.extended_price) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
