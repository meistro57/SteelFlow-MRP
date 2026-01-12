<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    partInstance: Object,
    workAreas: Array,
});

const form = useForm({
    work_area_id: '',
    sequence_number: 1,
    estimated_hours: 0,
});

const submit = () => {
    form.post(route('routing.store', props.partInstance.id));
};
</script>

<template>
    <AppLayout title="Create Routing Step">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add Routing Step for: {{ partInstance.part?.part_mark }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Work Area</label>
                            <select v-model="form.work_area_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option v-for="area in workAreas" :key="area.id" :value="area.id">
                                    {{ area.name }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Sequence Number</label>
                            <input type="number" v-model="form.sequence_number" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Estimated Hours</label>
                            <input type="number" step="0.1" v-model="form.estimated_hours" class="shadow border rounded w-full py-2 px-3 text-gray-700" />
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <button class="btn-primary" :disabled="form.processing">
                                Save Step
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
