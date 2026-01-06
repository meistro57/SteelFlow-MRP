<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm(
    {
        email: '',
        password: '',
        remember: false,
    },
    {
        // Keep login details available across visits when the user opts in
        remember: 'loginCredentials',
    },
);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="Log in" />

  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-steel-950">
    <div>
      <Link href="/">
        <h1 class="text-4xl font-bold text-blue-500">
          SteelFlow MRP
        </h1>
      </Link>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-steel-900 border border-steel-800 shadow-xl overflow-hidden sm:rounded-lg">
      <div
        v-if="status"
        class="mb-4 font-medium text-sm text-green-400"
      >
        {{ status }}
      </div>

      <form @submit.prevent="submit">
        <div>
          <label
            class="block font-medium text-sm text-text-secondary"
            for="email"
          >Email</label>
          <input 
            id="email" 
            v-model="form.email" 
            type="email" 
            class="bg-steel-800 border-steel-700 text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm mt-1 block w-full" 
            required 
            autofocus 
            autocomplete="username" 
          >
          <div
            v-if="form.errors.email"
            class="text-red-400 text-sm mt-2"
          >
            {{ form.errors.email }}
          </div>
        </div>

        <div class="mt-4">
          <label
            class="block font-medium text-sm text-text-secondary"
            for="password"
          >Password</label>
          <input 
            id="password" 
            v-model="form.password" 
            type="password" 
            class="bg-steel-800 border-steel-700 text-white focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm mt-1 block w-full" 
            required 
            autocomplete="current-password" 
          >
          <div
            v-if="form.errors.password"
            class="text-red-400 text-sm mt-2"
          >
            {{ form.errors.password }}
          </div>
        </div>

        <div class="block mt-4">
          <label class="flex items-center">
            <input
              v-model="form.remember"
              type="checkbox"
              name="remember"
              class="rounded border-steel-700 bg-steel-800 text-blue-600 shadow-sm focus:ring-blue-500"
            >
            <span class="ms-2 text-sm text-text-tertiary">Remember me</span>
          </label>
        </div>

        <div class="flex items-center justify-end mt-4">
          <button
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Log in
          </button>
        </div>
      </form>
            
      <div class="mt-6 pt-6 border-t border-steel-800">
        <a
          :href="route('login.microsoft')"
          class="w-full inline-flex justify-center items-center px-4 py-2 bg-[#00a1f1] border border-transparent rounded-md font-semibold text-white uppercase tracking-widest text-xs hover:bg-[#008bcf] transition duration-150"
        >
          <svg class="w-4 h-4 mr-2" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg"><path fill="#f3f3f3" d="M0 0h11v11H0zM12 0h11v11H12zM0 12h11v11H0zM12 12h11v11H12z"/></svg>
          Microsoft (SSO)
        </a>
      </div>
    </div>
  </div>

</template>
