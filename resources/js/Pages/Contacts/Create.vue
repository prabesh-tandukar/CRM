<!-- resources/js/Pages/Contacts/Create.vue -->
<template>
    <AppLayout title="Create Contact">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create Contact
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                            <!-- Mobile -->
                            <div>
                                <InputLabel for="mobile" value="Mobile" />
                                <TextInput
                                    id="mobile"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.mobile"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.mobile"
                                />
                            </div>

                            <!-- Job Title -->
                            <div>
                                <InputLabel for="job_title" value="Job Title" />
                                <TextInput
                                    id="job_title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.job_title"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.job_title"
                                />
                            </div>

                            <!-- Department -->
                            <div>
                                <InputLabel
                                    for="department"
                                    value="Department"
                                />
                                <TextInput
                                    id="department"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.department"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.department"
                                />
                            </div>

                            <!-- Company -->
                            <div>
                                <InputLabel for="company_id" value="Company" />
                                <select
                                    id="company_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.company_id"
                                >
                                    <option :value="null">No Company</option>
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
                                >
                                    <option :value="null">
                                        Select Lead Status
                                    </option>
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
                        </div>

                        <!-- Mailing Address -->
                        <div class="mt-6">
                            <InputLabel
                                for="mailing_address"
                                value="Mailing Address"
                            />
                            <textarea
                                id="mailing_address"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.mailing_address"
                                rows="3"
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.mailing_address"
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

                        <!-- Primary Contact Checkbox -->
                        <div class="mt-6 flex items-center">
                            <input
                                id="is_primary_contact"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                v-model="form.is_primary_contact"
                            />
                            <label
                                for="is_primary_contact"
                                class="ml-2 block text-sm text-gray-900"
                            >
                                Set as primary contact for this company
                            </label>
                            <InputError
                                class="mt-2"
                                :message="form.errors.is_primary_contact"
                            />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="mt-6 flex items-center justify-end gap-4">
                            <Link
                                :href="route('contacts.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Create Contact
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
    companies: Array,
    users: Array,
    leadSources: Array,
    leadStatuses: Array,
});

const form = useForm({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    mobile: "",
    job_title: "",
    department: "",
    company_id: null,
    owner_id: props.users && props.users.length > 0 ? props.users[0].id : null,
    lead_source: null,
    lead_status: null,
    mailing_address: "",
    description: "",
    is_primary_contact: false,
});

const submit = () => {
    form.post(route("contacts.store"));
};
</script>
