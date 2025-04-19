<!-- resources/js/Pages/Leads/Convert.vue -->
<template>
    <AppLayout title="Convert Lead">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Convert Lead: {{ lead.first_name }} {{ lead.last_name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-8">
                            <!-- Lead Summary -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Lead Information
                                </h3>
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
                                >
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Name
                                        </p>
                                        <p class="font-medium">
                                            {{ lead.first_name }}
                                            {{ lead.last_name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Email
                                        </p>
                                        <p class="font-medium">
                                            {{ lead.email || "N/A" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Phone
                                        </p>
                                        <p class="font-medium">
                                            {{ lead.phone || "N/A" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Company
                                        </p>
                                        <p class="font-medium">
                                            {{ lead.company_name || "N/A" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Lead Source
                                        </p>
                                        <p class="font-medium">
                                            {{ lead.lead_source || "N/A" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Estimated Budget
                                        </p>
                                        <p class="font-medium">
                                            {{
                                                lead.estimated_budget
                                                    ? `$${lead.estimated_budget}`
                                                    : "N/A"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Conversion Options -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Conversion Options
                                </h3>

                                <!-- Create Contact -->
                                <div class="mb-4">
                                    <div class="flex items-center">
                                        <input
                                            id="create_contact"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            v-model="form.create_contact"
                                        />
                                        <label
                                            for="create_contact"
                                            class="ml-2 font-medium text-gray-700"
                                        >
                                            Create Contact
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1 ml-6">
                                        A new contact will be created with the
                                        lead's information.
                                    </p>
                                </div>

                                <!-- Company Selection -->
                                <div
                                    class="mb-4 pl-6"
                                    v-if="form.create_contact"
                                >
                                    <InputLabel
                                        for="company_id"
                                        value="Associate with Company"
                                    />
                                    <select
                                        id="company_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.company_id"
                                    >
                                        <option :value="null">
                                            None (Create new from lead data)
                                        </option>
                                        <!-- Here you would ideally have a list of companies to select from -->
                                        <!-- For now, we'll use a placeholder -->
                                        <option value="placeholder" disabled>
                                            (Existing companies would be listed
                                            here)
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.company_id"
                                    />
                                </div>

                                <!-- Create Deal -->
                                <div class="mb-4">
                                    <div class="flex items-center">
                                        <input
                                            id="create_deal"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            v-model="form.create_deal"
                                        />
                                        <label
                                            for="create_deal"
                                            class="ml-2 font-medium text-gray-700"
                                        >
                                            Create Deal
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1 ml-6">
                                        A new deal will be created and
                                        associated with the contact.
                                    </p>
                                </div>

                                <!-- Deal Information (conditional) -->
                                <div
                                    class="pl-6 space-y-4"
                                    v-if="form.create_deal"
                                >
                                    <div>
                                        <InputLabel
                                            for="deal_name"
                                            value="Deal Name"
                                        />
                                        <TextInput
                                            id="deal_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.deal_name"
                                            required
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="form.errors.deal_name"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            for="deal_value"
                                            value="Deal Value ($)"
                                        />
                                        <TextInput
                                            id="deal_value"
                                            type="number"
                                            class="mt-1 block w-full"
                                            v-model="form.deal_value"
                                            min="0"
                                            step="0.01"
                                            required
                                        />
                                        <InputError
                                            class="mt-2"
                                            :message="form.errors.deal_value"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            for="deal_owner_id"
                                            value="Deal Owner"
                                        />
                                        <select
                                            id="deal_owner_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            v-model="form.deal_owner_id"
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
                                            :message="form.errors.deal_owner_id"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-8 flex items-center justify-end gap-4">
                            <Link
                                :href="route('leads.show', lead.id)"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Convert Lead
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    lead: Object,
    users: Array,
});

const form = useForm({
    create_contact: true,
    create_deal: true,
    company_id: null,
    deal_name: `New deal with ${
        props.lead.company_name ||
        props.lead.first_name + " " + props.lead.last_name
    }`,
    deal_value: props.lead.estimated_budget || 0,
    deal_owner_id: props.lead.owner_id,
});

const submit = () => {
    form.post(route("leads.convert", props.lead.id));
};
</script>
