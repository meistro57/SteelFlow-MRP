<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/">
                <h1 class="text-4xl font-bold text-primary">
                    SteelFlow MRP
                </h1>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label class="block font-medium text-sm text-gray-700" for="email">Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                        v-model="form.email" 
                        required 
                        autofocus 
                        autocomplete="username" 
                    />
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-2">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700" for="password">Password</label>
                    <input 
                        id="password" 
                        type="password" 
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" 
                        v-model="form.password" 
                        required 
                        autocomplete="current-password" 
                    />
                    <div v-if="form.errors.password" class="text-red-600 text-sm mt-2">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" v-model="form.remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="ms-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </button>
                </div>
            </form>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <a :href="route('login.microsoft')" class="w-full inline-flex justify-center items-center px-4 py-2 bg-[#00a1f1] border border-transparent rounded-md font-semibold text-white uppercase tracking-widest text-xs hover:bg-[#008bcf]">
                   Microsoft Login (SSO)
                </a>
            </div>
        </div>
    </div>
</template>
