<!-- resources/js/Pages/Projects/ImportKiss.vue -->
<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
});

const form = useForm({
    file: null,
});

const submit = () => {
    form.post(route('projects.import-kiss.store', props.project.id), {
        forceFormData: true,
    });
};
</script>

<template>
  <AppLayout title="Import KISS File">
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Import KISS File
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Upload a KISS CAD file to import assemblies and parts for {{ project.name }}
        </p>
      </div>
      <Link
        :href="route('projects.show', project.id)"
        class="btn-secondary"
      >
        Back to Project
      </Link>
    </div>

    <form
      class="card p-6 space-y-6"
      @submit.prevent="submit"
    >
      <div>
        <label class="input-label">KISS File</label>
        <input
          type="file"
          accept=".txt,.csv,.kiss"
          class="input"
          required
          @change="form.file = $event.target.files[0]"
        >
        <div
          v-if="form.errors.file"
          class="input-error"
        >
          {{ form.errors.file }}
        </div>
        <p class="mt-2 text-xs text-steel-400 font-mono">
          KISS format files contain assembly and part information from CAD software.
          Expected format: SHP (assemblies) and DET (parts) records with mark, quantity, material, and weight data.
        </p>
        <p class="mt-2">
          <a
            :href="route('import-templates.kiss')"
            class="text-sm text-forge-400 hover:text-forge-300 font-mono underline"
          >
            Download Template KISS File
          </a>
        </p>
      </div>

      <div class="bg-steel-800/50 border border-steel-700 rounded p-4">
        <h3 class="text-sm font-semibold text-steel-300 mb-2">
          Import Details
        </h3>
        <ul class="space-y-1 text-xs text-steel-400 font-mono">
          <li>• Project: {{ project.name }}</li>
          <li>• Job Number: {{ project.job_number }}</li>
          <li>• Assemblies and parts will be created automatically</li>
          <li>• Weights will be calculated based on material data</li>
        </ul>
      </div>

      <div class="flex justify-end space-x-3">
        <Link
          :href="route('projects.show', project.id)"
          class="btn-secondary"
        >
          Cancel
        </Link>
        <button
          type="submit"
          class="btn-primary"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Importing...</span>
          <span v-else>Import KISS File</span>
        </button>
      </div>
    </form>
  </AppLayout>
</template>
