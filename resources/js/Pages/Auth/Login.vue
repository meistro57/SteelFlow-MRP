<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/">
                <h1 class="text-4xl font-bold text-primary">SteelFlow MRP</h1>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <div class="flex flex-col items-center justify-center p-8">
                <img src="/BADGE_LOGO.jpg" alt="Logo" class="h-24 mb-8" />
                
                <h2 class="text-2xl font-semibold mb-6">Welcome Back</h2>
                
                <a :href="route('login.microsoft')" class="w-full inline-flex justify-center items-center px-4 py-3 bg-[#00a1f1] border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-[#008bcf] focus:bg-[#008bcf] active:bg-[#0076af] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 23 23" fill="currentColor">
                        <path d="M11.4 0H0v11.4h11.4V0zM23 0H11.6v11.4H23V0zM11.4 11.6H0V23h11.4V11.6zM23 11.6H11.6V23H23V11.6z"/>
                    </svg>
                    Sign in with Microsoft 365
                </a>
                
                <p class="mt-6 text-sm text-gray-600 text-center">
                    Please use your company Microsoft 365 account to access SteelFlow MRP.
                </p>
            </div>
        </div>
    </div>
</template>
