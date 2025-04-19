<!-- resources/js/Pages/Deals/Show.vue -->
<template>
    <AppLayout :title="deal.name">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Deal Details
                </h2>
                <div class="flex space-x-2">
                    <Link
                        v-if="can.edit"
                        :href="route('deals.edit', deal.id)"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6">
                    <div class="p-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ $page.props.flash.success }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Deal Info Card -->
                    <div class="md:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-xl font-medium text-gray-900 mb-4">
                                    {{ deal.name }}
                                </h3>
                                
                                <div class="mt-2">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStageClass(deal.pipeline_stage)"
                                    >
                                        {{ deal.pipeline_stage }}
                                    </span>
                                </div>
                                
                                <div class="mt-4 space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Amount:</span>
                                        <span class="text-sm font-medium">{{ formatCurrency(deal.amount) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Status:</span>
                                        <span
                                            class="text-sm font-medium"
                                            :class="{
                                                'text-green-600': deal.status === 'Won',
                                                'text-red-600': deal.status === 'Lost',
                                                'text-blue-600': deal.status === 'Open'
                                            }"
                                        >
                                            {{ deal.status }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Probability:</span>
                                        <span class="text-sm font-medium">{{ deal.probability || 0 }}%</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Expected Close:</span>
                                        <span class="text-sm font-medium">{{ formatDate(deal.expected_close_date) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Source:</span>
                                        <span class="text-sm font-medium">{{ deal.source || 'Not specified' }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Owner:</span>
                                        <span class="text-sm font-medium">{{ deal.owner ? deal.owner.name : 'Unassigned' }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Created:</span>
                                        <span class="text-sm font-medium">{{ formatDate(deal.created_at) }}</span>
                                    </div>
                                    
                                    <div v-if="deal.closed_date" class="flex justify-between">
                                        <span class="text-sm text-gray-500">Closed:</span>
                                        <span class="text-sm font-medium">{{ formatDate(deal.closed_date) }}</span>
                                    </div>
                                    
                                    <div v-if="deal.company" class="flex justify-between">
                                        <span class="text-sm text-gray-500">Company:</span>
                                        <Link :href="route('companies.show', deal.company.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            {{ deal.company.name }}
                                        </Link>
                                    </div>
                                    
                                    <div v-if="deal.primaryContact" class="flex justify-between">
                                        <span class="text-sm text-gray-500">Primary Contact:</span>
                                        <Link :href="route('contacts.show', deal.primaryContact.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            {{ deal.primaryContact.first_name }} {{ deal.primaryContact.last_name }}
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Status Actions -->
                                <div v-if="deal.status === 'Open'" class="mt-6 space-y-2">
                                    <button
                                        @click="showStatusModal = true; newStatus = 'Won'"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Won
                                    </button>
                                    <button
                                        @click="showStatusModal = true; newStatus = 'Lost'"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mark as Lost
                                    </button>
                                </div>
                                
                                <div v-else class="mt-6">
                                    <button
                                        @click="reopenDeal"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Reopen Deal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs and Related Items -->
                    <div class="md:col-span-3">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                                        <div v-if="deal.description" class="mb-6">
                                            <h4 class="text-sm font-medium text-gray-500 mb-2">
                                                Description
                                            </h4>
                                            <p class="text-gray-900 whitespace-pre-line">
                                                {{ deal.description }}
                                            </p>
                                        </div>

                                        <div v-if="deal.loss_reason" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                                            <h4 class="text-sm font-medium text-red-800 mb-2">
                                                Loss Reason
                                            </h4>
                                            <p class="text-red-700 whitespace-pre-line">
                                                {{ deal.loss_reason }}
                                            </p>
                                        </div>
                                        
                                        <div v-if="deal.products && deal.products.length > 0" class="mt-6">
                                            <h4 class="text-lg font-medium text-gray-900 mb-4">
                                                Products
                                            </h4>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Product
                                                            </th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Quantity
                                                            </th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Unit Price
                                                            </th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Discount
                                                            </th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Total
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <tr v-for="product in deal.products" :key="product.id">
                                                            <td class="px-6 py-4 whit