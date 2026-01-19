<!-- resources/js/Pages/Drawings/Create.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    projects: Array,
});

const form = useForm({
    project_id: '',
    number: '',
    revision: '',
    title: '',
});

const submit = () => {
    form.post('/drawings');
};
</script>

<template>
  <AppLayout title="Create Drawing">
    <template #header>
      <div class="flex items-center">
        <Link
          href="/drawings"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
        >
          &larr; Back
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Create Drawing
        </h2>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
      <form
        class="p-6 space-y-6"
        @submit.prevent="submit"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label
              for="project_id"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Project *
            </label>
            <select
              id="project_id"
              v-model="form.project_id"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">
                Select Project
              </option>
              <option
                v-for="project in projects"
                :key="project.id"
                :value="project.id"
              >
                {{ project.job_number ? `${project.job_number} — ${project.name}` : project.name }}
              </option>
            </select>
            <p
              v-if="form.errors.project_id"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.project_id }}
            </p>
          </div>

          <div>
            <label
              for="number"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Drawing Number *
            </label>
            <input
              id="number"
              v-model="form.number"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <p
              v-if="form.errors.number"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.number }}
            </p>
          </div>

          <div>
            <label
              for="revision"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Revision
            </label>
            <input
              id="revision"
              v-model="form.revision"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="e.g. A1"
            >
            <p
              v-if="form.errors.revision"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.revision }}
            </p>
          </div>

          <div>
            <label
              for="title"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              Title
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="(optional)"
            >
            <p
              v-if="form.errors.title"
              class="mt-1 text-sm text-red-600"
            >
              {{ form.errors.title }}
            </p>
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            :disabled="form.processing"
          >
            Save Drawing
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
