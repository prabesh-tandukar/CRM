<!-- resources/js/Pages/Leads/Edit.vue -->
<template>
    <AppLayout title="Edit Lead">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Lead
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Title -->
                            <div>
                                <InputLabel for="title" value="Title" />
                                <select
                                    id="title"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.title"
                                >
                                    <option :value="null">None</option>
                                    <option value="Mr.">Mr.</option>
                                    <option value="Mrs.">Mrs.</option>
                                    <option value="Ms.">Ms.</option>
                                    <option value="Dr.">Dr.</option>
                                    <option value="Prof.">Prof.</option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.title"
                                />
                            </div>

                            <!-- Owner -->
                            <div>
                                <InputLabel for="owner_id" value="Owner" />
                                <select
                                    id="owner_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.owner_id"
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

                            <!-- First Name -->
                            <div>
                                <InputLabel
                                    for="first_name"
                                    value="First Name"
                                />
                                <TextInput
                                    id="first_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.first_name"
                                    required
                                    autofocus
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.first_name"
                                />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <InputLabel for="last_name" value="Last Name" />
                                <TextInput
                                    id="last_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.last_name"
                                    required
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.last_name"
                                />
                            </div>

                            <!-- Email -->
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.email"
                                />
                            </div>

                            <!-- Phone -->
                            <div>
                                <InputLabel for="phone" value="Phone" />
                                <TextInput
                                    id="phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.phone"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.phone"
                                />
                            </div>

                            <!-- Company Name -->
                            <div>
                                <InputLabel
                                    for="company_name"
                                    value="Company"
                                />
                                <TextInput
                                    id="company_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.company_name"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.company_name"
                                />
                            </div>

                            <!-- Industry -->
                            <div>
                                <InputLabel for="industry" value="Industry" />
                                <select
                                    id="industry"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.industry"
                                >
                                    <option :value="null">
                                        Select Industry
                                    </option>
                                    <option
                                        v-for="industry in industries"
                                        :key="industry"
                                        :value="industry"
                                    >
                                        {{ industry }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.industry"
                                />
                            </div>

                            <!-- Website -->
                            <div>
                                <InputLabel for="website" value="Website" />
                                <TextInput
                                    id="website"
                                    type="url"
                                    class="mt-1 block w-full"
                                    v-model="form.website"
                                    placeholder="https://example.com"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.website"
                                />
                            </div>

                            <!-- Lead Source -->
                            <div>
                                <InputLabel
                                    for="lead_source"
                                    value="Lead Source"
                                />
                                <select
                                    id="lead_source"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.lead_source"
                                >
                                    <option :value="null">
                                        Select Lead Source
                                    </option>
                                    <option
                                        v-for="source in leadSources"
                                        :key="source"
                                        :value="source"
                                    >
                                        {{ source }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.lead_source"
                                />
                            </div>

                            <!-- Lead Status -->
                            <div>
                                <InputLabel
                                    for="lead_status"
                                    value="Lead Status"
                                />
                                <select
                                    id="lead_status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.lead_status"
                                    required
                                >
                                    <option
                                        v-for="status in leadStatuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ status }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.lead_status"
                                />
                            </div>

                            <!-- Estimated Budget -->
                            <div>
                                <InputLabel
                                    for="estimated_budget"
                                    value="Estimated Budget ($)"
                                />
                                <TextInput
                                    id="estimated_budget"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.estimated_budget"
                                    min="0"
                                    step="0.01"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.estimated_budget"
                                />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mt-6">
                            <InputLabel for="address" value="Address" />
                            <textarea
                                id="address"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.address"
                                rows="3"
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.address"
                            />
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <InputLabel for="description" value="Description" />
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

                        <!-- Submit Buttons -->
                        <div class="mt-6 flex items-center justify-end gap-4">
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
                                Update Lead
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
    leadSources: Array,
    leadStatuses: Array,
    industries: Array,
});

const form = useForm({
    title: props.lead.title,
    first_name: props.lead.first_name,
    last_name: props.lead.last_name,
    email: props.lead.email,
    phone: props.lead.phone,
    company_name: props.lead.company_name,
    website: props.lead.website,
    industry: props.lead.industry,
    lead_source: props.lead.lead_source,
    lead_status: props.lead.lead_status,
    estimated_budget: props.lead.estimated_budget,
    description: props.lead.description,
    address: props.lead.address,
    owner_id: props.lead.owner_id,
});

const submit = () => {
    form.put(route("leads.update", props.lead.id));
};
</script>
