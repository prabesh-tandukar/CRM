<!-- resources/js/Pages/Contacts/Show.vue -->
<template>
    <AppLayout :title="`${contact.first_name} ${contact.last_name}`">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Contact Details
                </h2>
                <div class="flex items-center space-x-3">
                    <Link
                        v-if="can.edit"
                        :href="route('contacts.edit', contact.id)"
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
                        :href="route('contacts.index')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        Back to Contacts
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Contact Info Card -->
                    <div class="md:col-span-1">
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-500 mb-4"
                                    >
                                        {{ contact.first_name.charAt(0)
                                        }}{{ contact.last_name.charAt(0) }}
                                    </div>
                                    <h3
                                        class="text-xl font-medium text-gray-900 text-center"
                                    >
                                        {{ contact.first_name }}
                                        {{ contact.last_name }}
                                    </h3>
                                    <p class="text-gray-600 text-center">
                                        {{
                                            contact.job_title || "No Job Title"
                                        }}
                                    </p>
                                    <div
                                        v-if="contact.company"
                                        class="text-gray-600 text-center"
                                    >
                                        {{ contact.company.name }}
                                    </div>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <div
                                        v-if="contact.email"
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
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Email
                                            </div>
                                            <div class="text-gray-900">
                                                {{ contact.email }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="contact.phone"
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
                                                {{ contact.phone }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="contact.mobile"
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
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Mobile
                                            </div>
                                            <div class="text-gray-900">
                                                {{ contact.mobile }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="contact.department"
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Department
                                            </div>
                                            <div class="text-gray-900">
                                                {{ contact.department }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="contact.mailing_address"
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
                                                Address
                                            </div>
                                            <div
                                                class="text-gray-900 whitespace-pre-line"
                                            >
                                                {{ contact.mailing_address }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="contact.lead_source"
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
                                                d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Lead Source
                                            </div>
                                            <div class="text-gray-900">
                                                {{ contact.lead_source }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="contact.owner"
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
                                                {{ contact.owner.name }}
                                            </div>
                                        </div>
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
                                        <button
                                            @click="activeTab = 'deals'"
                                            :class="[
                                                activeTab === 'deals'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                                'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                                            ]"
                                        >
                                            Deals
                                        </button>
                                        <button
                                            @click="activeTab = 'tasks'"
                                            :class="[
                                                activeTab === 'tasks'
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                                'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm',
                                            ]"
                                        >
                                            Tasks
                                        </button>
                                    </nav>
                                </div>

                                <!-- Tab Content -->
                                <div class="mt-6">
                                    <!-- Details Tab -->
                                    <div v-show="activeTab === 'details'">
                                        <div
                                            v-if="contact.description"
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
                                                {{ contact.description }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="
                                                contact.tags &&
                                                contact.tags.length > 0
                                            "
                                            class="mb-6"
                                        >
                                            <h4
                                                class="text-sm font-medium text-gray-500 mb-2"
                                            >
                                                Tags
                                            </h4>
                                            <div class="flex flex-wrap gap-2">
                                                <span
                                                    v-for="tag in contact.tags"
                                                    :key="tag.id"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ tag.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes Tab -->
                                    <div v-show="activeTab === 'notes'">
                                        <!-- Notes list -->
                                        <div
                                            v-if="
                                                contact.notes &&
                                                contact.notes.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="note in contact.notes"
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

                                    <!-- Deals Tab -->
                                    <div v-show="activeTab === 'deals'">
                                        <div
                                            v-if="
                                                contact.deals &&
                                                contact.deals.length > 0
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
                                                            Stage
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Amount
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Expected Close
                                                        </th>
                                                        <th
                                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                        >
                                                            Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody
                                                    class="bg-white divide-y divide-gray-200"
                                                >
                                                    <tr
                                                        v-for="deal in contact.deals"
                                                        :key="deal.id"
                                                    >
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap"
                                                        >
                                                            <div
                                                                class="text-sm font-medium text-gray-900"
                                                            >
                                                                {{ deal.name }}
                                                            </div>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap"
                                                        >
                                                            <span
                                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                                            >
                                                                {{
                                                                    deal.pipeline_stage
                                                                }}
                                                            </span>
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                        >
                                                            {{
                                                                formatCurrency(
                                                                    deal.amount
                                                                )
                                                            }}
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                        >
                                                            {{
                                                                formatDate(
                                                                    deal.expected_close_date
                                                                )
                                                            }}
                                                        </td>
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                                        >
                                                            <Link
                                                                :href="
                                                                    route(
                                                                        'deals.show',
                                                                        deal.id
                                                                    )
                                                                "
                                                                class="text-indigo-600 hover:text-indigo-900"
                                                                >View</Link
                                                            >
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div
                                            v-else
                                            class="text-center py-6 text-gray-500"
                                        >
                                            No deals associated with this
                                            contact
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <Link
                                                :href="
                                                    route('deals.create', {
                                                        contact_id: contact.id,
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
                                                Create Deal
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Tasks Tab -->
                                    <div v-show="activeTab === 'tasks'">
                                        <div
                                            v-if="
                                                contact.tasks &&
                                                contact.tasks.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="task in contact.tasks"
                                                :key="task.id"
                                                class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0"
                                            >
                                                <div
                                                    class="flex justify-between items-start"
                                                >
                                                    <div
                                                        class="flex items-start"
                                                    >
                                                        <div
                                                            class="flex-shrink-0 mt-1"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                :checked="
                                                                    task.status ===
                                                                    'Completed'
                                                                "
                                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                                @change="
                                                                    updateTaskStatus(
                                                                        task
                                                                    )
                                                                "
                                                            />
                                                        </div>
                                                        <div class="ml-3">
                                                            <h4
                                                                class="text-sm font-medium text-gray-900"
                                                                :class="{
                                                                    'line-through':
                                                                        task.status ===
                                                                        'Completed',
                                                                }"
                                                            >
                                                                {{ task.title }}
                                                            </h4>
                                                            <p
                                                                v-if="
                                                                    task.description
                                                                "
                                                                class="mt-1 text-sm text-gray-500"
                                                                :class="{
                                                                    'line-through':
                                                                        task.status ===
                                                                        'Completed',
                                                                }"
                                                            >
                                                                {{
                                                                    task.description
                                                                }}
                                                            </p>
                                                            <div
                                                                class="mt-1 flex items-center"
                                                            >
                                                                <span
                                                                    class="text-xs text-gray-500 mr-3"
                                                                    >Due:
                                                                    {{
                                                                        formatDate(
                                                                            task.due_date
                                                                        )
                                                                    }}</span
                                                                >
                                                                <span
                                                                    class="text-xs text-gray-500"
                                                                    >Assigned
                                                                    to:
                                                                    {{
                                                                        task.assignee
                                                                            ? task
                                                                                  .assignee
                                                                                  .name
                                                                            : "Unassigned"
                                                                    }}</span
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="{
                                                            'bg-green-100 text-green-800':
                                                                task.status ===
                                                                'Completed',
                                                            'bg-yellow-100 text-yellow-800':
                                                                task.status ===
                                                                'In Progress',
                                                            'bg-red-100 text-red-800':
                                                                task.status ===
                                                                'Overdue',
                                                            'bg-blue-100 text-blue-800':
                                                                task.status ===
                                                                'Not Started',
                                                        }"
                                                    >
                                                        {{ task.status }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="text-center py-6 text-gray-500"
                                        >
                                            No tasks associated with this
                                            contact
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <Link
                                                :href="
                                                    route('tasks.create', {
                                                        contact_id: contact.id,
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
                                                Create Task
                                            </Link>
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
                                        Delete Contact
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to delete this
                                            contact? This action cannot be
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
                                @click="deleteContact"
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
    contact: Object,
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

const confirmDelete = () => {
    deleteModal.value = true;
};

const deleteContact = () => {
    router.delete(route("contacts.destroy", props.contact.id), {
        onSuccess: () => {
            deleteModal.value = false;
        },
    });
};

const addNote = () => {
    noteForm.post(route("contacts.add-note", props.contact.id), {
        onSuccess: () => {
            noteForm.reset();
        },
    });
};

const updateTaskStatus = (task) => {
    // This will be implemented when we build the task module
    console.log("Update task status:", task.id);
};
</script>
