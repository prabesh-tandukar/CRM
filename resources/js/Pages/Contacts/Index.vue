<!-- resources/js/Pages/Contacts/Index.vue -->
<template>
    <AppLayout title="Contacts">
        <template #header-title>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Contacts
            </h2>
        </template>
        <template #header-actions>
            <Link
                v-if="can.create"
                :href="route('contacts.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 mr-2"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Add Contact
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 bg-white rounded-lg shadow p-4">
                    <div
                        class="flex flex-wrap items-center justify-between gap-4"
                    >
                        <!-- Search input -->
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search contacts..."
                                    class="w-full pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @input="debouncedSearch"
                                />
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Filter dropdowns -->
                        <div class="flex flex-wrap items-center gap-3">
                            <select
                                v-model="filters.companyId"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @change="filterContacts"
                            >
                                <option :value="null">All Companies</option>
                                <option
                                    v-for="company in companies"
                                    :key="company.id"
                                    :value="company.id"
                                >
                                    {{ company.name }}
                                </option>
                            </select>

                            <select
                                v-model="filters.perPage"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @change="filterContacts"
                            >
                                <option :value="10">10 per page</option>
                                <option :value="25">25 per page</option>
                                <option :value="50">50 per page</option>
                                <option :value="100">100 per page</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contacts Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    <button
                                        class="flex items-center"
                                        @click="sortContacts('full_name')"
                                    >
                                        Name
                                        <svg
                                            v-if="
                                                filters.sortField ===
                                                'full_name'
                                            "
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 ml-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                v-if="
                                                    filters.sortDirection ===
                                                    'asc'
                                                "
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    <button
                                        class="flex items-center"
                                        @click="sortContacts('email')"
                                    >
                                        Email
                                        <svg
                                            v-if="filters.sortField === 'email'"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 ml-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                v-if="
                                                    filters.sortDirection ===
                                                    'asc'
                                                "
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    <button
                                        class="flex items-center"
                                        @click="sortContacts('phone')"
                                    >
                                        Phone
                                        <svg
                                            v-if="filters.sortField === 'phone'"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 ml-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                v-if="
                                                    filters.sortDirection ===
                                                    'asc'
                                                "
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Company
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Owner
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="contact in contacts.data"
                                :key="contact.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 rounded-full"
                                        >
                                            {{ contact.first_name.charAt(0)
                                            }}{{ contact.last_name.charAt(0) }}
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="font-medium text-gray-900"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'contacts.show',
                                                            contact.id
                                                        )
                                                    "
                                                    class="hover:text-blue-600"
                                                >
                                                    {{ contact.first_name }}
                                                    {{ contact.last_name }}
                                                </Link>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ contact.job_title }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ contact.email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ contact.phone }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="contact.company"
                                        class="text-sm text-gray-900"
                                    >
                                        {{ contact.company.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">
                                        -
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="contact.owner"
                                        class="text-sm text-gray-900"
                                    >
                                        {{ contact.owner.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">
                                        -
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div
                                        class="flex items-center justify-end space-x-2"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'contacts.show',
                                                    contact.id
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-900"
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
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="can.edit"
                                            :href="
                                                route(
                                                    'contacts.edit',
                                                    contact.id
                                                )
                                            "
                                            class="text-amber-600 hover:text-amber-900"
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
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="can.delete"
                                            @click="confirmDelete(contact)"
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
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="contacts.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-4 text-center text-gray-500"
                                >
                                    No contacts found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4" v-if="contacts.data.length > 0">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ contacts.meta?.from || 0 }} to
                            {{ contacts.meta?.to || 0 }} of
                            {{ contacts.meta?.total || 0 }} contacts
                        </div>
                        <div class="flex space-x-2" v-if="contacts.meta?.links">
                            <Link
                                v-for="link in contacts.meta.links"
                                :key="link.url"
                                :href="link.url"
                                class="px-3 py-1 border rounded-md text-sm"
                                :class="{
                                    'bg-blue-50 border-blue-500 text-blue-600':
                                        link.active,
                                    'border-gray-300 text-gray-600 hover:bg-gray-50':
                                        !link.active && link.url !== null,
                                    'border-gray-200 text-gray-400 cursor-not-allowed':
                                        link.url === null,
                                }"
                                v-html="link.label"
                            ></Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="deleteModal.isOpen"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        >
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div
                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-red-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                </div>
                                <div
                                    class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left"
                                >
                                    <h3
                                        class="text-lg font-medium leading-6 text-gray-900"
                                    >
                                        Delete Contact
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to delete
                                            {{
                                                deleteModal.contact?.first_name
                                            }}
                                            {{
                                                deleteModal.contact?.last_name
                                            }}? This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                        >
                            <button
                                type="button"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                @click="deleteContact"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                @click="deleteModal.isOpen = false"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    contacts: Object,
    companies: Array,
    filters: Object,
    can: Object,
});

// Search and filters
const search = ref(props.filters.search || "");
const filters = ref({
    perPage: props.filters.perPage || 10,
    sortField: props.filters.sortField || "created_at",
    sortDirection: props.filters.sortDirection || "desc",
    companyId: props.filters.companyId || null,
});

// Delete confirmation
const deleteModal = ref({
    isOpen: false,
    contact: null,
});

// Methods
const debouncedSearch = debounce(() => {
    filterContacts();
}, 300);

const filterContacts = () => {
    router.get(
        route("contacts.index"),
        {
            search: search.value,
            perPage: filters.value.perPage,
            sortField: filters.value.sortField,
            sortDirection: filters.value.sortDirection,
            company_id: filters.value.companyId,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const sortContacts = (field) => {
    filters.value.sortDirection =
        field === filters.value.sortField &&
        filters.value.sortDirection === "asc"
            ? "desc"
            : "asc";
    filters.value.sortField = field;
    filterContacts();
};

const confirmDelete = (contact) => {
    deleteModal.value.contact = contact;
    deleteModal.value.isOpen = true;
};

const deleteContact = () => {
    router.delete(route("contacts.destroy", deleteModal.value.contact.id), {
        onSuccess: () => {
            deleteModal.value.isOpen = false;
        },
    });
};
</script>
