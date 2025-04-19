<!-- resources/js/Pages/Deals/Create.vue -->
<template>
    <AppLayout title="Create Deal">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create Deal
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Basic Deal Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Deal Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Deal Name -->
                                <div>
                                    <InputLabel for="name" value="Deal Name" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        autofocus
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.name"
                                    />
                                </div>

                                <!-- Owner -->
                                <div>
                                    <InputLabel for="owner_id" value="Owner" />
                                    <select
                                        id="owner_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.owner_id"
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
                                        :message="form.errors.owner_id"
                                    />
                                </div>

                                <!-- Amount -->
                                <div>
                                    <InputLabel
                                        for="amount"
                                        value="Amount ($)"
                                    />
                                    <TextInput
                                        id="amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full"
                                        v-model="form.amount"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.amount"
                                    />
                                </div>

                                <!-- Pipeline Stage -->
                                <div>
                                    <InputLabel
                                        for="pipeline_stage"
                                        value="Pipeline Stage"
                                    />
                                    <select
                                        id="pipeline_stage"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.pipeline_stage"
                                        required
                                    >
                                        <option
                                            v-for="stage in pipelineStages"
                                            :key="stage"
                                            :value="stage"
                                        >
                                            {{ stage }}
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.pipeline_stage"
                                    />
                                </div>

                                <!-- Probability -->
                                <div>
                                    <InputLabel
                                        for="probability"
                                        value="Probability (%)"
                                    />
                                    <TextInput
                                        id="probability"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="mt-1 block w-full"
                                        v-model="form.probability"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.probability"
                                    />
                                </div>

                                <!-- Expected Close Date -->
                                <div>
                                    <InputLabel
                                        for="expected_close_date"
                                        value="Expected Close Date"
                                    />
                                    <TextInput
                                        id="expected_close_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.expected_close_date"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.expected_close_date
                                        "
                                    />
                                </div>

                                <!-- Source -->
                                <div>
                                    <InputLabel for="source" value="Source" />
                                    <select
                                        id="source"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.source"
                                    >
                                        <option value="">Select Source</option>
                                        <option
                                            v-for="source in sources"
                                            :key="source"
                                            :value="source"
                                        >
                                            {{ source }}
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.source"
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
                                        <option value="Open">Open</option>
                                        <option value="Won">Won</option>
                                        <option value="Lost">Lost</option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.status"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Company and Contact Information -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Company & Contact Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Company -->
                                <div>
                                    <InputLabel
                                        for="company_id"
                                        value="Company"
                                    />
                                    <select
                                        id="company_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.company_id"
                                        @change="loadContacts"
                                    >
                                        <option :value="null">
                                            Select Company
                                        </option>
                                        <option
                                            v-for="company in companies"
                                            :key="company.id"
                                            :value="company.id"
                                        >
                                            {{ company.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.company_id"
                                    />
                                </div>

                                <!-- Primary Contact -->
                                <div>
                                    <InputLabel
                                        for="primary_contact_id"
                                        value="Primary Contact"
                                    />
                                    <select
                                        id="primary_contact_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.primary_contact_id"
                                        :disabled="!availableContacts.length"
                                    >
                                        <option :value="null">
                                            Select Contact
                                        </option>
                                        <option
                                            v-for="contact in availableContacts"
                                            :key="contact.id"
                                            :value="contact.id"
                                        >
                                            {{ contact.first_name }}
                                            {{ contact.last_name }}
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.primary_contact_id
                                        "
                                    />
                                    <p
                                        v-if="
                                            !availableContacts.length &&
                                            form.company_id
                                        "
                                        class="mt-2 text-sm text-gray-500"
                                    >
                                        No contacts found for this company.
                                        <Link
                                            :href="
                                                route('contacts.create', {
                                                    company_id: form.company_id,
                                                })
                                            "
                                            class="text-indigo-600 hover:text-indigo-900"
                                            >Create a contact</Link
                                        >
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Contacts -->
                        <div class="mt-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Additional Contacts
                                </h3>
                                <button
                                    type="button"
                                    @click="addContact"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    :disabled="!availableContacts.length"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 mr-1"
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
                                    Add Contact
                                </button>
                            </div>

                            <div v-if="form.contacts.length" class="space-y-4">
                                <div
                                    v-for="(contact, index) in form.contacts"
                                    :key="index"
                                    class="flex items-center space-x-4 p-4 bg-gray-50 rounded-md"
                                >
                                    <div class="flex-1">
                                        <select
                                            v-model="contact.id"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option
                                                v-for="c in getFilteredContacts()"
                                                :key="c.id"
                                                :value="c.id"
                                            >
                                                {{ c.first_name }}
                                                {{ c.last_name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <select
                                            v-model="contact.role"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="Stakeholder">
                                                Stakeholder
                                            </option>
                                            <option value="Decision Maker">
                                                Decision Maker
                                            </option>
                                            <option value="Influencer">
                                                Influencer
                                            </option>
                                            <option value="End User">
                                                End User
                                            </option>
                                            <option value="Technical Contact">
                                                Technical Contact
                                            </option>
                                        </select>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeContact(index)"
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
                            </div>
                            <div
                                v-else-if="!availableContacts.length"
                                class="text-center py-4 text-gray-500"
                            >
                                No contacts available to add.
                            </div>
                            <div v-else class="text-center py-4 text-gray-500">
                                No additional contacts added.
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-8">
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.description"
                                rows="4"
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.description"
                            />
                        </div>

                        <!-- Loss Reason (conditionally shown) -->
                        <div v-if="form.status === 'Lost'" class="mt-6">
                            <InputLabel for="loss_reason" value="Loss Reason" />
                            <textarea
                                id="loss_reason"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.loss_reason"
                                rows="3"
                                required
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.loss_reason"
                            />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-8 flex items-center justify-end gap-4">
                            <Link
                                :href="route('deals.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Create Deal
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    users: Array,
    companies: Array,
    contacts: Array,
    preselectedContact: Object,
    preselectedCompany: Object,
    pipelineStages: Array,
    sources: Array,
    products: Array,
});

const availableContacts = ref(props.contacts || []);

// Initialize form with preselected values if available
const form = useForm({
    name: "",
    company_id: props.preselectedCompany ? props.preselectedCompany.id : null,
    primary_contact_id: props.preselectedContact
        ? props.preselectedContact.id
        : null,
    owner_id: props.users && props.users.length > 0 ? props.users[0].id : null,
    amount: "",
    pipeline_stage: "Qualification",
    probability: 10,
    expected_close_date: "",
    status: "Open",
    source: "",
    description: "",
    loss_reason: "",
    contacts: [],
    products: [],
});

// If we have a preselected company, load its contacts
if (props.preselectedCompany) {
    loadContacts();
}

// Load contacts for the selected company
async function loadContacts() {
    if (!form.company_id) {
        availableContacts.value = [];
        return;
    }

    try {
        const response = await fetch(
            `/api/companies/${form.company_id}/contacts`
        );
        const data = await response.json();
        availableContacts.value = data;
    } catch (error) {
        console.error("Failed to load contacts:", error);
        availableContacts.value = [];
    }
}

// Add a new contact to the deal
function addContact() {
    if (availableContacts.value.length) {
        // Find a contact that isn't already selected
        const availableContact = getFilteredContacts()[0];
        if (availableContact) {
            form.contacts.push({
                id: availableContact.id,
                role: "Stakeholder",
            });
        }
    }
}

// Remove a contact from the deal
function removeContact(index) {
    form.contacts.splice(index, 1);
}

// Get contacts that aren't already selected
function getFilteredContacts() {
    // Get IDs of contacts that are already selected
    const selectedIds = [
        form.primary_contact_id,
        ...form.contacts.map((c) => c.id),
    ].filter(Boolean);

    // Filter out contacts that are already selected
    return availableContacts.value.filter(
        (contact) =>
            !selectedIds.includes(contact.id) ||
            form.contacts.some((c) => c.id === contact.id)
    );
}

function submit() {
    form.post(route("deals.store"));
}

// Watch for status changes to reset loss reason when not 'Lost'
watch(
    () => form.status,
    (newStatus) => {
        if (newStatus !== "Lost") {
            form.loss_reason = "";
        }
    }
);
</script>
