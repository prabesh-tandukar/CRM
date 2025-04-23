<!-- resources/js/Pages/Tasks/Create.vue -->
<template>
    <AppLayout title="Create Task">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create Task
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Title -->
                            <div class="md:col-span-2">
                                <InputLabel for="title" value="Title" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    autofocus
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

                            <!-- Due Date -->
                            <div>
                                <InputLabel for="due_date" value="Due Date" />
                                <TextInput
                                    id="due_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.due_date"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.due_date"
                                />
                            </div>

                            <!-- Priority -->
                            <div>
                                <InputLabel for="priority" value="Priority" />
                                <select
                                    id="priority"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.priority"
                                    required
                                >
                                    <option
                                        v-for="priority in priorities"
                                        :key="priority"
                                        :value="priority"
                                    >
                                        {{ priority }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.priority"
                                />
                            </div>

                            <!-- Status -->
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.status"
                                    required
                                >
                                    <option
                                        v-for="status in statuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ status }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.status"
                                />
                            </div>

                            <!-- Assignee -->
                            <div>
                                <InputLabel
                                    for="assignee_id"
                                    value="Assignee"
                                />
                                <select
                                    id="assignee_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.assignee_id"
                                    required
                                >
                                    <option
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ user.name }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.assignee_id"
                                />
                            </div>

                            <!-- Related To -->
                            <div class="md:col-span-2" v-if="relatedTo">
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
                                    v-model="form.taskable_type"
                                />
                                <input
                                    type="hidden"
                                    v-model="form.taskable_id"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="
                                        form.errors.taskable_id ||
                                        form.errors.taskable_type
                                    "
                                />
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-6 flex items-center justify-end gap-4">
                            <Link
                                :href="route('tasks.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Create Task
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    users: Array,
    statuses: Array,
    priorities: Array,
    relatedTo: Object,
});

// Initialize form with default values
const form = useForm({
    title: "",
    description: "",
    due_date: "",
    priority: "Medium",
    status: "Not Started",
    assignee_id:
        props.users && props.users.length > 0 ? props.users[0].id : null,
    taskable_type: props.relatedTo
        ? `App\\Models\\${capitalize(props.relatedTo.type)}`
        : null,
    taskable_id: props.relatedTo ? props.relatedTo.id : null,
});

function capitalize(string) {
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function submit() {
    form.post(route("tasks.store"));
}
</script>
