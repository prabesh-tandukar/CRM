<!-- resources/js/Pages/Tasks/Index.vue -->
<template>
    <AppLayout title="Tasks">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Tasks
                </h2>
                <Link
                    v-if="can.create"
                    :href="route('tasks.create')"
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
                    New Task
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Task Statistics -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-sm text-gray-500">Total Tasks</div>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-sm text-gray-500">Completed</div>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.completed }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-sm text-gray-500">Due Today</div>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ stats.today }}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-sm text-gray-500">Overdue</div>
                        <div class="text-2xl font-bold text-red-600">
                            {{ stats.overdue }}
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex-1 min-w-[200px]">
                            <input
                                type="text"
                                v-model="filters.search"
                                placeholder="Search tasks..."
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @input="debouncedSearch"
                            />
                        </div>
                        <div>
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
                        </div>
                        <div>
                            <select
                                v-model="filters.assigneeId"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">All Assignees</option>
                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="filters.dueDate"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">All Due Dates</option>
                                <option
                                    v-for="(label, value) in dueDateOptions"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <button
                                @click="getTasks"
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

                <!-- Tasks Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Task
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Due Date
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Assignee
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Related To
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Priority
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="task in tasks.data" :key="task.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :checked="
                                                task.status === 'Completed'
                                            "
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @change="updateTaskStatus(task)"
                                        />
                                        <span
                                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800':
                                                    task.status === 'Completed',
                                                'bg-yellow-100 text-yellow-800':
                                                    task.status ===
                                                    'In Progress',
                                                'bg-red-100 text-red-800':
                                                    isOverdue(task),
                                                'bg-blue-100 text-blue-800':
                                                    task.status ===
                                                        'Not Started' &&
                                                    !isOverdue(task),
                                                'bg-gray-100 text-gray-800':
                                                    task.status === 'Deferred',
                                            }"
                                        >
                                            {{
                                                isOverdue(task) &&
                                                task.status !== "Completed"
                                                    ? "Overdue"
                                                    : task.status
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ task.title }}
                                    </div>
                                    <div
                                        v-if="task.description"
                                        class="text-sm text-gray-500 truncate max-w-xs"
                                    >
                                        {{ task.description }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    <span
                                        :class="{
                                            'text-red-600 font-medium':
                                                isOverdue(task) &&
                                                task.status !== 'Completed',
                                            'text-blue-600 font-medium':
                                                isDueToday(task) &&
                                                task.status !== 'Completed',
                                        }"
                                    >
                                        {{ formatDate(task.due_date) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{
                                        task.assignee
                                            ? task.assignee.name
                                            : "Unassigned"
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    <div v-if="task.taskable">
                                        <span class="text-xs text-gray-500">{{
                                            getTaskableType(task)
                                        }}</span>
                                        <div
                                            class="text-sm text-indigo-600 hover:text-indigo-900"
                                        >
                                            <Link :href="getTaskableLink(task)">
                                                {{ getTaskableName(task) }}
                                            </Link>
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-red-100 text-red-800':
                                                task.priority === 'High',
                                            'bg-yellow-100 text-yellow-800':
                                                task.priority === 'Medium',
                                            'bg-blue-100 text-blue-800':
                                                task.priority === 'Low',
                                        }"
                                    >
                                        {{ task.priority }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <Link
                                        :href="route('tasks.show', task.id)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-2"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        v-if="can.edit"
                                        :href="route('tasks.edit', task.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="tasks.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-4 text-center text-gray-500"
                                >
                                    No tasks found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <Pagination :links="tasks.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import Pagination from "@/Components/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    tasks: Object,
    users: Array,
    statuses: Array,
    dueDateOptions: Object,
    filters: Object,
    stats: Object,
    can: Object,
});

const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    assigneeId: props.filters.assigneeId || "",
    dueDate: props.filters.dueDate || "",
    perPage: props.filters.perPage || 10,
});

// Helper functions
const formatDate = (dateString) => {
    if (!dateString) return "No due date";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const isOverdue = (task) => {
    if (!task.due_date || task.status === "Completed") return false;
    const dueDate = new Date(task.due_date);
    dueDate.setHours(23, 59, 59, 999); // End of day
    return dueDate < new Date();
};

const isDueToday = (task) => {
    if (!task.due_date) return false;
    const dueDate = new Date(task.due_date);
    const today = new Date();
    return dueDate.toDateString() === today.toDateString();
};

const getTaskableType = (task) => {
    if (!task.taskable) return "";
    const type = task.taskable_type.split("\\").pop();
    return type;
};

const getTaskableName = (task) => {
    if (!task.taskable) return "";

    const type = getTaskableType(task);

    if (type === "Contact") {
        return `${task.taskable.first_name} ${task.taskable.last_name}`;
    } else {
        return task.taskable.name;
    }
};

const getTaskableLink = (task) => {
    if (!task.taskable) return "#";

    const type = getTaskableType(task);
    const routeMap = {
        Deal: "deals.show",
        Contact: "contacts.show",
        Company: "companies.show",
    };

    return route(routeMap[type], task.taskable.id);
};

const updateTaskStatus = (task) => {
    const newStatus = task.status === "Completed" ? "Not Started" : "Completed";

    router.post(
        route("tasks.update-status", task.id),
        {
            status: newStatus,
        },
        {
            preserveScroll: true,
        }
    );
};

// Search and filtering
const debouncedSearch = debounce(() => {
    getTasks();
}, 300);

const getTasks = () => {
    router.get(route("tasks.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        assigneeId: "",
        dueDate: "",
        perPage: 10,
    };
    getTasks();
};
</script>
