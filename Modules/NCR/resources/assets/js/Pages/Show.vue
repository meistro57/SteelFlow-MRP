<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    ncr: Object,
    dispositions: Object,
});

const dispositionForm = useForm({
    disposition: '',
    notes: '',
    rework_operation: '',
});

const submitDisposition = () => {
    dispositionForm.post(route('ncr.disposition', props.ncr.id), {
        onSuccess: () => dispositionForm.reset(),
    });
};

const closeNCR = () => {
    if (confirm('Are you sure you want to close this NCR?')) {
        router.post(route('ncr.close', props.ncr.id));
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'open': return 'bg-yellow-100 text-yellow-800';
        case 'under_review': return 'bg-blue-100 text-blue-800';
        case 'dispositioned': return 'bg-purple-100 text-purple-800';
        case 'closed': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
  <AppLayout title="NCR Details">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          NCR: {{ ncr.number }}
        </h2>
        <span :class="['px-3 py-1 rounded-full text-sm font-semibold capitalize', getStatusColor(ncr.status)]">
          {{ ncr.status }}
        </span>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-700">
                Information
              </h3>
              <div class="space-y-3">
                <p><span class="text-gray-500 font-medium">Reporter:</span> {{ ncr.reporter?.name }}</p>
                <p><span class="text-gray-500 font-medium">Date Reported:</span> {{ new Date(ncr.created_at).toLocaleDateString() }}</p>
                                
                <div class="mt-4">
                  <p class="text-gray-500 font-medium mb-1">
                    Failure Reason:
                  </p>
                  <div class="p-4 bg-gray-50 rounded border border-gray-100 italic text-gray-700">
                    {{ ncr.failure_reason }}
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-700">
                Subject Details
              </h3>
              <div class="space-y-3">
                <div
                  v-if="ncr.part_instance"
                  class="bg-blue-50 p-4 rounded border border-blue-100"
                >
                  <p class="text-blue-800 font-bold mb-2">
                    Part Instance
                  </p>
                  <p><span class="text-blue-600 font-medium">Part Mark:</span> {{ ncr.part_instance.part.part_mark }}</p>
                  <p><span class="text-blue-600 font-medium">Assembly:</span> {{ ncr.part_instance.part.assembly?.mark }}</p>
                  <p><span class="text-blue-600 font-medium">Project:</span> {{ ncr.part_instance.part.project?.job_number }}</p>
                </div>
                <div
                  v-if="ncr.production_item"
                  class="bg-indigo-50 p-4 rounded border border-indigo-100"
                >
                  <p class="text-indigo-800 font-bold mb-2">
                    Production Item
                  </p>
                  <p><span class="text-indigo-600 font-medium">Item ID:</span> #{{ ncr.production_item.id }}</p>
                  <p v-if="ncr.production_item.part_instance">
                    <span class="text-indigo-600 font-medium">Part:</span> {{ ncr.production_item.part_instance.part?.part_mark }}
                  </p>
                </div>
                <div
                  v-if="ncr.stock_item"
                  class="bg-green-50 p-4 rounded border border-green-100"
                >
                  <p class="text-green-800 font-bold mb-2">
                    Stock Item
                  </p>
                  <p><span class="text-green-600 font-medium">Stock ID:</span> {{ ncr.stock_item.stock_id }}</p>
                  <p><span class="text-green-600 font-medium">Material:</span> {{ ncr.stock_item.material?.type }} {{ ncr.stock_item.size }}</p>
                  <p><span class="text-green-600 font-medium">Grade:</span> {{ ncr.stock_item.grade }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Disposition Form (if Open or Under Review) -->
        <div
          v-if="ncr.status === 'open' || ncr.status === 'under_review'"
          class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6 border-l-4 border-blue-500"
        >
          <h3 class="text-lg font-bold mb-4 text-gray-700">
            Make Disposition
          </h3>
          <form @submit.prevent="submitDisposition">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Select Action</label>
                  <select
                    v-model="dispositionForm.disposition"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">
                      Choose an action...
                    </option>
                    <option
                      v-for="(label, value) in dispositions"
                      :key="value"
                      :value="value"
                    >
                      {{ label }}
                    </option>
                  </select>
                </div>

                <div v-if="dispositionForm.disposition === 'REWORK'">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Rework Operation</label>
                  <input
                    v-model="dispositionForm.rework_operation"
                    type="text"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g. Grinding, Re-welding"
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Disposition/Remediation Notes</label>
                <textarea
                  v-model="dispositionForm.notes"
                  rows="4"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Explain the decision and required steps..."
                />
              </div>
            </div>

            <div class="flex justify-end mt-6">
              <button
                type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition duration-150 ease-in-out shadow-sm"
                :disabled="dispositionForm.processing || !dispositionForm.disposition"
              >
                Submit Disposition
              </button>
            </div>
          </form>
        </div>

        <!-- Already Dispositioned Details -->
        <div
          v-if="ncr.status === 'dispositioned' || ncr.status === 'closed'"
          class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6 border-l-4 border-purple-500"
        >
          <h3 class="text-lg font-bold mb-4 text-gray-700">
            Disposition Decision
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <div class="space-y-3">
                <p><span class="text-gray-500 font-medium">Action:</span> <span class="font-bold text-purple-700">{{ ncr.disposition }}</span></p>
                <p v-if="ncr.rework_operation">
                  <span class="text-gray-500 font-medium">Specific Operation:</span> {{ ncr.rework_operation }}
                </p>
                <p><span class="text-gray-500 font-medium">Dispositioner:</span> {{ ncr.dispositioner?.name }}</p>
                <p><span class="text-gray-500 font-medium">Decision Date:</span> {{ ncr.dispositioned_at ? new Date(ncr.dispositioned_at).toLocaleDateString() : 'N/A' }}</p>
              </div>
            </div>
            <div>
              <p class="text-gray-500 font-medium mb-1">
                Remediation/Closure Notes:
              </p>
              <div class="p-4 bg-purple-50 rounded border border-purple-100 text-gray-700 h-full min-h-[100px]">
                {{ ncr.remediation_notes || 'No specific notes provided.' }}
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between items-center">
          <Link
            :href="route('ncr.index')"
            class="text-blue-600 hover:text-blue-800 font-medium flex items-center"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4 mr-1"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Back to List
          </Link>
                    
          <button
            v-if="ncr.status === 'dispositioned'"
            class="bg-green-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-green-700 transition duration-150 ease-in-out shadow-sm flex items-center"
            @click="closeNCR"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-1"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
            Mark as Resolved/Closed
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
