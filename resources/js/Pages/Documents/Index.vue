<!-- resources/js/Pages/Documents/Index.vue -->
<template>
    <AppLayout title="Documents">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Documents
                </h2>
                <Link
                    v-if="can.upload"
                    :href="route('documents.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                        />
                    </svg>
                    Upload Document
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex-1 min-w-[200px]">
                            <input
                                type="text"
                                v-model="filters.search"
                                placeholder="Search documents..."
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @input="debouncedSearch"
                            />
                        </div>
                        <div>
                            <select
                                v-model="filters.category"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">All Categories</option>
                                <option
                                    v-for="category in categories"
                                    :key="category"
                                    :value="category"
                                >
                                    {{ category }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="filters.type"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">All Types</option>
                                <option
                                    v-for="(label, value) in typeOptions"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <button
                                @click="getDocuments"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            >
                                Filter
                            </button>
                            <button
                                @click="clearFilters"
                                class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Document Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="document in documents.data"
                        :key="document.id"
                        class="bg-white rounded-lg shadow overflow-hidden"
                    >
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <DocumentIcon
                                            :fileType="document.file_type"
                                        />
                                    </div>
                                    <div class="ml-3">
                                        <Link
                                            :href="
                                                route(
                                                    'documents.show',
                                                    document.id
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900 font-medium truncate"
                                        >
                                            {{ document.title }}
                                        </Link>
                                        <div
                                            class="text-xs text-gray-500 truncate"
                                        >
                                            {{ document.original_filename }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <Link
                                        :href="
                                            route(
                                                'documents.download',
                                                document.id
                                            )
                                        "
                                        class="text-gray-400 hover:text-gray-500"
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
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>

                            <div class="text-sm text-gray-500 mt-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        {{ formatFileSize(document.file_size) }}
                                    </div>
                                    <div>
                                        {{ formatDate(document.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center">
                                <div class="flex-1">
                                    <div
                                        v-if="document.category"
                                        class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-md inline-block"
                                    >
                                        {{ document.category }}
                                    </div>
                                </div>
                                <div
                                    v-if="document.documentable"
                                    class="text-xs text-right"
                                >
                                    <span class="text-gray-500"
                                        >{{ getRelatedType(document) }}:</span
                                    >
                                    <Link
                                        :href="getRelatedLink(document)"
                                        class="text-indigo-600 hover:text-indigo-900 ml-1"
                                    >
                                        {{ getRelatedName(document) }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="documents.data.length === 0"
                        class="md:col-span-3 bg-white rounded-lg shadow p-6 text-center text-gray-500"
                    >
                        No documents found
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <Pagination :links="documents.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import DocumentIcon from "@/Components/DocumentIcon.vue"; // We'll create this component next

const props = defineProps({
    documents: Object,
    categories: Array,
    typeOptions: Object,
    filters: Object,
    can: Object,
});

const filters = ref({
    search: props.filters.search || "",
    category: props.filters.category || "",
    type: props.filters.type || "",
    perPage: props.filters.perPage || 10,
});

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

// Search and filtering
const debouncedSearch = debounce(() => {
    getDocuments();
}, 300);

const getDocuments = () => {
    router.get(route("documents.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: "",
        category: "",
        type: "",
        perPage: 10,
    };
    getDocuments();
};
</script>
