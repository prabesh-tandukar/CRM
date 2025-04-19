<!-- resources/js/Pages/Leads/Show.vue -->
<template>
    <AppLayout :title="`${lead.first_name} ${lead.last_name}`">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Lead Details
                </h2>
                <div class="flex space-x-2">
                    <Link
                        v-if="lead.lead_status !== 'Converted'"
                        :href="route('leads.convert.show', lead.id)"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Convert Lead
                    </Link>
                    <Link
                        :href="route('leads.edit', lead.id)"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit Lead
                    </Link>
                    <Link
                        :href="route('leads.index')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Back to Leads
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
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="md:w-2/3">
                                <h1 class="text-2xl font-bold">
                                    {{ lead.title ? `${lead.title} ` : ""
                                    }}{{ lead.first_name }} {{ lead.last_name }}
                                </h1>
                                <p class="text-gray-500">
                                    {{ lead.company_name }}
                                </p>

                                <div
                                    class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4"
                                >
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Email
                                        </p>
                                        <p>{{ lead.email || "N/A" }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Phone
                                        </p>
                                        <p>{{ lead.phone || "N/A" }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Website
                                        </p>
                                        <p>
                                            <a
                                                v-if="lead.website"
                                                :href="lead.website"
                                                target="_blank"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ lead.website }}
                                            </a>
                                            <span v-else>N/A</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Industry
                                        </p>
                                        <p>{{ lead.industry || "N/A" }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Estimated Budget
                                        </p>
                                        <p>
                                            {{
                                                lead.estimated_budget
                                                    ? `$${lead.estimated_budget}`
                                                    : "N/A"
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Lead Source
                                        </p>
                                        <p>{{ lead.lead_source || "N/A" }}</p>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Address</p>
                                    <p>{{ lead.address || "N/A" }}</p>
                                </div>

                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">
                                        Description
                                    </p>
                                    <p>{{ lead.description || "N/A" }}</p>
                                </div>
                            </div>

                            <div class="md:w-1/3 mt-6 md:mt-0">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3
                                        class="font-semibold text-gray-700 mb-3"
                                    >
                                        Lead Information
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Status
                                            </p>
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="
                                                    getStatusClass(
                                                        lead.lead_status
                                                    )
                                                "
                                            >
                                                {{ lead.lead_status }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Owner
                                            </p>
                                            <p>
                                                {{
                                                    lead.owner
                                                        ? lead.owner.name
                                                        : "Unassigned"
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Created By
                                            </p>
                                            <p>
                                                {{
                                                    lead.creator
                                                        ? lead.creator.name
                                                        : "System"
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Created On
                                            </p>
                                            <p>
                                                {{
                                                    formatDate(lead.created_at)
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Last Updated
                                            </p>
                                            <p>
                                                {{
                                                    formatDate(lead.updated_at)
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Notes
                        </h3>

                        <!-- Add Note Form -->
                        <form @submit.prevent="addNote" class="mb-6">
                            <div>
                                <textarea
                                    v-model="noteForm.content"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="3"
                                    placeholder="Add a note..."
                                    required
                                ></textarea>
                                <InputError
                                    v-if="noteForm.errors.content"
                                    :message="noteForm.errors.content"
                                    class="mt-2"
                                />
                            </div>
                            <div class="mt-2 flex justify-end">
                                <PrimaryButton
                                    :class="{
                                        'opacity-25': noteForm.processing,
                                    }"
                                    :disabled="noteForm.processing"
                                >
                                    Add Note
                                </PrimaryButton>
                            </div>
                        </form>

                        <!-- Notes List -->
                        <div v-if="notes && notes.length > 0" class="space-y-4">
                            <div
                                v-for="note in notes"
                                :key="note.id"
                                class="bg-gray-50 p-4 rounded-lg"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="whitespace-pre-wrap">
                                            {{ note.content }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            {{
                                                note.user
                                                    ? note.user.name
                                                    : "Unknown"
                                            }}
                                            -
                                            {{
                                                formatDateTime(note.created_at)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 text-center py-4">
                            No notes yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    lead: Object,
    notes: {
        type: Array,
        default: () => [],
    },
});

// Form for adding notes
const noteForm = useForm({
    content: "",
});

function addNote() {
    noteForm.post(route("leads.add-note", props.lead.id), {
        onSuccess: () => {
            noteForm.reset();
        },
    });
}

function formatDate(date) {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
}

function formatDateTime(date) {
    if (!date) return "N/A";
    const dateObj = new Date(date);
    return `${dateObj.toLocaleDateString()} ${dateObj.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
    })}`;
}

function getStatusClass(status) {
    const statusClasses = {
        New: "bg-blue-100 text-blue-800",
        Contacted: "bg-yellow-100 text-yellow-800",
        Qualified: "bg-green-100 text-green-800",
        Unqualified: "bg-red-100 text-red-800",
        Nurturing: "bg-purple-100 text-purple-800",
        Converted: "bg-indigo-100 text-indigo-800",
        Closed: "bg-gray-100 text-gray-800",
    };

    return statusClasses[status] || "bg-gray-100 text-gray-800";
}
</script>
