<!-- Modules/Inventory/resources/assets/js/Pages/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    materials: Array,
    projects: Array,
    statuses: Object,
    stockAreas: Object,
    types: Array,
    sizesByType: Object,
    grades: Array,
});

const form = useForm({
    material_id: null,
    type: '',
    size: '',
    grade: '',
    length: null,
    quantity: 1,
    status: 'free',
    reserved_project_id: null,
    stock_area: '',
    heat_number: '',
    po_number: '',
    country_of_origin: '',
    cost_per_unit: null,
    receive_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const onMaterialChange = () => {
    if (form.material_id) {
        const material = props.materials.find(m => m.id === parseInt(form.material_id));
        if (material) {
            form.type = material.type;
            form.size = material.size_imperial || material.size_metric;
            form.grade = material.grade?.code || '';
        }
    }
};

const sizes = computed(() => {
    if (!form.type) return [];
    return props.sizesByType[form.type] || [];
});

const submit = () => {
    form.post(route('inventory.store'));
};
</script>

<template>
  <AppLayout title="Add Stock Item">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-semibold text-xl text-white leading-tight">
            Add New Stock Item
          </h2>
          <p class="text-sm text-steel-400">
            Manual inventory entry
          </p>
        </div>
        <Link
          :href="route('inventory.index')"
          class="btn-secondary"
        >
          Cancel
        </Link>
      </div>
    </template>

    <div class="max-w-5xl mx-auto">
      <form
        class="space-y-6"
        @submit.prevent="submit"
      >
        <div class="card-industrial">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Material Selection -->
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Quick Select Material (Optional)
              </label>
              <select
                v-model="form.material_id"
                class="input-industrial w-full"
                @change="onMaterialChange"
              >
                <option :value="null">
                  -- Select a material to auto-fill --
                </option>
                <option
                  v-for="m in materials"
                  :key="m.id"
                  :value="m.id"
                >
                  {{ m.type }} {{ m.size_imperial }} ({{ m.grade?.code }})
                </option>
              </select>
            </div>

            <!-- Basic Info -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Material Type *
              </label>
              <select
                v-model="form.type"
                class="input-industrial w-full"
                required
              >
                <option value="">
                  -- Select Type --
                </option>
                <option
                  v-for="t in types"
                  :key="t"
                  :value="t"
                >
                  {{ t }}
                </option>
              </select>
              <div
                v-if="form.errors.type"
                class="text-forge-500 text-xs mt-1"
              >
                {{ form.errors.type }}
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Size *
              </label>
              <input
                v-model="form.size"
                type="text"
                class="input-industrial w-full"
                list="size-options"
                required
              >
              <datalist id="size-options">
                <option
                  v-for="s in sizes"
                  :key="s"
                  :value="s"
                />
              </datalist>
              <div
                v-if="form.errors.size"
                class="text-forge-500 text-xs mt-1"
              >
                {{ form.errors.size }}
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Grade *
              </label>
              <select
                v-model="form.grade"
                class="input-industrial w-full"
                required
              >
                <option value="">
                  -- Select Grade --
                </option>
                <option
                  v-for="g in grades"
                  :key="g"
                  :value="g"
                >
                  {{ g }}
                </option>
              </select>
              <div
                v-if="form.errors.grade"
                class="text-forge-500 text-xs mt-1"
              >
                {{ form.errors.grade }}
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Length (Decimal Feet) *
              </label>
              <input
                v-model="form.length"
                type="number"
                step="0.001"
                class="input-industrial w-full font-mono"
                required
              >
              <div
                v-if="form.errors.length"
                class="text-forge-500 text-xs mt-1"
              >
                {{ form.errors.length }}
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Quantity *
              </label>
              <input
                v-model="form.quantity"
                type="number"
                min="1"
                class="input-industrial w-full"
                required
              >
              <div
                v-if="form.errors.quantity"
                class="text-forge-500 text-xs mt-1"
              >
                {{ form.errors.quantity }}
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Status *
              </label>
              <select
                v-model="form.status"
                class="input-industrial w-full"
                required
              >
                <option
                  v-for="(label, value) in statuses"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Tracking Info -->
        <div class="card-industrial">
          <h3 class="text-forge-500 font-bold uppercase tracking-widest text-xs mb-4">
            Tracking & Logistics
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Heat Number
              </label>
              <input
                v-model="form.heat_number"
                type="text"
                class="input-industrial w-full font-mono"
              >
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                PO Number
              </label>
              <input
                v-model="form.po_number"
                type="text"
                class="input-industrial w-full font-mono"
              >
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Stock Area / Location
              </label>
              <select
                v-model="form.stock_area"
                class="input-industrial w-full"
              >
                <option value="">
                  -- Select Location --
                </option>
                <option
                  v-for="(label, value) in stockAreas"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Reserved for Project
              </label>
              <select
                v-model="form.reserved_project_id"
                class="input-industrial w-full"
              >
                <option :value="null">
                  -- No Reservation --
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
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Receive Date
              </label>
              <input
                v-model="form.receive_date"
                type="date"
                class="input-industrial w-full"
              >
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
                Country of Origin
              </label>
              <input
                v-model="form.country_of_origin"
                type="text"
                class="input-industrial w-full"
              >
            </div>
          </div>

          <div class="mt-6 space-y-1">
            <label class="block text-xs font-bold text-steel-500 uppercase tracking-widest mb-2">
              Notes
            </label>
            <textarea
              v-model="form.notes"
              class="input-industrial w-full h-24"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-4">
          <Link
            :href="route('inventory.index')"
            class="btn-secondary"
          >
            Cancel
          </Link>
          <button
            type="submit"
            class="btn-forge px-8"
            :disabled="form.processing"
          >
            Save Stock Item
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
