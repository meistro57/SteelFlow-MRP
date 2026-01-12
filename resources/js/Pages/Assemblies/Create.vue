<!-- resources/js/Pages/Assemblies/Create.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
});

const form = useForm({
    project_id: props.project.id,
    mark: '',
    description: '',
    quantity: 1,
    weight_each_lbs: null,
    assembly_type: '',
    main_member_type: '',
    main_member_size: '',
    main_member_grade: '',
    main_member_length: null,
});

const submit = () => {
    form.post(route('assemblies.store'));
};
</script>

<template>
  <AppLayout title="Create Assembly">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-white leading-tight">
          New Assembly: {{ project.name }} ({{ project.job_number }})
        </h2>
        <Link
          :href="route('projects.show', project.id)"
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
              placeholder="e.g. Column, Beam, Brace"
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

        <div class="flex justify-end mt-8">
          <button
            type="submit"
            class="btn-forge"
            :disabled="form.processing"
          >
            Create Assembly
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
