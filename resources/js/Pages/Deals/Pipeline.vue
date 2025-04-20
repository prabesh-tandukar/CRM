<!-- resources/js/Pages/Deals/Pipeline.vue -->
<template>
    <AppLayout title="Pipeline">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Deal Pipeline
                </h2>
                <div>
                    <Link
                        v-if="can.create"
                        :href="route('deals.create')"
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
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                            />
                        </svg>
                        New Deal
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filter Bar -->
                <div
                    class="mb-6 bg-white p-4 rounded-lg shadow flex flex-wrap gap-4 items-center"
                >
                    <div class="flex-1 min-w-[200px]">
                        <input
                            type="text"
                            v-model="filters.search"
                            placeholder="Search deals..."
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="filters.owner_id"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">All Owners</option>
                            <option
                                v-for="user in owners"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <select
                            v-model="filters.company_id"
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
                    </div>
                    <div>
                        <button
                            @click="getDeals"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
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
                        <button
                            @click="clearFilters"
                            class="ml-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Pipeline Statistics -->
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
                    >
                        <div>
                            <div class="text-sm font-medium text-gray-500">
                                Total Deals
                            </div>
                            <div class="text-2xl font-bold">
                                {{ dealStats.total_count }}
                            </div>
                            <div class="text-lg">
                                {{ formatCurrency(dealStats.total_value) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">
                                Qualification
                            </div>
                            <div class="text-2xl font-bold">
                                {{ dealStats.qualification_count }}
                            </div>
                            <div class="text-lg">
                                {{
                                    formatCurrency(
                                        dealStats.qualification_value
                                    )
                                }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">
                                Proposal
                            </div>
                            <div class="text-2xl font-bold">
                                {{ dealStats.proposal_count }}
                            </div>
                            <div class="text-lg">
                                {{ formatCurrency(dealStats.proposal_value) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">
                                Negotiation
                            </div>
                            <div class="text-2xl font-bold">
                                {{ dealStats.negotiation_count }}
                            </div>
                            <div class="text-lg">
                                {{
                                    formatCurrency(dealStats.negotiation_value)
                                }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">
                                Won Deals
                            </div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ dealStats.won_count }}
                            </div>
                            <div class="text-lg text-green-600">
                                {{ formatCurrency(dealStats.won_value) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pipeline Kanban Board -->
                <div class="overflow-x-auto pb-4">
                    <div class="flex gap-4 min-w-max">
                        <!-- Qualification Column -->
                        <div class="w-80 flex-shrink-0">
                            <div
                                class="bg-blue-50 p-3 rounded-t-lg border border-blue-200"
                            >
                                <h3 class="font-semibold text-blue-800">
                                    Qualification
                                </h3>
                                <div class="text-sm text-blue-600">
                                    {{ getStageDeals("Qualification").length }}
                                    deals ·
                                    {{
                                        formatCurrency(
                                            getStageValue("Qualification")
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-b-lg shadow overflow-hidden border-b border-l border-r border-blue-200 max-h-[calc(100vh-300px)] overflow-y-auto"
                            >
                                <div
                                    v-if="
                                        getStageDeals('Qualification')
                                            .length === 0
                                    "
                                    class="p-6 text-center text-gray-500"
                                >
                                    No deals in this stage
                                </div>
                                <div
                                    v-for="deal in getStageDeals(
                                        'Qualification'
                                    )"
                                    :key="deal.id"
                                    class="p-4 border-b border-gray-200 hover:bg-gray-50 last:border-b-0"
                                    draggable="true"
                                    @dragstart="dragStart($event, deal)"
                                >
                                    <DealCard :deal="deal" />
                                </div>
                                <div
                                    class="p-4 border-b border-gray-200 border-dashed text-center text-gray-400 hover:bg-blue-50"
                                    @dragover.prevent
                                    @drop="handleDrop($event, 'Qualification')"
                                >
                                    Drop deal here
                                </div>
                            </div>
                        </div>

                        <!-- Needs Analysis Column -->
                        <div class="w-80 flex-shrink-0">
                            <div
                                class="bg-indigo-50 p-3 rounded-t-lg border border-indigo-200"
                            >
                                <h3 class="font-semibold text-indigo-800">
                                    Needs Analysis
                                </h3>
                                <div class="text-sm text-indigo-600">
                                    {{ getStageDeals("Needs Analysis").length }}
                                    deals ·
                                    {{
                                        formatCurrency(
                                            getStageValue("Needs Analysis")
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-b-lg shadow overflow-hidden border-b border-l border-r border-indigo-200 max-h-[calc(100vh-300px)] overflow-y-auto"
                            >
                                <div
                                    v-if="
                                        getStageDeals('Needs Analysis')
                                            .length === 0
                                    "
                                    class="p-6 text-center text-gray-500"
                                >
                                    No deals in this stage
                                </div>
                                <div
                                    v-for="deal in getStageDeals(
                                        'Needs Analysis'
                                    )"
                                    :key="deal.id"
                                    class="p-4 border-b border-gray-200 hover:bg-gray-50 last:border-b-0"
                                    draggable="true"
                                    @dragstart="dragStart($event, deal)"
                                >
                                    <DealCard :deal="deal" />
                                </div>
                                <div
                                    class="p-4 border-b border-gray-200 border-dashed text-center text-gray-400 hover:bg-indigo-50"
                                    @dragover.prevent
                                    @drop="handleDrop($event, 'Needs Analysis')"
                                >
                                    Drop deal here
                                </div>
                            </div>
                        </div>

                        <!-- Proposal Column -->
                        <div class="w-80 flex-shrink-0">
                            <div
                                class="bg-purple-50 p-3 rounded-t-lg border border-purple-200"
                            >
                                <h3 class="font-semibold text-purple-800">
                                    Proposal
                                </h3>
                                <div class="text-sm text-purple-600">
                                    {{ getStageDeals("Proposal").length }} deals
                                    ·
                                    {{
                                        formatCurrency(
                                            getStageValue("Proposal")
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-b-lg shadow overflow-hidden border-b border-l border-r border-purple-200 max-h-[calc(100vh-300px)] overflow-y-auto"
                            >
                                <div
                                    v-if="
                                        getStageDeals('Proposal').length === 0
                                    "
                                    class="p-6 text-center text-gray-500"
                                >
                                    No deals in this stage
                                </div>
                                <div
                                    v-for="deal in getStageDeals('Proposal')"
                                    :key="deal.id"
                                    class="p-4 border-b border-gray-200 hover:bg-gray-50 last:border-b-0"
                                    draggable="true"
                                    @dragstart="dragStart($event, deal)"
                                >
                                    <deal-card :deal="deal" />
                                </div>
                                <div
                                    class="p-4 border-b border-gray-200 border-dashed text-center text-gray-400 hover:bg-purple-50"
                                    @dragover.prevent
                                    @drop="handleDrop($event, 'Proposal')"
                                >
                                    Drop deal here
                                </div>
                            </div>
                        </div>

                        <!-- Negotiation Column -->
                        <div class="w-80 flex-shrink-0">
                            <div
                                class="bg-yellow-50 p-3 rounded-t-lg border border-yellow-200"
                            >
                                <h3 class="font-semibold text-yellow-800">
                                    Negotiation
                                </h3>
                                <div class="text-sm text-yellow-600">
                                    {{ getStageDeals("Negotiation").length }}
                                    deals ·
                                    {{
                                        formatCurrency(
                                            getStageValue("Negotiation")
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-b-lg shadow overflow-hidden border-b border-l border-r border-yellow-200 max-h-[calc(100vh-300px)] overflow-y-auto"
                            >
                                <div
                                    v-if="
                                        getStageDeals('Negotiation').length ===
                                        0
                                    "
                                    class="p-6 text-center text-gray-500"
                                >
                                    No deals in this stage
                                </div>
                                <div
                                    v-for="deal in getStageDeals('Negotiation')"
                                    :key="deal.id"
                                    class="p-4 border-b border-gray-200 hover:bg-gray-50 last:border-b-0"
                                    draggable="true"
                                    @dragstart="dragStart($event, deal)"
                                >
                                    <deal-card :deal="deal" />
                                </div>
                                <div
                                    class="p-4 border-b border-gray-200 border-dashed text-center text-gray-400 hover:bg-yellow-50"
                                    @dragover.prevent
                                    @drop="handleDrop($event, 'Negotiation')"
                                >
                                    Drop deal here
                                </div>
                            </div>
                        </div>

                        <!-- Closed Won Column -->
                        <div class="w-80 flex-shrink-0">
                            <div
                                class="bg-green-50 p-3 rounded-t-lg border border-green-200"
                            >
                                <h3 class="font-semibold text-green-800">
                                    Closed Won
                                </h3>
                                <div class="text-sm text-green-600">
                                    {{ getStageDeals("Closed Won").length }}
                                    deals ·
                                    {{
                                        formatCurrency(
                                            getStageValue("Closed Won")
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-b-lg shadow overflow-hidden border-b border-l border-r border-green-200 max-h-[calc(100vh-300px)] overflow-y-auto"
                            >
                                <div
                                    v-if="
                                        getStageDeals('Closed Won').length === 0
                                    "
                                    class="p-6 text-center text-gray-500"
                                >
                                    No deals in this stage
                                </div>
                                <div
                                    v-for="deal in getStageDeals('Closed Won')"
                                    :key="deal.id"
                                    class="p-4 border-b border-gray-200 hover:bg-gray-50 last:border-b-0"
                                >
                                    <deal-card :deal="deal" />
                                </div>
                                <div
                                    class="p-4 border-b border-gray-200 border-dashed text-center text-gray-400 hover:bg-green-50"
                                    @dragover.prevent
                                    @drop="handleDrop($event, 'Closed Won')"
                                >
                                    Drop deal here
                                </div>
                            </div>
                        </div>

                        <!-- Closed Lost Column -->
                        <div class="w-80 flex-shrink-0">
                            <div
                                class="bg-red-50 p-3 rounded-t-lg border border-red-200"
                            >
                                <h3 class="font-semibold text-red-800">
                                    Closed Lost
                                </h3>
                                <div class="text-sm text-red-600">
                                    {{ getStageDeals("Closed Lost").length }}
                                    deals ·
                                    {{
                                        formatCurrency(
                                            getStageValue("Closed Lost")
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="bg-white rounded-b-lg shadow overflow-hidden border-b border-l border-r border-red-200 max-h-[calc(100vh-300px)] overflow-y-auto"
                            >
                                <div
                                    v-if="
                                        getStageDeals('Closed Lost').length ===
                                        0
                                    "
                                    class="p-6 text-center text-gray-500"
                                >
                                    No deals in this stage
                                </div>
                                <div
                                    v-for="deal in getStageDeals('Closed Lost')"
                                    :key="deal.id"
                                    class="p-4 border-b border-gray-200 hover:bg-gray-50 last:border-b-0"
                                >
                                    <deal-card :deal="deal" />
                                </div>
                                <div
                                    class="p-4 border-b border-gray-200 border-dashed text-center text-gray-400 hover:bg-red-50"
                                    @dragover.prevent
                                    @drop="handleDrop($event, 'Closed Lost')"
                                >
                                    Drop deal here
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deal Stage Update Modal -->
        <Modal :show="showUpdateModal" @close="closeUpdateModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Update Deal Stage
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to move the deal "{{
                        selectedDeal ? selectedDeal.name : ""
                    }}" to {{ newStage }}?
                </p>

                <!-- Loss Reason (only for Closed Lost) -->
                <div v-if="newStage === 'Closed Lost'" class="mt-4">
                    <InputLabel
                        for="lost_reason"
                        value="Loss Reason (Required)"
                    />
                    <textarea
                        id="lost_reason"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="stageForm.lost_reason"
                        rows="3"
                        required
                    ></textarea>
                    <InputError
                        class="mt-2"
                        :message="stageForm.errors.lost_reason"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeUpdateModal" class="mr-3">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        :class="{ 'opacity-25': stageForm.processing }"
                        :disabled="
                            stageForm.processing ||
                            (newStage === 'Closed Lost' &&
                                !stageForm.lost_reason)
                        "
                        @click="updateDealStage"
                    >
                        Update
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DealCard from "@/Components/DealCard.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    deals: Array,
    owners: Array,
    companies: Array,
    dealStats: Object,
    filters: Object,
    can: Object,
});

// Form and state
const filters = ref({
    search: props.filters.search || "",
    owner_id: props.filters.owner_id || "",
    company_id: props.filters.company_id || "",
});

// Drag and drop functionality
const draggedDeal = ref(null);
const selectedDeal = ref(null);
const newStage = ref("");
const showUpdateModal = ref(false);

const stageForm = useForm({
    pipeline_stage: "",
    lost_reason: "",
});

function dragStart(event, deal) {
    draggedDeal.value = deal;
    event.dataTransfer.effectAllowed = "move";
    event.dataTransfer.setData("text/plain", deal.id);
}

function handleDrop(event, stage) {
    event.preventDefault();

    if (!draggedDeal.value || draggedDeal.value.pipeline_stage === stage) {
        return;
    }

    // Set values for confirmation modal
    selectedDeal.value = draggedDeal.value;
    newStage.value = stage;
    showUpdateModal.value = true;
}

function closeUpdateModal() {
    showUpdateModal.value = false;
    selectedDeal.value = null;
    newStage.value = "";
    stageForm.reset();
}

function updateDealStage() {
    if (!selectedDeal.value || !newStage.value) return;

    stageForm.pipeline_stage = newStage.value;

    stageForm.post(route("deals.change-status", selectedDeal.value.id), {
        onSuccess: () => {
            closeUpdateModal();
            // Refresh the deals to update the UI
            getDeals();
        },
    });
}

// Helper functions
function getStageDeals(stage) {
    return props.deals.filter((deal) => deal.pipeline_stage === stage);
}

function getStageValue(stage) {
    return getStageDeals(stage).reduce(
        (sum, deal) => sum + (parseFloat(deal.amount) || 0),
        0
    );
}

function formatCurrency(value) {
    if (!value) return "$0";
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

function formatDate(date) {
    if (!date) return "";
    const d = new Date(date);
    return d.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
    });
}

// Search and filtering
const debouncedSearch = debounce(() => {
    getDeals();
}, 300);

function getDeals() {
    router.get(route("deals.pipeline"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    filters.value = {
        search: "",
        owner_id: "",
        company_id: "",
    };
    getDeals();
}
</script>
