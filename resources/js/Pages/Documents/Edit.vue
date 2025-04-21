<!-- resources/js/Pages/Documents/Edit.vue -->
<template>
    <AppLayout title="Edit Document">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Document
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Current File Info -->
                            <div class="md:col-span-2">
                                <div
                                    class="flex items-center p-4 bg-gray-50 rounded-md"
                                >
                                    <DocumentIcon
                                        :fileType="document.file_type"
                                        class="h-10 w-10"
                                    />
                                    <div class="ml-3">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ document.original_filename }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{
                                                formatFileSize(
                                                    document.file_size
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <div class="ml-auto">
                                        <Link
                                            :href="
                                                route(
                                                    'documents.download',
                                                    document.id
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Download
                                        </Link>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    To update the file content, please delete
                                    this document and upload a new one.
                                </p>
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

                            <!-- Version -->
                            <div>
                                <InputLabel for="version" value="Version" />
                                <TextInput
                                    id="version"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.version"
                                    placeholder="1.0"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.version"
                                />
                            </div>

                            <!-- Related To (Read-only) -->
                            <div
                                v-if="document.documentable"
                                class="md:col-span-2"
                            >
                                <InputLabel value="Related To" />
                                <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                    <div class="text-sm text-gray-500">
                                        {{ getRelatedType(document) }}
                                    </div>
                                    <div class="text-sm font-medium">
                                        {{ getRelatedName(document) }}
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    The document's relationship cannot be
                                    changed.
                                </p>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-6 flex items-center justify-between">
                            <div>
                                <DangerButton
                                    v-if="can.delete"
                                    @click.prevent="confirmDelete"
                                    type="button"
                                >
                                    Delete Document
                                </DangerButton>
                            </div>
                            <div class="flex items-center gap-4">
                                <Link
                                    :href="route('documents.show', document.id)"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Update Document
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Document
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this document? This action
                    cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton
                        @click="confirmingDeletion = false"
                        class="mr-3"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteDocument">
                        Delete Document
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import DocumentIcon from "@/Components/DocumentIcon.vue";

const props = defineProps({
    document: Object,
    categories: Array,
    can: Object,
});

// Initialize form with document data
const form = useForm({
    title: props.document.title,
    description: props.document.description || "",
    category: props.document.category || "",
    version: props.document.version || "1.0",
});

const confirmingDeletion = ref(false);

// Helper functions
const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const getRelatedType = (document) => {
    if (!document.documentable) return "";

    const type = document.documentable_type.split("\\").pop();
    return type;
};

const getRelatedName = (document) => {
    if (!document.documentable) return "";

    const type = getRelatedType(document);

    if (type === "Contact") {
        return `${document.documentable.first_name} ${document.documentable.last_name}`;
    } else {
        return document.documentable.name;
    }
};

function submit() {
    form.put(route("documents.update", props.document.id));
}

function confirmDelete() {
    confirmingDeletion.value = true;
}

function deleteDocument() {
    router.delete(route("documents.destroy", props.document.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        },
    });
}
</script>
