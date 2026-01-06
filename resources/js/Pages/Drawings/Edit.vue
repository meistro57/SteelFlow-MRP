<!-- resources/js/Pages/Drawings/Edit.vue -->
<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    drawing: Object,
    projects: Array,
});

const form = useForm({
    project_id: props.drawing.project_id,
    number: props.drawing.number,
    revision: props.drawing.revision ?? '',
    title: props.drawing.title ?? '',
});

const uploadForm = useForm({
    file: null,
    revision: props.drawing.revision ?? '',
});

const submit = () => {
    form.put(`/drawings/${props.drawing.id}`);
};

const submitUpload = () => {
    uploadForm.post(`/drawings/${props.drawing.id}/upload`, {
        forceFormData: true,
        onSuccess: () => {
            uploadForm.reset('file');
        },
    });
};
</script>

<template>
  <AppLayout :title="`Edit Drawing ${drawing.number}`">
    <template #header>
      <div class="flex items-center">
        <Link
          href="/drawings"
          class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
        >
          &larr; Back
        </Link>
        <div>
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Drawing {{ drawing.number }}
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ drawing.project?.name || 'Unassigned Project' }}
          </p>
        </div>
      </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
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
              Save Changes
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            File Management
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Upload a new revision or replace the current file. Existing files are removed automatically.
          </p>

          <div class="flex items-center justify-between p-4 bg-steel-900/40 rounded-md border border-steel-800">
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Current File
              </div>
              <div class="text-base font-semibold text-white">
                <template v-if="drawing.file_path && drawing.url">
                  <a
                    :href="drawing.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-weld-300 hover:text-weld-200"
                  >
                    View Drawing
                  </a>
                </template>
                <template v-else>
                  Not uploaded
                </template>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Revision
              </div>
              <div class="text-base font-semibold text-white">
                {{ drawing.revision || '—' }}
              </div>
            </div>
          </div>

          <form
            class="space-y-4"
            @submit.prevent="submitUpload"
          >
            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                for="file"
              >
                Upload File
              </label>
              <input
                id="file"
                type="file"
                accept=".pdf,.dwg,.dxf,.jpg,.jpeg,.png"
                class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-200"
                @change="uploadForm.file = $event.target.files[0]"
              >
              <p
                v-if="uploadForm.errors.file"
                class="mt-1 text-sm text-red-600"
              >
                {{ uploadForm.errors.file }}
              </p>
            </div>

            <div>
              <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                for="upload_revision"
              >
                Revision Label
              </label>
              <input
                id="upload_revision"
                v-model="uploadForm.revision"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Optional revision tag for this upload"
              >
              <p
                v-if="uploadForm.errors.revision"
                class="mt-1 text-sm text-red-600"
              >
                {{ uploadForm.errors.revision }}
              </p>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                :disabled="uploadForm.processing"
              >
                Upload & Replace
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
