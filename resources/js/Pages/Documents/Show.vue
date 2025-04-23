<!-- resources/js/Pages/Documents/Show.vue -->
<template>
    <AppLayout :title="document.title">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Document Details
                </h2>
            </div>
        </template>
        <template #header-actions>
            <div class="flex space-x-2">
                <Link
                    :href="downloadUrl"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                        />
                    </svg>
                    Download
                </Link>
                <Link
                    :href="route('documents.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Back to Documents
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div
                    v-if="$page.props.flash && $page.props.flash.success"
                    class="mb-6"
                >
                    <div
                        class="p-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"
                    >
                        {{ $page.props.flash.success }}
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Document Preview -->
                        <div class="md:col-span-1">
                            <div class="flex flex-col items-center">
                                <div class="h-32 w-32 mb-4">
                                    <DocumentIcon
                                        :fileType="document.file_type"
                                    />
                                </div>
                                <Link
                                    :href="downloadUrl"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        />
                                    </svg>
                                    Download
                                </Link>
                                <!-- In the Show.vue document, add this button next to the Download button -->
                                <Link
                                    v-if="
                                        (document.uploaded_by &&
                                            document.uploaded_by.id ===
                                                $page.props.auth.user.id) ||
                                        $page.props.auth.user.roles.includes(
                                            'Admin'
                                        )
                                    "
                                    :href="route('documents.edit', document.id)"
                                    class="mt-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    Edit Details
                                </Link>
                                <button
                                    v-if="can.delete"
                                    @click="confirmDelete"
                                    class="mt-2 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full justify-center"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Document Details -->
                        <div class="md:col-span-3">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                                {{ document.title }}
                            </h2>

                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"
                            >
                                <div>
                                    <h3
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        File Information
                                    </h3>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >Filename:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                document.original_filename
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >File Type:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                getFileTypeName(
                                                    document.file_type
                                                )
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >File Size:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                formatFileSize(
                                                    document.file_size
                                                )
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >Version:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                document.version || "1.0"
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >Category:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                document.category ||
                                                "Uncategorized"
                                            }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Document Details
                                    </h3>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >Uploaded By:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                document.uploaded_by
                                                    ? document.uploaded_by.name
                                                    : "Unknown"
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500"
                                                >Upload Date:</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                formatDate(document.created_at)
                                            }}</span>
                                        </div>
                                        <div
                                            v-if="document.documentable"
                                            class="flex justify-between"
                                        >
                                            <span class="text-sm text-gray-500"
                                                >Related To:</span
                                            >
                                            <Link
                                                :href="getRelatedLink(document)"
                                                class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ getRelatedType(document) }}:
                                                {{ getRelatedName(document) }}
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="document.description" class="mt-4">
                                <h3
                                    class="text-sm font-medium text-gray-500 mb-2"
                                >
                                    Description
                                </h3>
                                <p class="text-gray-700 whitespace-pre-line">
                                    {{ document.description }}
                                </p>
                            </div>
                        </div>
                    </div>
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
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import DocumentIcon from "@/Components/DocumentIcon.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    document: Object,
    downloadUrl: String,
    can: Object,
});

const confirmingDeletion = ref(false);

// Helper functions
const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const getFileTypeName = (mimeType) => {
    if (!mimeType) return "Unknown";

    const mimeTypeMap = {
        "application/pdf": "PDF Document",
        "application/msword": "Word Document",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            "Word Document",
        "application/vnd.ms-excel": "Excel Spreadsheet",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            "Excel Spreadsheet",
        "application/vnd.ms-powerpoint": "PowerPoint Presentation",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation":
            "PowerPoint Presentation",
        "text/plain": "Text Document",
        "text/csv": "CSV File",
        "application/zip": "ZIP Archive",
        "application/x-rar-compressed": "RAR Archive",
    };

    if (mimeType.startsWith("image/")) {
        return "Image";
    }

    return mimeTypeMap[mimeType] || mimeType;
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

const getRelatedLink = (document) => {
    if (!document.documentable) return "#";

    const type = getRelatedType(document);
    const routeMap = {
        Deal: "deals.show",
        Contact: "contacts.show",
        Company: "companies.show",
    };

    return route(routeMap[type], document.documentable.id);
};

const confirmDelete = () => {
    confirmingDeletion.value = true;
};

const deleteDocument = () => {
    router.delete(route("documents.destroy", props.document.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        },
    });
};
</script>
