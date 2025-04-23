<!-- resources/js/Pages/Tasks/Edit.vue -->
<template>
    <AppLayout title="Edit Task">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Task
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
                                <p class="text-sm text-gray-500 mt-1">
                                    The task's relationship cannot be changed
                                    after creation.
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
                                    Delete Task
                                </DangerButton>
                            </div>
                            <div class="flex items-center gap-4">
                                <Link
                                    :href="route('tasks.show', task.id)"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Update Task
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
                <h2 class="text-lg font-medium text-gray-900">Delete Task</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this task? This action
                    cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton
                        @click="confirmingDeletion = false"
                        class="mr-3"
                    >
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTask"
                    >
                        Delete Task
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    task: Object,
    users: Array,
    statuses: Array,
    priorities: Array,
    relatedTo: Object,
    can: Object,
});

// Format due date for input
const formatDateForInput = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toISOString().split("T")[0];
};

// Initialize form with task data
const form = useForm({
    title: props.task.title,
    description: props.task.description || "",
    due_date: formatDateForInput(props.task.due_date),
    priority: props.task.priority,
    status: props.task.status,
    assignee_id: props.task.assignee_id,
});

const confirmingDeletion = ref(false);

function capitalize(string) {
    if (!string) return "";
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function submit() {
    form.put(route("tasks.update", props.task.id));
}

function confirmDelete() {
    confirmingDeletion.value = true;
}

function deleteTask() {
    router.delete(route("tasks.destroy", props.task.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        },
    });
}
</script>
