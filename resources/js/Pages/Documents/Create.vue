<!-- resources/js/Pages/Documents/Create.vue -->
<template>
    <AppLayout title="Upload Document">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Upload Document
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form
                        @submit.prevent="submit"
                        class="p-6"
                        enctype="multipart/form-data"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- File Upload -->
                            <div class="md:col-span-2">
                                <InputLabel for="file" value="File" />
                                <div
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                                    @dragover.prevent
                                    @drop.prevent="onFileDrop"
                                    :class="{ 'border-indigo-500': isDragging }"
                                    @dragenter.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                >
                                    <div class="space-y-1 text-center">
                                        <svg
                                            class="mx-auto h-12 w-12 text-gray-400"
                                            stroke="currentColor"
                                            fill="none"
                                            viewBox="0 0 48 48"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label
                                                for="file"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                                            >
                                                <span>Upload a file</span>
                                                <input
                                                    id="file"
                                                    ref="fileInput"
                                                    type="file"
                                                    class="sr-only"
                                                    @change="onFileChange"
                                                />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            Any file up to 10MB
                                        </p>
                                    </div>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.file"
                                />

                                <!-- File Preview -->
                                <div
                                    v-if="selectedFile"
                                    class="mt-3 flex items-center justify-between p-3 bg-gray-50 rounded-md"
                                >
                                    <div class="flex items-center">
                                        <DocumentIcon
                                            :fileType="selectedFile.type"
                                            class="h-10 w-10"
                                        />
                                        <div class="ml-3">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ selectedFile.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{
                                                    formatFileSize(
                                                        selectedFile.size
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="md:col-span-2">
                                <InputLabel for="title" value="Title" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.title"
                                />
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <InputLabel
                                    for="description"
                                    value="Description"
                                />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="3"
                                ></textarea>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.description"
                                />
                            </div>

                            <!-- Category -->
                            <div>
                                <InputLabel for="category" value="Category" />
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <input
                                        list="categories"
                                        id="category"
                                        type="text"
                                        class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        v-model="form.category"
                                        placeholder="Select or enter a category"
                                    />
                                    <datalist id="categories">
                                        <option
                                            v-for="category in categories"
                                            :key="category"
                                            :value="category"
                                        >
                                            {{ category }}
                                        </option>
                                    </datalist>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.category"
                                />
                            </div>

                            <!-- Related To -->
                            <div v-if="relatedTo">
                                <InputLabel value="Related To" />
                                <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                    <div class="text-sm text-gray-500">
                                        {{ capitalize(relatedTo.type) }}
                                    </div>
                                    <div class="text-sm font-medium">
                                        {{ relatedTo.name }}
                                    </div>
                                </div>
                                <input
                                    type="hidden"
                                    v-model="form.documentable_type"
                                />
                                <input
                                    type="hidden"
                                    v-model="form.documentable_id"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="
                                        form.errors.documentable_id ||
                                        form.errors.documentable_type
                                    "
                                />
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-6 flex items-center justify-end gap-4">
                            <Link
                                :href="route('documents.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing || !selectedFile"
                            >
                                Upload Document
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import DocumentIcon from "@/Components/DocumentIcon.vue";

const props = defineProps({
    categories: Array,
    relatedTo: Object,
});

const fileInput = ref(null);
const selectedFile = ref(null);
const isDragging = ref(false);

// Initialize form with default values
const form = useForm({
    file: null,
    title: "",
    description: "",
    category: "",
    documentable_type: props.relatedTo
        ? `App\\Models\\${capitalize(props.relatedTo.type)}`
        : null,
    documentable_id: props.relatedTo ? props.relatedTo.id : null,
});

function capitalize(string) {
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function onFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        form.file = file;

        // Auto-fill title if empty
        if (!form.title) {
            form.title = file.name.split(".")[0]; // Use filename without extension as title
        }
    }
}

function onFileDrop(event) {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        selectedFile.value = file;
        form.file = file;

        // Auto-fill title if empty
        if (!form.title) {
            form.title = file.name.split(".")[0]; // Use filename without extension as title
        }
    }
}

function removeFile() {
    selectedFile.value = null;
    form.file = null;
    if (fileInput.value) {
        fileInput.value.value = "";
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
}

function submit() {
    form.post(route("documents.store"));
}
</script>
