<!-- resources/js/Pages/Tasks/Show.vue -->
<template>
    <AppLayout :title="task.title">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Task Details
                </h2>
                <div class="flex space-x-2">
                    <Link
                        v-if="can.edit"
                        :href="route('tasks.edit', task.id)"
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
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Edit Task
                    </Link>
                    <Link
                        :href="route('tasks.index')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        Back to Tasks
                    </Link>
                </div>
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Task Details -->
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-medium text-gray-900">
                                    {{ task.title }}
                                </h3>
                                <div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                task.status === 'Completed',
                                            'bg-yellow-100 text-yellow-800':
                                                task.status === 'In Progress',
                                            'bg-red-100 text-red-800':
                                                isOverdue(task),
                                            'bg-blue-100 text-blue-800':
                                                task.status === 'Not Started' &&
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
                            </div>

                            <div
                                class="prose max-w-none mb-6"
                                v-if="task.description"
                            >
                                <p class="whitespace-pre-wrap">
                                    {{ task.description }}
                                </p>
                            </div>

                            <div v-if="task.taskable" class="mb-6">
                                <h4
                                    class="text-sm font-medium text-gray-500 mb-2"
                                >
                                    Related To
                                </h4>
                                <div class="flex items-center">
                                    <span
                                        class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-md"
                                        >{{ getTaskableType(task) }}</span
                                    >
                                    <Link
                                        :href="getTaskableLink(task)"
                                        class="ml-2 text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ getTaskableName(task) }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Task Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-4">
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Status
                                    </h4>
                                    <div class="flex items-center mt-1">
                                        <input
                                            type="checkbox"
                                            :checked="
                                                task.status === 'Completed'
                                            "
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @change="updateTaskStatus"
                                        />
                                        <span class="ml-2"
                                            >Mark as completed</span
                                        >
                                    </div>
                                </div>

                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Due Date
                                    </h4>
                                    <p
                                        class="text-gray-900"
                                        :class="{
                                            'text-red-600':
                                                isOverdue(task) &&
                                                task.status !== 'Completed',
                                        }"
                                    >
                                        {{ formatDate(task.due_date) }}
                                    </p>
                                </div>

                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Priority
                                    </h4>
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
                                </div>

                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Assignee
                                    </h4>
                                    <p class="text-gray-900">
                                        {{
                                            task.assignee
                                                ? task.assignee.name
                                                : "Unassigned"
                                        }}
                                    </p>
                                </div>

                                <!-- Continuing the Tasks/Show.vue component -->
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Assignee
                                    </h4>
                                    <p class="text-gray-900">
                                        {{
                                            task.assignee
                                                ? task.assignee.name
                                                : "Unassigned"
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created By
                                    </h4>
                                    <p class="text-gray-900">
                                        {{
                                            task.creator
                                                ? task.creator.name
                                                : "Unknown"
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created On
                                    </h4>
                                    <p class="text-gray-900">
                                        {{ formatDate(task.created_at) }}
                                    </p>
                                </div>

                                <div v-if="task.completed_at">
                                    <h4
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Completed On
                                    </h4>
                                    <p class="text-gray-900">
                                        {{ formatDate(task.completed_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    task: Object,
    can: Object,
});

// Helper functions
const formatDate = (dateString) => {
    if (!dateString) return "Not set";
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

const updateTaskStatus = () => {
    const newStatus =
        props.task.status === "Completed" ? "Not Started" : "Completed";

    router.post(route("tasks.update-status", props.task.id), {
        status: newStatus,
    });
};
</script>
