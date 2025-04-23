<!-- resources/js/Pages/Deals/Show.vue -->
<template>
    <AppLayout :title="deal.name">
        <template #header-title>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Deal Details
                </h2>
            </div>
        </template>
        <template #header-actions>
            <div class="flex space-x-2">
                <Link
                    v-if="can.edit"
                    :href="route('deals.edit', deal.id)"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                    Edit Deal
                </Link>
                <Link
                    :href="route('deals.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Back to Deals
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
                    <!-- Deal Info Card -->
                    <div class="md:col-span-1">
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-xl font-medium text-gray-900 mb-4"
                                >
                                    {{ deal.name }}
                                </h3>

                                <div class="mt-2">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="
                                            getStageClass(deal.pipeline_stage)
                                        "
                                    >
                                        {{ deal.pipeline_stage }}
                                    </span>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Amount:</span
                                        >
                                        <span class="text-sm font-medium">{{
                                            formatCurrency(deal.amount)
                                        }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Status:</span
                                        >
                                        <span
                                            class="text-sm font-medium"
                                            :class="{
                                                'text-green-600':
                                                    deal.won === true,
                                                'text-red-600':
                                                    deal.won === false,
                                                'text-blue-600':
                                                    deal.won === null,
                                            }"
                                        >
                                            {{
                                                deal.won === true
                                                    ? "Won"
                                                    : deal.won === false
                                                    ? "Lost"
                                                    : "Open"
                                            }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Probability:</span
                                        >
                                        <span class="text-sm font-medium"
                                            >{{ deal.probability || 0 }}%</span
                                        >
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Expected Close:</span
                                        >
                                        <span class="text-sm font-medium">{{
                                            formatDate(deal.expected_close_date)
                                        }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Source:</span
                                        >
                                        <span class="text-sm font-medium">{{
                                            deal.source || "Not specified"
                                        }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Owner:</span
                                        >
                                        <span class="text-sm font-medium">{{
                                            deal.owner
                                                ? deal.owner.name
                                                : "Unassigned"
                                        }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500"
                                            >Created:</span
                                        >
                                        <span class="text-sm font-medium">{{
                                            formatDate(deal.created_at)
                                        }}</span>
                                    </div>

                                    <div
                                        v-if="deal.actual_close_date"
                                        class="flex justify-between"
                                    >
                                        <span class="text-sm text-gray-500"
                                            >Closed:</span
                                        >
                                        <span class="text-sm font-medium">{{
                                            formatDate(deal.actual_close_date)
                                        }}</span>
                                    </div>

                                    <div
                                        v-if="deal.company"
                                        class="flex justify-between"
                                    >
                                        <span class="text-sm text-gray-500"
                                            >Company:</span
                                        >
                                        <Link
                                            :href="
                                                route(
                                                    'companies.show',
                                                    deal.company.id
                                                )
                                            "
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ deal.company.name }}
                                        </Link>
                                    </div>

                                    <div
                                        v-if="deal.primaryContact"
                                        class="flex justify-between"
                                    >
                                        <span class="text-sm text-gray-500"
                                            >Primary Contact:</span
                                        >
                                        <Link
                                            :href="
                                                route(
                                                    'contacts.show',
                                                    deal.primaryContact.id
                                                )
                                            "
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ deal.primaryContact.first_name }}
                                            {{ deal.primaryContact.last_name }}
                                        </Link>
                                    </div>
                                </div>

                                <!-- Status Actions -->
                                <div
                                    v-if="deal.won === null"
                                    class="mt-6 space-y-2"
                                >
                                    <button
                                        @click="
                                            showStageModal = true;
                                            newStage = 'Closed Won';
                                        "
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        Mark as Won
                                    </button>
                                    <button
                                        @click="
                                            showStageModal = true;
                                            newStage = 'Closed Lost';
                                        "
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        Mark as Lost
                                    </button>
                                </div>

                                <div v-else class="mt-6">
                                    <button
                                        @click="reopenDeal"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            />
                                        </svg>
                                        Reopen Deal
                                    </button>
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
                                            v-if="deal.description"
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
                                                {{ deal.description }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="deal.lost_reason"
                                            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md"
                                        >
                                            <h4
                                                class="text-sm font-medium text-red-800 mb-2"
                                            >
                                                Loss Reason
                                            </h4>
                                            <p
                                                class="text-red-700 whitespace-pre-line"
                                            >
                                                {{ deal.lost_reason }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="
                                                deal.products &&
                                                deal.products.length > 0
                                            "
                                            class="mt-6"
                                        >
                                            <h4
                                                class="text-lg font-medium text-gray-900 mb-4"
                                            >
                                                Products
                                            </h4>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full divide-y divide-gray-200"
                                                >
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Product
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Quantity
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Unit Price
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Discount
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Total
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody
                                                        class="bg-white divide-y divide-gray-200"
                                                    >
                                                        <tr
                                                            v-for="product in deal.products"
                                                            :key="product.id"
                                                        >
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap"
                                                            >
                                                                <div
                                                                    class="text-sm font-medium text-gray-900"
                                                                >
                                                                    {{
                                                                        product.name
                                                                    }}
                                                                </div>
                                                                <div
                                                                    class="text-sm text-gray-500"
                                                                    v-if="
                                                                        product.code
                                                                    "
                                                                >
                                                                    {{
                                                                        product.code
                                                                    }}
                                                                </div>
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    product
                                                                        .pivot
                                                                        .quantity
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        product
                                                                            .pivot
                                                                            .unit_price
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        product
                                                                            .pivot
                                                                            .discount_amount
                                                                    )
                                                                }}
                                                                <span
                                                                    v-if="
                                                                        product
                                                                            .pivot
                                                                            .discount_percent >
                                                                        0
                                                                    "
                                                                    class="text-xs text-gray-400"
                                                                >
                                                                    ({{
                                                                        product
                                                                            .pivot
                                                                            .discount_percent
                                                                    }}%)
                                                                </span>
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        product
                                                                            .pivot
                                                                            .total_price
                                                                    )
                                                                }}
                                                            </td>
                                                        </tr>
                                                        <tr class="bg-gray-50">
                                                            <td
                                                                colspan="4"
                                                                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                                            >
                                                                Total:
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        totalValue
                                                                    )
                                                                }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contacts Tab -->
                                    <div v-show="activeTab === 'contacts'">
                                        <div
                                            v-if="
                                                deal.contacts &&
                                                deal.contacts.length > 0
                                            "
                                        >
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full divide-y divide-gray-200"
                                                >
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Name
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Role
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Email
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Phone
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                            >
                                                                Action
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody
                                                        class="bg-white divide-y divide-gray-200"
                                                    >
                                                        <tr
                                                            v-for="contact in deal.contacts"
                                                            :key="contact.id"
                                                        >
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap"
                                                            >
                                                                <div
                                                                    class="text-sm font-medium text-gray-900"
                                                                >
                                                                    {{
                                                                        contact.first_name
                                                                    }}
                                                                    {{
                                                                        contact.last_name
                                                                    }}
                                                                </div>
                                                                <div
                                                                    class="text-sm text-gray-500"
                                                                    v-if="
                                                                        contact.job_title
                                                                    "
                                                                >
                                                                    {{
                                                                        contact.job_title
                                                                    }}
                                                                </div>
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    contact
                                                                        .pivot
                                                                        .role ||
                                                                    "N/A"
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    contact.email ||
                                                                    "N/A"
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    contact.phone ||
                                                                    "N/A"
                                                                }}
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
                                        </div>
                                        <div
                                            v-else
                                            class="text-center py-6 text-gray-500"
                                        >
                                            No contacts associated with this
                                            deal
                                        </div>
                                    </div>

                                    <!-- Notes Tab -->
                                    <div v-show="activeTab === 'notes'">
                                        <!-- Notes list -->
                                        <div
                                            v-if="
                                                deal.notes &&
                                                deal.notes.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="note in deal.notes"
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

                                    <!-- Tasks Tab -->
                                    <div v-show="activeTab === 'tasks'">
                                        <div
                                            v-if="
                                                deal.tasks &&
                                                deal.tasks.length > 0
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                v-for="task in deal.tasks"
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
                                            No tasks associated with this deal
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <Link
                                                :href="
                                                    route('tasks.create', {
                                                        deal_id: deal.id,
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

        <!-- Stage Update Modal -->
        <Modal :show="showStageModal" @close="showStageModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Update Deal Status
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to mark this deal as
                    {{ newStage === "Closed Won" ? "won" : "lost" }}?
                </p>

                <!-- Loss Reason (for lost deals) -->
                <div v-if="newStage === 'Closed Lost'" class="mt-4">
                    <InputLabel
                        for="lost_reason"
                        value="Loss Reason (Required)"
                    />
                    <textarea
                        id="lost_reason"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="stageForm.lost_reason"
                        rows="3"
                        required
                    ></textarea>
                    <InputError
                        class="mt-2"
                        :message="stageForm.errors.lost_reason"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton
                        @click="showStageModal = false"
                        class="mr-3"
                    >
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        :class="{ 'opacity-25': stageForm.processing }"
                        :disabled="
                            stageForm.processing ||
                            (newStage === 'Closed Lost' &&
                                !stageForm.lost_reason)
                        "
                        @click="updateDealStage"
                    >
                        Confirm
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    deal: Object,
    totalValue: Number,
    can: Object,
});

// Active tab state
const activeTab = ref("details");

// Stage update modal
const showStageModal = ref(false);
const newStage = ref("");
const stageForm = useForm({
    pipeline_stage: "",
    lost_reason: "",
});

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
    if (!value) return "$0";
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const getStageClass = (stage) => {
    switch (stage) {
        case "Qualification":
            return "bg-blue-100 text-blue-800";
        case "Needs Analysis":
            return "bg-indigo-100 text-indigo-800";
        case "Proposal":
            return "bg-purple-100 text-purple-800";
        case "Negotiation":
            return "bg-yellow-100 text-yellow-800";
        case "Closed Won":
            return "bg-green-100 text-green-800";
        case "Closed Lost":
            return "bg-red-100 text-red-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const updateDealStage = () => {
    stageForm.pipeline_stage = newStage.value;
    stageForm.post(route("deals.change-status", props.deal.id), {
        onSuccess: () => {
            showStageModal.value = false;
            stageForm.reset();
        },
    });
};

const reopenDeal = () => {
    stageForm.pipeline_stage = "Qualification";
    stageForm.post(route("deals.change-status", props.deal.id), {
        onSuccess: () => {
            showStageModal.value = false;
        },
    });
};

const addNote = () => {
    noteForm.post(route("deals.add-note", props.deal.id), {
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
