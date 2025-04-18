<template>
    <AppLayout :title="'Coming Soon - ' + feature">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Coming Soon
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <h3 class="text-lg font-medium mb-4">
                            {{ feature }} is coming soon!
                        </h3>
                        <p>
                            This feature is under development and will be
                            available in a future update.
                        </p>
                        <div v-if="id" class="mt-2 text-sm text-gray-600">
                            You were trying to access ID: {{ id }}
                        </div>
                        <div class="mt-6">
                            <Link
                                :href="route('contacts.index')"
                                class="text-indigo-600 hover:text-indigo-800"
                            >
                                Return to Contacts
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    feature: String,
    id: [String, Number],
});
</script>
