<script setup>
import { watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const showSuccess = ref(false);
const showErrors = ref(false);
const showMessage = ref(false);

const successMessage = ref('');
const errorMessage = ref('');
const generalMessage = ref('');

watch(
    () => usePage().props.flash,
    (newValue) => {
        if (newValue && newValue.success) {
            successMessage.value = newValue.success;
            showSuccess.value = true;
            setTimeout(() => (showSuccess.value = false), 5000);
        } else {
            showSuccess.value = false;
        }

        if (newValue && newValue.error) {
            errorMessage.value = newValue.error;
            showErrors.value = true;
            setTimeout(() => (showErrors.value = false), 5000);
        } else {
            showErrors.value = false;
        }

        if (newValue && newValue.message) {
            generalMessage.value = newValue.message;
            showMessage.value = true;
            setTimeout(() => (showMessage.value = false), 5000);
        } else {
            showMessage.value = false;
        }
    },
    { deep: true }
);
</script>

<template>
  <div class="relative z-50">
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 -translate-y-full"
      enter-to-class="transform opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform opacity-100 translate-y-0"
      leave-to-class="transform opacity-0 -translate-y-full"
    >
      <div
        v-if="showSuccess"
        class="fixed top-0 left-0 right-0 p-4 bg-green-500 text-white text-center shadow-lg"
      >
        {{ successMessage }}
      </div>
    </transition>

    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 -translate-y-full"
      enter-to-class="transform opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform opacity-100 translate-y-0"
      leave-to-class="transform opacity-0 -translate-y-full"
    >
      <div
        v-if="showErrors"
        class="fixed top-0 left-0 right-0 p-4 bg-red-500 text-white text-center shadow-lg"
      >
        {{ errorMessage }}
      </div>
    </transition>

    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 -translate-y-full"
      enter-to-class="transform opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform opacity-100 translate-y-0"
      leave-to-class="transform opacity-0 -translate-y-full"
    >
      <div
        v-if="showMessage"
        class="fixed top-0 left-0 right-0 p-4 bg-blue-500 text-white text-center shadow-lg"
      >
        {{ generalMessage }}
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* Add any specific styles for flash messages here if needed */
</style>
