<!-- resources/js/Pages/Parts/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    assembly: Object,
});

const form = useForm({
    project_id: props.assembly.project_id,
    assembly_id: props.assembly.id,
    part_mark: '',
    material_id: null,
    type: '',
    size_imperial: '',
    length: null,
    quantity: 1,
    is_main_member: false,
    finish: '',
    remarks: '',
});

const submit = () => {
    form.post(route('parts.store'));
};
</script>

<template>
  <AppLayout title="Add Part">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-white leading-tight">
          Add Part to Assembly: {{ assembly.mark }}
        </h2>
        <Link
          :href="route('assemblies.show', assembly.id)"
          class="btn-secondary"
        >
          Cancel
        </Link>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <form
        class="card-industrial space-y-6"
        @submit.prevent="submit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-1">
            <label
              for="part_mark"
              class="block text-sm font-medium text-steel-300"
            >Part Mark</label>
            <input
              id="part_mark"
              v-model="form.part_mark"
              type="text"
              class="input-industrial w-full"
              required
            >
            <div
              v-if="form.errors.part_mark"
              class="text-forge-500 text-xs mt-1"
            >
              {{ form.errors.part_mark }}
            </div>
          </div>

          <div class="space-y-1">
            <label
              for="quantity"
              class="block text-sm font-medium text-steel-300"
            >Quantity (per assembly)</label>
            <input
              id="quantity"
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
            <label
              for="type"
              class="block text-sm font-medium text-steel-300"
            >Material Type</label>
            <input
              id="type"
              v-model="form.type"
              type="text"
              class="input-industrial w-full"
              placeholder="e.g. W, L, PL"
            >
          </div>

          <div class="space-y-1">
            <label
              for="size_imperial"
              class="block text-sm font-medium text-steel-300"
            >Size</label>
            <input
              id="size_imperial"
              v-model="form.size_imperial"
              type="text"
              class="input-industrial w-full"
              placeholder="e.g. 12x26, 4x4x1/4"
            >
          </div>

          <div class="space-y-1">
            <label
              for="length"
              class="block text-sm font-medium text-steel-300"
            >Length (ft)</label>
            <input
              id="length"
              v-model="form.length"
              type="number"
              step="0.001"
              class="input-industrial w-full"
            >
          </div>

          <div class="flex items-center pt-6">
            <input
              id="is_main_member"
              v-model="form.is_main_member"
              type="checkbox"
              class="rounded border-steel-700 bg-steel-900 text-forge-600 focus:ring-forge-500"
            >
            <label
              for="is_main_member"
              class="ml-2 block text-sm text-steel-300"
            >Main Member</label>
          </div>

          <div class="space-y-1">
            <label
              for="finish"
              class="block text-sm font-medium text-steel-300"
            >Finish</label>
            <input
              id="finish"
              v-model="form.finish"
              type="text"
              class="input-industrial w-full"
            >
          </div>

          <div class="space-y-1">
            <label
              for="remarks"
              class="block text-sm font-medium text-steel-300"
            >Remarks</label>
            <input
              id="remarks"
              v-model="form.remarks"
              type="text"
              class="input-industrial w-full"
            >
          </div>
        </div>

        <div class="flex justify-end mt-8">
          <button
            type="submit"
            class="btn-forge"
            :disabled="form.processing"
          >
            Save Part
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
