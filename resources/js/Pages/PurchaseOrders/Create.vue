<!-- resources/js/Pages/PurchaseOrders/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    vendors: Array,
    projects: Array,
    materials: Array,
});

const form = useForm({
    po_number: '',
    vendor_id: '',
    project_id: '',
    order_date: new Date().toISOString().split('T')[0],
    expected_date: '',
    ship_to_address: '',
    notes: '',
    tax: 0,
    freight: 0,
    lines: [
        { material_id: '', type: '', size: '', grade: '', length: 0, quantity: 1, unit_price: 0 }
    ],
});

const addLine = () => {
    form.lines.push({ material_id: '', type: '', size: '', grade: '', length: 0, quantity: 1, unit_price: 0 });
};

const removeLine = (index) => {
    form.lines.splice(index, 1);
};

const onMaterialChange = (index) => {
    const line = form.lines[index];
    if (line.material_id) {
        const material = props.materials.find(m => m.id === parseInt(line.material_id));
        if (material) {
            line.type = material.type;
            line.size = material.size_imperial || material.size_metric;
            line.grade = material.grade?.code || '';
        }
    }
};

const subtotal = computed(() => {
    return form.lines.reduce((acc, line) => acc + (line.quantity * line.unit_price), 0);
});

const total = computed(() => {
    return subtotal.value + (parseFloat(form.tax) || 0) + (parseFloat(form.freight) || 0);
});

const submit = () => {
    form.post(route('purchase-orders.store'));
};
</script>

<template>
  <AppLayout title="Create Purchase Order">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-white leading-tight">
          Create Purchase Order
        </h2>
        <Link
          :href="route('purchase-orders.index')"
          class="btn-secondary"
        >
          Cancel
        </Link>
      </div>
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <form
        class="space-y-6"
        @submit.prevent="submit"
      >
        <div class="card-industrial">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase">PO Number *</label>
              <input
                v-model="form.po_number"
                type="text"
                class="input-industrial w-full"
                required
              >
              <div
                v-if="form.errors.po_number"
                class="text-forge-500 text-xs"
              >
                {{ form.errors.po_number }}
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase">Vendor *</label>
              <select
                v-model="form.vendor_id"
                class="input-industrial w-full"
                required
              >
                <option value="">
                  Select Vendor
                </option>
                <option
                  v-for="vendor in vendors"
                  :key="vendor.id"
                  :value="vendor.id"
                >
                  {{ vendor.name }}
                </option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase">Project</label>
              <select
                v-model="form.project_id"
                class="input-industrial w-full"
              >
                <option value="">
                  No Project
                </option>
                <option
                  v-for="p in projects"
                  :key="p.id"
                  :value="p.id"
                >
                  {{ p.job_number }} - {{ p.name }}
                </option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase">Order Date *</label>
              <input
                v-model="form.order_date"
                type="date"
                class="input-industrial w-full"
                required
              >
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase">Expected Date</label>
              <input
                v-model="form.expected_date"
                type="date"
                class="input-industrial w-full"
              >
            </div>
          </div>

          <div class="mt-6">
            <label class="block text-xs font-bold text-steel-500 uppercase mb-2">Ship To Address</label>
            <textarea
              v-model="form.ship_to_address"
              class="input-industrial w-full h-20"
            />
          </div>
        </div>

        <div class="card-industrial">
          <h3 class="text-forge-500 font-bold uppercase tracking-widest text-xs mb-4">
            Line Items
          </h3>
          
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-steel-700 text-steel-500 uppercase text-[10px] font-bold">
                  <th class="pb-2 text-left w-32">
                    Material
                  </th>
                  <th class="pb-2 text-left">
                    Type
                  </th>
                  <th class="pb-2 text-left">
                    Size
                  </th>
                  <th class="pb-2 text-left">
                    Grade
                  </th>
                  <th class="pb-2 text-right">
                    Len (ft)
                  </th>
                  <th class="pb-2 text-right">
                    Qty
                  </th>
                  <th class="pb-2 text-right">
                    Unit Price
                  </th>
                  <th class="pb-2 text-right">
                    Total
                  </th>
                  <th class="pb-2" />
                </tr>
              </thead>
              <tbody class="divide-y divide-steel-800">
                <tr
                  v-for="(line, index) in form.lines"
                  :key="index"
                  class="py-2"
                >
                  <td class="py-2 pr-2">
                    <select
                      v-model="line.material_id"
                      class="input-industrial-sm w-full"
                      @change="onMaterialChange(index)"
                    >
                      <option value="">
                        - Custom -
                      </option>
                      <option
                        v-for="m in materials"
                        :key="m.id"
                        :value="m.id"
                      >
                        {{ m.type }} {{ m.size_imperial }}
                      </option>
                    </select>
                  </td>
                  <td class="py-2 pr-2">
                    <input
                      v-model="line.type"
                      type="text"
                      class="input-industrial-sm w-full"
                      placeholder="Type"
                      required
                    >
                  </td>
                  <td class="py-2 pr-2">
                    <input
                      v-model="line.size"
                      type="text"
                      class="input-industrial-sm w-full"
                      placeholder="Size"
                      required
                    >
                  </td>
                  <td class="py-2 pr-2">
                    <input
                      v-model="line.grade"
                      type="text"
                      class="input-industrial-sm w-full"
                      placeholder="Grade"
                      required
                    >
                  </td>
                  <td class="py-2 pr-2">
                    <input
                      v-model="line.length"
                      type="number"
                      step="0.001"
                      class="input-industrial-sm w-full text-right"
                      required
                    >
                  </td>
                  <td class="py-2 pr-2">
                    <input
                      v-model="line.quantity"
                      type="number"
                      step="0.001"
                      class="input-industrial-sm w-full text-right"
                      required
                    >
                  </td>
                  <td class="py-2 pr-2">
                    <input
                      v-model="line.unit_price"
                      type="number"
                      step="0.01"
                      class="input-industrial-sm w-full text-right"
                      required
                    >
                  </td>
                  <td class="py-2 text-right font-mono font-bold">
                    {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(line.quantity * line.unit_price) }}
                  </td>
                  <td class="py-2 text-center">
                    <button
                      type="button"
                      class="text-forge-500 hover:text-forge-400"
                      @click="removeLine(index)"
                    >
                      <svg
                        class="w-4 h-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      ><path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                        clip-rule="evenodd"
                      /></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <button
            type="button"
            class="mt-4 text-xs font-bold text-weld-400 uppercase hover:text-weld-300"
            @click="addLine"
          >
            + Add Line Item
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="card-industrial">
            <label class="block text-xs font-bold text-steel-500 uppercase mb-2">Notes</label>
            <textarea
              v-model="form.notes"
              class="input-industrial w-full h-24"
            />
          </div>

          <div class="card-industrial space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-steel-400">Subtotal</span>
              <span class="text-white font-mono">{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(subtotal) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <label class="text-steel-400">Tax</label>
              <input
                v-model="form.tax"
                type="number"
                step="0.01"
                class="input-industrial-sm w-32 text-right"
              >
            </div>
            <div class="flex justify-between items-center">
              <label class="text-steel-400">Freight</label>
              <input
                v-model="form.freight"
                type="number"
                step="0.01"
                class="input-industrial-sm w-32 text-right"
              >
            </div>
            <div class="border-t border-steel-700 pt-4 flex justify-between items-center">
              <span class="text-lg font-bold text-white uppercase tracking-widest">Total</span>
              <span class="text-2xl font-bold text-forge-400 font-mono">{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(total) }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-6">
          <button
            type="submit"
            class="btn-forge px-12 py-3 text-lg"
            :disabled="form.processing"
          >
            Create Purchase Order
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
