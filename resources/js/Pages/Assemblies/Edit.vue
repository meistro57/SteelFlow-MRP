<!-- resources/js/Pages/Assemblies/Edit.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    assembly: Object,
});

const form = useForm({
    mark: props.assembly.mark,
    description: props.assembly.description,
    quantity: props.assembly.quantity,
    weight_each_lbs: props.assembly.weight_each_lbs,
    assembly_type: props.assembly.assembly_type,
    main_member_type: props.assembly.main_member_type,
    main_member_size: props.assembly.main_member_size,
    main_member_grade: props.assembly.main_member_grade,
    main_member_length: props.assembly.main_member_length,
    drawing_id: props.assembly.drawing_id,
});

const submit = () => {
    form.put(route('assemblies.update', props.assembly.id));
};
</script>

<template>
  <AppLayout title="Edit Assembly">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-white leading-tight">
          Edit Assembly: {{ assembly.mark }}
        </h2>
        <Link
          :href="route('projects.show', assembly.project_id)"
          class="btn-secondary"
        >
          Back to Project
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
              for="mark"
              class="block text-sm font-medium text-steel-300"
            >Assembly Mark</label>
            <input
              id="mark"
              v-model="form.mark"
              type="text"
              class="input-industrial w-full"
              required
            >
            <div
              v-if="form.errors.mark"
              class="text-forge-500 text-xs mt-1"
            >
              {{ form.errors.mark }}
            </div>
          </div>

          <div class="space-y-1">
            <label
              for="quantity"
              class="block text-sm font-medium text-steel-300"
            >Quantity</label>
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

          <div class="md:col-span-2 space-y-1">
            <label
              for="description"
              class="block text-sm font-medium text-steel-300"
            >Description</label>
            <input
              id="description"
              v-model="form.description"
              type="text"
              class="input-industrial w-full"
            >
          </div>

          <div class="space-y-1">
            <label
              for="assembly_type"
              class="block text-sm font-medium text-steel-300"
            >Assembly Type</label>
            <input
              id="assembly_type"
              v-model="form.assembly_type"
              type="text"
              class="input-industrial w-full"
            >
          </div>

          <div class="space-y-1">
            <label
              for="weight_each_lbs"
              class="block text-sm font-medium text-steel-300"
            >Weight Each (lbs)</label>
            <input
              id="weight_each_lbs"
              v-model="form.weight_each_lbs"
              type="number"
              step="0.01"
              class="input-industrial w-full"
            >
          </div>

          <div class="md:col-span-2">
            <h3 class="text-forge-500 font-bold uppercase tracking-widest text-xs mb-4">
              Main Member Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="block text-sm font-medium text-steel-300">Type</label>
                <input
                  v-model="form.main_member_type"
                  type="text"
                  class="input-industrial w-full"
                >
              </div>
              <div class="space-y-1">
                <label class="block text-sm font-medium text-steel-300">Size</label>
                <input
                  v-model="form.main_member_size"
                  type="text"
                  class="input-industrial w-full"
                >
              </div>
              <div class="space-y-1">
                <label class="block text-sm font-medium text-steel-300">Grade</label>
                <input
                  v-model="form.main_member_grade"
                  type="text"
                  class="input-industrial w-full"
                >
              </div>
              <div class="space-y-1">
                <label class="block text-sm font-medium text-steel-300">Length (ft)</label>
                <input
                  v-model="form.main_member_length"
                  type="number"
                  step="0.001"
                  class="input-industrial w-full"
                >
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between mt-8">
          <Link
            :href="route('assemblies.destroy', assembly.id)"
            method="delete"
            as="button"
            class="text-forge-500 hover:text-forge-400 text-sm font-bold uppercase tracking-widest"
            on-before="() => confirm('Are you sure you want to delete this assembly? This will also delete all associated parts and instances.')"
          >
            Delete Assembly
          </Link>
          <button
            type="submit"
            class="btn-forge"
            :disabled="form.processing"
          >
            Update Assembly
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
