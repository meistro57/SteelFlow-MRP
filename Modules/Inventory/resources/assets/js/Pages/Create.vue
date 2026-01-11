<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    materials: Array,
    projects: Array,
    statuses: Object,
    stockAreas: Object,
    types: Array,
    sizesByType: Object,
    grades: Array,
});

</script>

<template>
  <AppLayout title="Create Stock Item">
    Create form here
  </AppLayout>
</template>