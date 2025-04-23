<!-- resources/js/Pages/Deals/Index.vue -->
<template>
    <AppLayout title="Deals">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Deals
                </h2>
            </div>
        </template>
        <template #header-actions
            ><div v-if="can.create">
                <Link
                    :href="route('deals.create')"
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
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        />
                    </svg>
                    Create Deal
                </Link>
            </div></template
        >

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8"
                >
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-indigo-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">
                                    Total Deals
                                </div>
                                <div
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    {{ stats.total }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-blue-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">
                                    Open Deals
                                </div>
                                <div
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    {{ stats.open }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">
                                    Won Deals
                                </div>
                                <div
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    {{ stats.won }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 bg-yellow-100 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-yellow-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">
                                    Pipeline Value
                                </div>
                                <div
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    {{ formatCurrency(stats.value) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div
                            class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4"
                        >
                            <div class="flex-1">
                                <input
                                    type="text"
                                    v-model="filters.search"
                                    placeholder="Search deals..."
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @input="debouncedSearch"
                                />
                            </div>
                            <div
                                class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2"
                            >
                                <select
                                    v-model="filters.status"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Statuses</option>
                                    <option
                                        v-for="status in statuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ status }}
                                    </option>
                                </select>

                                <select
                                    v-model="filters.stage"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Stages</option>
                                    <option
                                        v-for="stage in stages"
                                        :key="stage"
                                        :value="stage"
                                    >
                                        {{ stage }}
                                    </option>
                                </select>

                                <select
                                    v-model="filters.companyId"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Companies</option>
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
                                >
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>

                                <button
                                    @click="getDeals"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 transition"
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
                                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                        />
                                    </svg>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deals Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                            @click="sort('name')"
                                        >
                                            <div class="flex items-center">
                                                Deal Name
                                                <SortIcon
                                                    :field="'name'"
                                                    :current-sort="
                                                        filters.sortField
                                                    "
                                                    :direction="
                                                        filters.sortDirection
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                            @click="sort('company_id')"
                                        >
                                            <div class="flex items-center">
                                                Company
                                                <SortIcon
                                                    :field="'company_id'"
                                                    :current-sort="
                                                        filters.sortField
                                                    "
                                                    :direction="
                                                        filters.sortDirection
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                            @click="sort('amount')"
                                        >
                                            <div class="flex items-center">
                                                Amount
                                                <SortIcon
                                                    :field="'amount'"
                                                    :current-sort="
                                                        filters.sortField
                                                    "
                                                    :direction="
                                                        filters.sortDirection
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                            @click="sort('pipeline_stage')"
                                        >
                                            <div class="flex items-center">
                                                Stage
                                                <SortIcon
                                                    :field="'pipeline_stage'"
                                                    :current-sort="
                                                        filters.sortField
                                                    "
                                                    :direction="
                                                        filters.sortDirection
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                            @click="sort('expected_close_date')"
                                        >
                                            <div class="flex items-center">
                                                Close Date
                                                <SortIcon
                                                    :field="'expected_close_date'"
                                                    :current-sort="
                                                        filters.sortField
                                                    "
                                                    :direction="
                                                        filters.sortDirection
                                                    "
                                                />
                                            </div>
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Owner
                                        </th>
                                        <th
                                            scope="col"
                                            class="relative px-6 py-3"
                                        >
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="deal in deals.data"
                                        :key="deal.id"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div
                                                        class="text-sm font-medium text-gray-900"
                                                    >
                                                        <Link
                                                            :href="
                                                                route(
                                                                    'deals.show',
                                                                    deal.id
                                                                )
                                                            "
                                                            class="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            {{ deal.name }}
                                                        </Link>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <Link
                                                    v-if="deal.company"
                                                    :href="
                                                        route(
                                                            'companies.show',
                                                            deal.company.id
                                                        )
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    {{ deal.company.name }}
                                                </Link>
                                                <span v-else>-</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{
                                                    formatCurrency(deal.amount)
                                                }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="
                                                    getStageClass(
                                                        deal.pipeline_stage
                                                    )
                                                "
                                            >
                                                {{ deal.pipeline_stage }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        >
                                            {{
                                                formatDate(
                                                    deal.expected_close_date
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        >
                                            {{
                                                deal.owner
                                                    ? deal.owner.name
                                                    : "Unassigned"
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <div
                                                class="flex justify-end space-x-2"
                                            >
                                                <Link
                                                    v-if="can.edit"
                                                    :href="
                                                        route(
                                                            'deals.edit',
                                                            deal.id
                                                        )
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900"
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
                                                    @click="
                                                        confirmDealDeletion(
                                                            deal
                                                        )
                                                    "
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
                                    <tr v-if="deals.data.length === 0">
                                        <td
                                            colspan="7"
                                            class="px-6 py-4 text-center text-gray-500"
                                        >
                                            No deals found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="deals.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDealDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this deal?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Once this deal is deleted, all of its resources and data
                    will be permanently deleted.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteDeal"
                    >
                        Delete Deal
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
// import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    deals: Object,
    filters: Object,
    stats: Object,
    companies: Array,
    stages: Array,
    statuses: Array,
    can: Object,
});

const search = ref(props.filters.search);
const filters = ref({
    search: props.filters.search,
    status: props.filters.status,
    stage: props.filters.stage,
    companyId: props.filters.companyId,
    perPage: props.filters.perPage,
    sortField: props.filters.sortField,
    sortDirection: props.filters.sortDirection,
});

const confirmingDealDeletion = ref(false);
const dealToDelete = ref(null);

const form = useForm({});

// Watch for search input changes and debounce
watch(
    search,
    debounce(function (value) {
        filters.value.search = value;
        getDeals();
    }, 300)
);

function getDeals() {
    router.get(
        route("deals.index"),
        {
            search: filters.value.search,
            status: filters.value.status,
            stage: filters.value.stage,
            company_id: filters.value.companyId,
            per_page: filters.value.perPage,
            sort_field: filters.value.sortField,
            sort_direction: filters.value.sortDirection,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function sort(field) {
    if (filters.value.sortField === field) {
        filters.value.sortDirection =
            filters.value.sortDirection === "asc" ? "desc" : "asc";
    } else {
        filters.value.sortField = field;
        filters.value.sortDirection = "asc";
    }
    getDeals();
}

function confirmDealDeletion(deal) {
    dealToDelete.value = deal;
    confirmingDealDeletion.value = true;
}

function closeModal() {
    confirmingDealDeletion.value = false;
    setTimeout(() => {
        dealToDelete.value = null;
    }, 300);
}

function deleteDeal() {
    form.delete(route("deals.destroy", dealToDelete.value.id), {
        onSuccess: () => {
            closeModal();
        },
    });
}

function formatDate(date) {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
}

function formatCurrency(value) {
    if (!value) return "$0.00";
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
}

function getStageClass(stage) {
    const stageClasses = {
        Qualification: "bg-blue-100 text-blue-800",
        "Needs Analysis": "bg-indigo-100 text-indigo-800",
        Proposal: "bg-purple-100 text-purple-800",
        Negotiation: "bg-yellow-100 text-yellow-800",
        "Closed Won": "bg-green-100 text-green-800",
        "Closed Lost": "bg-red-100 text-red-800",
    };

    return stageClasses[stage] || "bg-gray-100 text-gray-800";
}

// SortIcon component
const SortIcon = {
    props: ["field", "currentSort", "direction"],
    template: `
        <svg v-if="currentSort === field && direction === 'asc'" class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
        <svg v-else-if="currentSort === field && direction === 'desc'" class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
        <svg v-else class="ml-1 w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
        </svg>
    `,
};
</script>
