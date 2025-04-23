<!-- resources/js/Pages/Leads/Index.vue -->
<template>
    <AppLayout title="Leads">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Leads
                </h2>
            </div>
        </template>
        <template #header-actions>
            <div class="flex items-center space-x-4">
                <Link
                    v-if="can.create"
                    :href="route('leads.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg
                        class="w-5 h-5 mr-2"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Add Lead
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div
                        class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition-shadow"
                        :class="{
                            'border-2 border-blue-500':
                                filters.status === 'All' || !filters.status,
                        }"
                        @click="setStatus('All')"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">
                                    All Leads
                                </div>
                                <div
                                    class="mt-1 text-2xl font-semibold text-gray-900"
                                >
                                    {{ counts.all }}
                                </div>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-blue-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition-shadow"
                        :class="{
                            'border-2 border-green-500':
                                filters.status === 'New',
                        }"
                        @click="setStatus('New')"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">
                                    New Leads
                                </div>
                                <div
                                    class="mt-1 text-2xl font-semibold text-gray-900"
                                >
                                    {{ counts.new }}
                                </div>
                            </div>
                            <div class="rounded-full bg-green-100 p-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition-shadow"
                        :class="{
                            'border-2 border-purple-500':
                                filters.status === 'Converted',
                        }"
                        @click="setStatus('Converted')"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">
                                    Converted Leads
                                </div>
                                <div
                                    class="mt-1 text-2xl font-semibold text-gray-900"
                                >
                                    {{ counts.converted }}
                                </div>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-purple-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 bg-white rounded-lg shadow p-4">
                    <div
                        class="flex flex-wrap items-center justify-between gap-4"
                    >
                        <!-- Search input -->
                        <div class="w-full md:w-1/3">
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search leads..."
                                    class="w-full pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    @input="debouncedSearch"
                                />
                                <div
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Filter dropdowns -->
                        <div class="flex flex-wrap items-center gap-3">
                            <select
                                v-model="filters.status"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @change="filterLeads"
                            >
                                <option :value="null">All Statuses</option>
                                <option
                                    v-for="status in statuses"
                                    :key="status"
                                    :value="status"
                                >
                                    {{ status }}
                                </option>
                            </select>

                            <select
                                v-model="filters.source"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @change="filterLeads"
                            >
                                <option :value="null">All Sources</option>
                                <option
                                    v-for="source in sources"
                                    :key="source"
                                    :value="source"
                                >
                                    {{ source }}
                                </option>
                            </select>

                            <select
                                v-model="filters.perPage"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                @change="filterLeads"
                            >
                                <option :value="10">10 per page</option>
                                <option :value="25">25 per page</option>
                                <option :value="50">50 per page</option>
                                <option :value="100">100 per page</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Leads Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    <button
                                        class="flex items-center"
                                        @click="sortLeads('last_name')"
                                    >
                                        Name
                                        <svg
                                            v-if="
                                                filters.sortField ===
                                                'last_name'
                                            "
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 ml-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                v-if="
                                                    filters.sortDirection ===
                                                    'asc'
                                                "
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    <button
                                        class="flex items-center"
                                        @click="sortLeads('company_name')"
                                    >
                                        Company
                                        <svg
                                            v-if="
                                                filters.sortField ===
                                                'company_name'
                                            "
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 ml-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                v-if="
                                                    filters.sortDirection ===
                                                    'asc'
                                                "
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Email / Phone
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    <button
                                        class="flex items-center"
                                        @click="sortLeads('lead_status')"
                                    >
                                        Status
                                        <svg
                                            v-if="
                                                filters.sortField ===
                                                'lead_status'
                                            "
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 ml-1"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                v-if="
                                                    filters.sortDirection ===
                                                    'asc'
                                                "
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 15l7-7 7 7"
                                            />
                                            <path
                                                v-else
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Owner
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="lead in leads.data"
                                :key="lead.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 rounded-full text-blue-600 font-medium"
                                        >
                                            {{ lead.first_name.charAt(0)
                                            }}{{ lead.last_name.charAt(0) }}
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="font-medium text-gray-900"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'leads.show',
                                                            lead.id
                                                        )
                                                    "
                                                    class="hover:text-blue-600"
                                                >
                                                    {{
                                                        lead.title
                                                            ? lead.title + " "
                                                            : ""
                                                    }}{{ lead.first_name }}
                                                    {{ lead.last_name }}
                                                </Link>
                                            </div>
                                            <div
                                                v-if="lead.converted_at"
                                                class="text-xs text-purple-600"
                                            >
                                                Converted on
                                                {{
                                                    formatDate(
                                                        lead.converted_at
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ lead.company_name || "-" }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="lead.email"
                                        class="text-sm text-gray-900"
                                    >
                                        {{ lead.email }}
                                    </div>
                                    <div
                                        v-if="lead.phone"
                                        class="text-sm text-gray-500"
                                    >
                                        {{ lead.phone }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-blue-100 text-blue-800':
                                                lead.lead_status === 'New',
                                            'bg-yellow-100 text-yellow-800':
                                                lead.lead_status ===
                                                'Contacted',
                                            'bg-green-100 text-green-800':
                                                lead.lead_status ===
                                                'Qualified',
                                            'bg-red-100 text-red-800':
                                                lead.lead_status ===
                                                'Unqualified',
                                            'bg-purple-100 text-purple-800':
                                                lead.converted_at,
                                        }"
                                    >
                                        {{
                                            lead.converted_at
                                                ? "Converted"
                                                : lead.lead_status
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="lead.owner"
                                        class="text-sm text-gray-900"
                                    >
                                        {{ lead.owner.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">
                                        -
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div
                                        class="flex items-center justify-end space-x-2"
                                    >
                                        <Link
                                            :href="route('leads.show', lead.id)"
                                            class="text-blue-600 hover:text-blue-900"
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
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="
                                                can.edit && !lead.converted_at
                                            "
                                            :href="route('leads.edit', lead.id)"
                                            class="text-amber-600 hover:text-amber-900"
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
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="
                                                can.convert &&
                                                !lead.converted_at
                                            "
                                            :href="
                                                route(
                                                    'leads.convert.show',
                                                    lead.id
                                                )
                                            "
                                            class="text-purple-600 hover:text-purple-900"
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
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                                />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="
                                                can.delete && !lead.converted_at
                                            "
                                            @click="confirmDelete(lead)"
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
                                </td>
                            </tr>
                            <tr v-if="leads.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-4 text-center text-gray-500"
                                >
                                    No leads found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4" v-if="leads.data && leads.data.length > 0">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ leads.meta?.from || 0 }} to
                            {{ leads.meta?.to || 0 }} of
                            {{ leads.meta?.total || 0 }} leads
                        </div>
                        <div class="flex space-x-2" v-if="leads.meta?.links">
                            <Link
                                v-for="(link, index) in leads.meta.links"
                                :key="index"
                                :href="link.url"
                                class="px-3 py-1 border rounded-md text-sm"
                                :class="{
                                    'bg-blue-50 border-blue-500 text-blue-600':
                                        link.active,
                                    'border-gray-300 text-gray-600 hover:bg-gray-50':
                                        !link.active && link.url !== null,
                                    'border-gray-200 text-gray-400 cursor-not-allowed':
                                        link.url === null,
                                }"
                                v-html="link.label"
                            ></Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="deleteModal.isOpen"
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
                                        Delete Lead
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to delete
                                            {{ deleteModal.lead?.first_name }}
                                            {{ deleteModal.lead?.last_name }}?
                                            This action cannot be undone.
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
                                @click="deleteLead"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                @click="deleteModal.isOpen = false"
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
import { ref, computed, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    leads: Object,
    statuses: Array,
    sources: Array,
    filters: Object,
    counts: Object,
    can: Object,
});

// Search and filters
const search = ref(props.filters.search || "");
const filters = ref({
    perPage: props.filters.perPage || 10,
    sortField: props.filters.sortField || "created_at",
    sortDirection: props.filters.sortDirection || "desc",
    status: props.filters.status || null,
    source: props.filters.source || null,
});

// Delete confirmation
const deleteModal = ref({
    isOpen: false,
    lead: null,
});

// Methods
const debouncedSearch = debounce(() => {
    filterLeads();
}, 300);

const filterLeads = () => {
    router.get(
        route("leads.index"),
        {
            search: search.value,
            perPage: filters.value.perPage,
            sortField: filters.value.sortField,
            sortDirection: filters.value.sortDirection,
            status: filters.value.status,
            source: filters.value.source,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const sortLeads = (field) => {
    filters.value.sortDirection =
        field === filters.value.sortField &&
        filters.value.sortDirection === "asc"
            ? "desc"
            : "asc";
    filters.value.sortField = field;
    filterLeads();
};

const setStatus = (status) => {
    filters.value.status = status;
    filterLeads();
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const confirmDelete = (lead) => {
    deleteModal.value.lead = lead;
    deleteModal.value.isOpen = true;
};

const deleteLead = () => {
    router.delete(route("leads.destroy", deleteModal.value.lead.id), {
        onSuccess: () => {
            deleteModal.value.isOpen = false;
        },
    });
};

// Watch for URL params changes (e.g. when using browser back/forward)
watch(
    () => usePage().url.value,
    (newUrl) => {
        const url = new URL(newUrl);
        search.value = url.searchParams.get("search") || "";
        filters.value.perPage = parseInt(url.searchParams.get("perPage")) || 10;
        filters.value.sortField =
            url.searchParams.get("sortField") || "created_at";
        filters.value.sortDirection =
            url.searchParams.get("sortDirection") || "desc";
        filters.value.status = url.searchParams.get("status") || null;
        filters.value.source = url.searchParams.get("source") || null;
    }
);
</script>
