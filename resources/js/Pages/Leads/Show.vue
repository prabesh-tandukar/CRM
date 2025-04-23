<!-- resources/js/Pages/Leads/Show.vue -->
<template>
    <AppLayout :title="`${lead.first_name} ${lead.last_name}`">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Lead Details
                </h2>
            </div>
        </template>
        <template #header-actions>
            <div class="flex space-x-2">
                <Link
                    v-if="can.convert && !isConverted"
                    :href="route('leads.convert.show', lead.id)"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Convert Lead
                </Link>
                <Link
                    v-if="can.edit && !isConverted"
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

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Lead Info Card -->
                    <div class="md:col-span-1">
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-500 mb-4"
                                    >
                                        {{ lead.first_name.charAt(0)
                                        }}{{ lead.last_name.charAt(0) }}
                                    </div>
                                    <h3
                                        class="text-xl font-medium text-gray-900 text-center"
                                    >
                                        {{ lead.first_name }}
                                        {{ lead.last_name }}
                                    </h3>
                                    <p
                                        v-if="lead.company_name"
                                        class="text-gray-600 text-center"
                                    >
                                        {{ lead.company_name }}
                                    </p>
                                    <span
                                        class="mt-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="
                                            getStatusClass(lead.lead_status)
                                        "
                                    >
                                        {{ lead.lead_status }}
                                    </span>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <div
                                        v-if="lead.email"
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
                                                {{ lead.email }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="lead.phone"
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
                                                {{ lead.phone }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="lead.website"
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
                                                    :href="lead.website"
                                                    target="_blank"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    {{ lead.website }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="lead.industry"
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
                                                Industry
                                            </div>
                                            <div class="text-gray-900">
                                                {{ lead.industry }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="lead.lead_source"
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
                                                {{ lead.lead_source }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="lead.estimated_budget"
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
                                                Estimated Budget
                                            </div>
                                            <div class="text-gray-900">
                                                ${{ lead.estimated_budget }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="lead.owner"
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
                                                {{ lead.owner.name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
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
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">
                                                Created
                                            </div>
                                            <div class="text-gray-900">
                                                {{
                                                    formatDate(lead.created_at)
                                                }}
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
                                            v-if="lead.description"
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
                                                {{ lead.description }}
                                            </p>
                                        </div>

                                        <div v-if="lead.address" class="mb-6">
                                            <h4
                                                class="text-sm font-medium text-gray-500 mb-2"
                                            >
                                                Address
                                            </h4>
                                            <p
                                                class="text-gray-900 whitespace-pre-line"
                                            >
                                                {{ lead.address }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="isConverted"
                                            class="mt-6 p-4 bg-green-50 border border-green-200 rounded-md"
                                        >
                                            <h4
                                                class="text-sm font-medium text-green-800 mb-2"
                                            >
                                                Conversion Information
                                            </h4>
                                            <p class="text-green-700">
                                                This lead was converted on
                                                {{
                                                    formatDate(
                                                        lead.converted_at
                                                    )
                                                }}.
                                            </p>
                                            <div
                                                v-if="lead.convertedContact"
                                                class="mt-2"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'contacts.show',
                                                            lead
                                                                .convertedContact
                                                                .id
                                                        )
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View Contact Record
                                                </Link>
                                            </div>
                                            <div
                                                v-if="lead.convertedDeal"
                                                class="mt-2"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'deals.show',
                                                            lead.convertedDeal
                                                                .id
                                                        )
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View Deal Record
                                                </Link>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes Tab -->
                                    <div v-show="activeTab === 'notes'">
                                        <!-- Notes list -->
                                        <div
                                            v-if="
                                                lead.notes &&
                                                lead.notes.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="note in lead.notes"
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
                                                    v-if="note.creator"
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

                                    <!-- Tasks Tab -->
                                    <div v-show="activeTab === 'tasks'">
                                        <div
                                            v-if="
                                                lead.tasks &&
                                                lead.tasks.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="task in lead.tasks"
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
                                            No tasks associated with this lead
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <Link
                                                :href="
                                                    route('tasks.create', {
                                                        lead_id: lead.id,
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
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    lead: Object,
    can: Object,
    isConverted: Boolean,
});

// Active tab state
const activeTab = ref("details");

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

const getStatusClass = (status) => {
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
};

const addNote = () => {
    noteForm.post(route("leads.add-note", props.lead.id), {
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
<!-- ? task .assignee -->
