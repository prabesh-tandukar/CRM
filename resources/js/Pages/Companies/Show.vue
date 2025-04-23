<!-- resources/js/Pages/Companies/Show.vue -->
<template>
    <AppLayout :title="company.name">
        <template #header-title>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Company Details
            </h2>
        </template>
        <template #header-actions
            ><div class="flex items-center space-x-3">
                <Link
                    v-if="can.edit"
                    :href="route('companies.edit', company.id)"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
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
                    Edit
                </Link>
                <button
                    v-if="can.delete"
                    @click="confirmDelete"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                    Delete
                </button>
                <Link
                    :href="route('companies.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Back to Companies
                </Link>
            </div></template
        >

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Company Info Card -->
                    <div class="md:col-span-1">
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-500 mb-4"
                                    >
                                        {{ company.name.charAt(0) }}
                                    </div>
                                    <h3
                                        class="text-xl font-medium text-gray-900 text-center"
                                    >
                                        {{ company.name }}
                                    </h3>
                                    <p
                                        v-if="company.industry"
                                        class="text-gray-600 text-center"
                                    >
                                        {{ company.industry }}
                                    </p>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <div
                                        v-if="company.website"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Website
                                            </div>
                                            <div class="text-gray-900">
                                                <a
                                                    :href="company.website"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    {{
                                                        formatWebsite(
                                                            company.website
                                                        )
                                                    }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="company.phone"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Phone
                                            </div>
                                            <div class="text-gray-900">
                                                {{ company.phone }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="company.fax"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Fax
                                            </div>
                                            <div class="text-gray-900">
                                                {{ company.fax }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="company.employees_count"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Employees
                                            </div>
                                            <div class="text-gray-900">
                                                {{ company.employees_count }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="company.annual_revenue"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Annual Revenue
                                            </div>
                                            <div class="text-gray-900">
                                                {{
                                                    formatCurrency(
                                                        company.annual_revenue
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="company.billing_address"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Billing Address
                                            </div>
                                            <div
                                                class="text-gray-900 whitespace-pre-line"
                                            >
                                                {{ company.billing_address }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="company.owner"
                                        class="flex items-start"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 mr-3 mt-0.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Owner
                                            </div>
                                            <div class="text-gray-900">
                                                {{ company.owner.name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats Card -->
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Quick Stats
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500"
                                            >Contacts</span
                                        >
                                        <span
                                            class="text-gray-900 font-medium"
                                            >{{ contactsCount }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Deals</span>
                                        <span
                                            class="text-gray-900 font-medium"
                                            >{{ dealsCount }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500"
                                            >Total Deal Value</span
                                        >
                                        <span
                                            class="text-gray-900 font-medium"
                                            >{{
                                                formatCurrency(dealsValue)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs and Related Items -->
                    <div class="md:col-span-3">
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <!-- Tabs -->
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8">
                                        <button
                                            @click="activeTab = 'details'"
                                            :class="[
                                                activeTab === 'details'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                                'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                                            ]"
                                        >
                                            Details
                                        </button>
                                        <button
                                            @click="activeTab = 'contacts'"
                                            :class="[
                                                activeTab === 'contacts'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                                'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                                            ]"
                                        >
                                            Contacts
                                        </button>
                                        <button
                                            @click="activeTab = 'notes'"
                                            :class="[
                                                activeTab === 'notes'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                                'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                                            ]"
                                        >
                                            Notes
                                        </button>
                                    </nav>
                                </div>

                                <!-- Tab Content -->
                                <div class="mt-6">
                                    <!-- Details Tab -->
                                    <div v-show="activeTab === 'details'">
                                        <div
                                            v-if="company.description"
                                            class="mb-6"
                                        >
                                            <h4
                                                class="text-sm font-medium text-gray-500 mb-2"
                                            >
                                                Description
                                            </h4>
                                            <p
                                                class="text-gray-900 whitespace-pre-line"
                                            >
                                                {{ company.description }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="company.shipping_address"
                                            class="mb-6"
                                        >
                                            <h4
                                                class="text-sm font-medium text-gray-500 mb-2"
                                            >
                                                Shipping Address
                                            </h4>
                                            <p
                                                class="text-gray-900 whitespace-pre-line"
                                            >
                                                {{ company.shipping_address }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Contacts Tab -->
                                    <div v-show="activeTab === 'contacts'">
                                        <div
                                            v-if="
                                                company.contacts &&
                                                company.contacts.length > 0
                                            "
                                            class="overflow-x-auto"
                                        >
                                            <table
                                                class="min-w-full divide-y divide-gray-200"
                                            >
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Name
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Email
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Phone
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Job Title
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Actions
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody
                                                    class="bg-white divide-y divide-gray-200"
                                                >
                                                    <tr
                                                        v-for="contact in company.contacts"
                                                        :key="contact.id"
                                                        class="hover:bg-gray-50"
                                                    >
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap"
                                                        >
                                                            <div
                                                                class="flex items-center"
                                                            >
                                                                <div
                                                                    class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 rounded-full text-blue-600 font-medium"
                                                                >
                                                                    {{
                                                                        contact.first_name.charAt(
                                                                            0
                                                                        )
                                                                    }}{{
                                                                        contact.last_name.charAt(
                                                                            0
                                                                        )
                                                                    }}
                                                                </div>
                                                                <div
                                                                    class="ml-4"
                                                                >
                                                                    <div
                                                                        class="font-medium text-gray-900"
                                                                    >
                                                                        <Link
                                                                            :href="
                                                                                route(
                                                                                    'contacts.show',
                                                                                    contact.id
                                                                                )
                                                                            "
                                                                            class="hover:text-blue-600"
                                                                        >
                                                                            {{
                                                                                contact.first_name
                                                                            }}
                                                                            {{
                                                                                contact.last_name
                                                                            }}
                                                                        </Link>
                                                                    </div>
                                                                    <div
                                                                        v-if="
                                                                            contact.is_primary_contact
                                                                        "
                                                                        class="text-xs text-blue-600"
                                                                    >
                                                                        Primary
                                                                        Contact
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap"
                                                        >
                                                            <div
                                                                class="text-sm text-gray-900"
                                                            >
                                                                {{
                                                                    contact.email ||
                                                                    "-"
                                                                }}
                                                            </div>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap"
                                                        >
                                                            <div
                                                                class="text-sm text-gray-900"
                                                            >
                                                                {{
                                                                    contact.phone ||
                                                                    "-"
                                                                }}
                                                            </div>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap"
                                                        >
                                                            <div
                                                                class="text-sm text-gray-900"
                                                            >
                                                                {{
                                                                    contact.job_title ||
                                                                    "-"
                                                                }}
                                                            </div>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                                        >
                                                            <Link
                                                                :href="
                                                                    route(
                                                                        'contacts.show',
                                                                        contact.id
                                                                    )
                                                                "
                                                                class="text-indigo-600 hover:text-indigo-900"
                                                            >
                                                                View
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div
                                            v-else
                                            class="text-center py-6 text-gray-500"
                                        >
                                            No contacts associated with this
                                            company
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <Link
                                                :href="
                                                    route('contacts.create', {
                                                        company_id: company.id,
                                                    })
                                                "
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                    />
                                                </svg>
                                                Add Contact
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Notes Tab -->
                                    <div v-show="activeTab === 'notes'">
                                        <!-- Notes list -->
                                        <div
                                            v-if="
                                                company.notes &&
                                                company.notes.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="note in company.notes"
                                                :key="note.id"
                                                class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0"
                                            >
                                                <div
                                                    class="flex justify-between items-start"
                                                >
                                                    <h4
                                                        class="text-sm font-medium text-gray-900"
                                                    >
                                                        {{
                                                            note.title || "Note"
                                                        }}
                                                    </h4>
                                                    <span
                                                        class="text-xs text-gray-500"
                                                        >{{
                                                            formatDate(
                                                                note.created_at
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <p
                                                    class="mt-2 text-gray-700 whitespace-pre-line"
                                                >
                                                    {{ note.content }}
                                                </p>
                                                <p
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    By {{ note.creator.name }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="text-center py-6 text-gray-500"
                                        >
                                            No notes available
                                        </div>

                                        <!-- Add Note Form -->
                                        <div
                                            class="mt-6 border-t border-gray-200 pt-6"
                                        >
                                            <h4
                                                class="text-sm font-medium text-gray-900 mb-4"
                                            >
                                                Add a Note
                                            </h4>
                                            <form @submit.prevent="addNote">
                                                <div>
                                                    <InputLabel
                                                        for="note_title"
                                                        value="Title (Optional)"
                                                    />
                                                    <TextInput
                                                        id="note_title"
                                                        type="text"
                                                        class="mt-1 block w-full"
                                                        v-model="noteForm.title"
                                                    />
                                                </div>
                                                <div class="mt-4">
                                                    <InputLabel
                                                        for="note_content"
                                                        value="Content"
                                                    />
                                                    <textarea
                                                        id="note_content"
                                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                        v-model="
                                                            noteForm.content
                                                        "
                                                        rows="3"
                                                        required
                                                    ></textarea>
                                                </div>
                                                <div
                                                    class="mt-4 flex justify-end"
                                                >
                                                    <PrimaryButton
                                                        :disabled="
                                                            noteForm.processing
                                                        "
                                                    >
                                                        Add Note
                                                    </PrimaryButton>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="deleteModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        >
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div
                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    >
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-red-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                </div>
                                <div
                                    class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left"
                                >
                                    <h3
                                        class="text-lg font-medium leading-6 text-gray-900"
                                    >
                                        Delete Company
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to delete this
                                            company? This action cannot be
                                            undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                        >
                            <button
                                type="button"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                                @click="deleteCompany"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                @click="deleteModal = false"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    company: Object,
    contactsCount: Number,
    dealsCount: Number,
    dealsValue: Number,
    can: Object,
});

// Active tab state
const activeTab = ref("details");

// Delete confirmation modal
const deleteModal = ref(false);

// Note form
const noteForm = useForm({
    title: "",
    content: "",
});

// Helper methods
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formatCurrency = (value) => {
    if (!value) return "$0.00";
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const formatWebsite = (website) => {
    return website.replace(/^https?:\/\/(www\.)?/, "").replace(/\/$/, "");
};

const confirmDelete = () => {
    deleteModal.value = true;
};

const deleteCompany = () => {
    router.delete(route("companies.destroy", props.company.id), {
        onSuccess: () => {
            deleteModal.value = false;
        },
    });
};

const addNote = () => {
    noteForm.post(route("companies.add-note", props.company.id), {
        onSuccess: () => {
            noteForm.reset();
        },
    });
};
</script>
