<!-- resources/js/Pages/Companies/Edit.vue -->
<template>
    <AppLayout title="Edit Company">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Company
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Company Name -->
                            <div>
                                <InputLabel for="name" value="Company Name" />
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

                            <!-- Fax -->
                            <div>
                                <InputLabel for="fax" value="Fax" />
                                <TextInput
                                    id="fax"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.fax"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.fax"
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

                            <!-- Employees Count -->
                            <div>
                                <InputLabel
                                    for="employees_count"
                                    value="Number of Employees"
                                />
                                <TextInput
                                    id="employees_count"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.employees_count"
                                    min="0"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.employees_count"
                                />
                            </div>

                            <!-- Annual Revenue -->
                            <div>
                                <InputLabel
                                    for="annual_revenue"
                                    value="Annual Revenue ($)"
                                />
                                <TextInput
                                    id="annual_revenue"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.annual_revenue"
                                    min="0"
                                    step="0.01"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.annual_revenue"
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
                        </div>

                        <!-- Billing Address -->
                        <div class="mt-6">
                            <InputLabel
                                for="billing_address"
                                value="Billing Address"
                            />
                            <textarea
                                id="billing_address"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.billing_address"
                                rows="3"
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.billing_address"
                            />
                        </div>

                        <!-- Shipping Address -->
                        <div class="mt-6">
                            <InputLabel
                                for="shipping_address"
                                value="Shipping Address"
                            />
                            <textarea
                                id="shipping_address"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                v-model="form.shipping_address"
                                rows="3"
                            ></textarea>
                            <InputError
                                class="mt-2"
                                :message="form.errors.shipping_address"
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
                                :href="route('companies.show', company.id)"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Update Company
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
    company: Object,
    users: Array,
    industries: Array,
});

const form = useForm({
    name: props.company.name,
    website: props.company.website || "",
    phone: props.company.phone || "",
    fax: props.company.fax || "",
    industry: props.company.industry,
    employees_count: props.company.employees_count,
    annual_revenue: props.company.annual_revenue,
    billing_address: props.company.billing_address || "",
    shipping_address: props.company.shipping_address || "",
    description: props.company.description || "",
    owner_id: props.company.owner_id,
});

const submit = () => {
    form.put(route("companies.update", props.company.id));
};
</script>
