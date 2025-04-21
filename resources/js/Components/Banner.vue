<!-- resources/js/Components/Banner.vue -->
<template>
    <div v-if="show && message" class="bg-indigo-500">
        <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center min-w-0">
                    <span class="flex p-2 rounded-lg bg-indigo-600">
                        <svg
                            class="h-5 w-5 text-white"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </span>
                    <p class="ml-3 font-medium text-white truncate">
                        {{ message }}
                    </p>
                </div>
                <div class="shrink-0 sm:ml-3">
                    <button
                        type="button"
                        class="flex p-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:bg-indigo-600 transition ease-in-out duration-150"
                        @click="show = false"
                    >
                        <svg
                            class="h-5 w-5 text-white"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";

const show = ref(false);
const message = ref("");

onMounted(() => {
    const page = usePage();

    if (page.props.flash && page.props.flash.message) {
        message.value = page.props.flash.message;
        show.value = true;
    }

    if (page.props.flash && page.props.flash.success) {
        message.value = page.props.flash.success;
        show.value = true;
    }
});
</script>
