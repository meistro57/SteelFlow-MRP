<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    partInstances: Array,
    productionItems: Array,
    stockItems: Array,
    selectedType: String,
});

const form = useForm({
    type: props.selectedType || 'part',
    part_instance_id: '',
    production_item_id: '',
    stock_item_id: '',
    failure_reason: '',
    remediation_notes: '',
});

// Watch for type change to fetch relevant items
watch(() => form.type, (newType) => {
    router.get(route('ncr.create'), { type: newType }, {
        preserveState: true,
        preserveScroll: true,
        only: ['partInstances', 'productionItems', 'stockItems', 'selectedType']
    });
    
    // Reset selected IDs
    form.part_instance_id = '';
    form.production_item_id = '';
    form.stock_item_id = '';
});

const submit = () => {
    form.post(route('ncr.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
  <AppLayout title="Report NCR">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight text-glow-forge uppercase tracking-wide">
        Non-Conformance Report Entry
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-t-4 border-red-500">
          <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-700">
              New Quality Incident Report
            </h3>
            <p class="text-sm text-gray-500">
              Document the failure details and affected items.
            </p>
          </div>

          <form
            class="p-6"
            @submit.prevent="submit"
          >
            <div class="grid grid-cols-1 gap-6">
              <!-- Type Selection -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">NCR Category</label>
                <div class="grid grid-cols-3 gap-2">
                  <button 
                    type="button"
                    :class="['px-4 py-2 text-sm font-semibold border rounded-md transition-colors', 
                             form.type === 'part' ? 'bg-red-600 text-white border-red-600 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100']"
                    @click="form.type = 'part'"
                  >
                    Part Instance
                  </button>
                  <button 
                    type="button"
                    :class="['px-4 py-2 text-sm font-semibold border rounded-md transition-colors', 
                             form.type === 'production' ? 'bg-red-600 text-white border-red-600 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100']"
                    @click="form.type = 'production'"
                  >
                    Production
                  </button>
                  <button 
                    type="button"
                    :class="['px-4 py-2 text-sm font-semibold border rounded-md transition-colors', 
                             form.type === 'material' ? 'bg-red-600 text-white border-red-600 shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100']"
                    @click="form.type = 'material'"
                  >
                    Material
                  </button>
                </div>
              </div>

              <!-- Item Selection based on Type -->
              <div v-if="form.type === 'part'">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Select Affected Part</label>
                <select
                  v-model="form.part_instance_id"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                  required
                >
                  <option value="">
                    -- Choose Part Instance --
                  </option>
                  <option
                    v-for="pi in partInstances"
                    :key="pi.id"
                    :value="pi.id"
                  >
                    {{ pi.part?.part_mark }} - {{ pi.part?.assembly?.mark }} (ID: {{ pi.id }})
                  </option>
                </select>
                <p
                  v-if="!partInstances.length"
                  class="text-xs text-red-500 mt-1"
                >
                  No eligible part instances found.
                </p>
              </div>

              <div v-if="form.type === 'production'">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Select Production Item</label>
                <select
                  v-model="form.production_item_id"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                  required
                >
                  <option value="">
                    -- Choose Production Item --
                  </option>
                  <option
                    v-for="item in productionItems"
                    :key="item.id"
                    :value="item.id"
                  >
                    {{ item.part_instance?.part?.part_mark || 'Unknown' }} - Batch #{{ item.batch_id }}
                  </option>
                </select>
                <p
                  v-if="!productionItems.length"
                  class="text-xs text-red-500 mt-1"
                >
                  No eligible production items found.
                </p>
              </div>

              <div v-if="form.type === 'material'">
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Select Material Stock</label>
                <select
                  v-model="form.stock_item_id"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                  required
                >
                  <option value="">
                    -- Choose Stock Item --
                  </option>
                  <option
                    v-for="si in stockItems"
                    :key="si.id"
                    :value="si.id"
                  >
                    {{ si.stock_id }} - {{ si.material?.type }} {{ si.size }} (H: {{ si.heat_number || 'N/A' }})
                  </option>
                </select>
                <p
                  v-if="!stockItems.length"
                  class="text-xs text-red-500 mt-1"
                >
                  No eligible stock items found.
                </p>
              </div>

              <!-- Failure Details -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Detailed Failure Reason</label>
                <textarea 
                  v-model="form.failure_reason" 
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" 
                  rows="4" 
                  placeholder="Explain exactly what went wrong..."
                  required
                />
                <div
                  v-if="form.errors.failure_reason"
                  class="text-red-500 text-xs mt-1"
                >
                  {{ form.errors.failure_reason }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Initial Remediation Notes (Optional)</label>
                <textarea 
                  v-model="form.remediation_notes" 
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" 
                  rows="2" 
                  placeholder="Any immediate actions taken?"
                />
              </div>
            </div>

            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
              <Link
                :href="route('ncr.index')"
                class="text-gray-500 hover:text-gray-700 font-medium text-sm"
              >
                Cancel & Return
              </Link>

              <button 
                class="bg-red-600 text-white px-8 py-3 rounded-md font-bold uppercase tracking-widest hover:bg-red-700 transition duration-150 disabled:opacity-50 shadow-lg" 
                :disabled="form.processing"
              >
                <span v-if="form.processing">Submitting...</span>
                <span v-else>Register NCR</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
