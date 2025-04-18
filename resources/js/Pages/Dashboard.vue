<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Stats Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                >
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="text-gray-500 text-sm font-medium">
                            Total Contacts
                        </div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">
                                {{ counts.contacts }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="text-gray-500 text-sm font-medium">
                            Active Leads
                        </div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">
                                {{ counts.leads }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="text-gray-500 text-sm font-medium">
                            Open Deals
                        </div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">
                                {{ counts.deals }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="text-gray-500 text-sm font-medium">
                            Deals Won
                        </div>
                        <div class="mt-2 flex items-baseline">
                            <div class="text-3xl font-semibold text-gray-900">
                                {{ counts.deals_won }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pipeline Stats -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6"
                >
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Sales Pipeline
                    </h3>

                    <div class="space-y-4">
                        <div
                            v-for="stage in pipelineData"
                            :key="stage.pipeline_stage"
                            class="w-full"
                        >
                            <div class="flex justify-between mb-1">
                                <span
                                    class="text-sm font-medium text-gray-700"
                                    >{{ stage.pipeline_stage }}</span
                                >
                                <span class="text-sm font-medium text-gray-700">
                                    {{ stage.count }} deals |
                                    {{ formatCurrency(stage.value) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div
                                    class="bg-blue-600 h-2.5 rounded-full"
                                    :style="{
                                        width:
                                            calculatePercentage(stage.value) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Sales Performance Chart -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Sales Performance
                        </h3>
                        <div class="h-64">
                            <canvas ref="salesChart"></canvas>
                        </div>
                    </div>

                    <!-- Upcoming Tasks -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Upcoming Tasks
                            </h3>
                            <Link
                                href="/tasks"
                                class="text-sm text-blue-600 hover:text-blue-800"
                                >View All</Link
                            >
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in upcomingTasks"
                                :key="task.id"
                                class="border-b border-gray-200 pb-3 last:border-b-0"
                            >
                                <div class="flex justify-between">
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ task.title }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <span
                                                >Due:
                                                {{
                                                    formatDate(task.due_date)
                                                }}</span
                                            >
                                            <span class="mx-1">•</span>
                                            <span
                                                >{{
                                                    task.priority
                                                }}
                                                Priority</span
                                            >
                                        </p>
                                    </div>
                                    <div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800':
                                                    task.status ===
                                                    'In Progress',
                                                'bg-blue-100 text-blue-800':
                                                    task.status ===
                                                    'Not Started',
                                                'bg-red-100 text-red-800':
                                                    task.status === 'Overdue',
                                            }"
                                        >
                                            {{ task.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="upcomingTasks.length === 0"
                                class="text-sm text-gray-500 text-center py-4"
                            >
                                No upcoming tasks found
                            </div>
                        </div>
                    </div>

                    <!-- Recent Leads -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Recent Leads
                            </h3>
                            <Link
                                href="/leads"
                                class="text-sm text-blue-600 hover:text-blue-800"
                                >View All</Link
                            >
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="lead in recentLeads"
                                :key="lead.id"
                                class="border-b border-gray-200 pb-3 last:border-b-0"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ lead.first_name }}
                                        {{ lead.last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ lead.company_name || "No Company" }}
                                        <span class="mx-1">•</span>
                                        <span>{{
                                            lead.email || "No Email"
                                        }}</span>
                                    </p>
                                </div>
                                <div
                                    class="flex justify-between items-center mt-1"
                                >
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ lead.lead_status }}
                                    </span>
                                    <Link
                                        :href="`/leads/${lead.id}`"
                                        class="text-xs text-blue-600 hover:text-blue-800"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>

                            <div
                                v-if="recentLeads.length === 0"
                                class="text-sm text-gray-500 text-center py-4"
                            >
                                No recent leads found
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Chart from "chart.js/auto";

const props = defineProps({
    counts: Object,
    pipelineData: Array,
    monthlyData: Array,
    upcomingTasks: Array,
    recentLeads: Array,
});

const salesChart = ref(null);
let chart = null;

onMounted(() => {
    // Initialize sales performance chart
    if (salesChart.value) {
        const ctx = salesChart.value.getContext("2d");

        chart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: props.monthlyData.map((d) => d.month),
                datasets: [
                    {
                        label: "Sales Value",
                        data: props.monthlyData.map((d) => d.value),
                        backgroundColor: "rgba(59, 130, 246, 0.5)",
                        borderColor: "rgb(59, 130, 246)",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }
});

// Helper functions
const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const calculatePercentage = (value) => {
    const maxValue = Math.max(
        ...props.pipelineData.map((stage) => stage.value || 0)
    );
    if (maxValue === 0) return 0;
    return Math.round((value / maxValue) * 100);
};
</script>
