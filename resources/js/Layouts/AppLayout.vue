<!-- resources/js/Layouts/AppLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-100">
        <Head :title="title" />

        <Banner />

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div
                :class="[
                    'bg-blue-600 text-white transition-all duration-300 ease-in-out hidden md:block relative',
                    collapsed ? 'w-16' : 'w-64',
                ]"
            >
                <!-- Logo and Collapse Button -->
                <div
                    class="p-4 flex items-center justify-between border-b border-blue-500"
                >
                    <Link
                        :href="route('dashboard')"
                        class="text-xl font-bold text-white truncate"
                        v-show="!collapsed"
                    >
                        CRM System
                    </Link>
                    <div v-show="collapsed" class="w-full flex justify-center">
                        <Link
                            :href="route('dashboard')"
                            class="text-xl font-bold text-white"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                />
                            </svg>
                        </Link>
                    </div>
                    <button
                        @click="collapsed = !collapsed"
                        class="text-white p-1 rounded hover:bg-blue-500 focus:outline-none absolute right-0 mr-4"
                        :class="{ 'right-0 -mr-3 top-4': collapsed }"
                    >
                        <svg
                            v-if="collapsed"
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
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                        <svg
                            v-else
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
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="overflow-y-auto h-full pb-20">
                    <nav class="mt-2">
                        <div
                            class="px-4 py-2 text-xs uppercase tracking-wider text-blue-300"
                            v-if="!collapsed"
                        >
                            Main
                        </div>
                        <div class="h-4" v-else></div>
                        <SidebarLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    />
                                </svg>
                            </template>
                            Dashboard
                        </SidebarLink>

                        <div
                            class="mt-4 px-4 py-2 text-xs uppercase tracking-wider text-blue-300"
                            v-if="!collapsed"
                        >
                            Contacts
                        </div>
                        <div class="h-4" v-else></div>
                        <SidebarLink
                            :href="route('contacts.index')"
                            :active="route().current('contacts.*')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </template>
                            Contacts
                        </SidebarLink>
                        <SidebarLink
                            :href="route('companies.index')"
                            :active="route().current('companies.*')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </template>
                            Companies
                        </SidebarLink>

                        <div
                            class="mt-4 px-4 py-2 text-xs uppercase tracking-wider text-blue-300"
                            v-if="!collapsed"
                        >
                            Sales
                        </div>
                        <div class="h-4" v-else></div>
                        <SidebarLink
                            :href="route('leads.index')"
                            :active="route().current('leads.*')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </template>
                            Leads
                        </SidebarLink>
                        <SidebarLink
                            :href="route('deals.index')"
                            :active="
                                route().current('deals.index') ||
                                route().current('deals.create') ||
                                route().current('deals.edit') ||
                                route().current('deals.show')
                            "
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </template>
                            Deals
                        </SidebarLink>
                        <SidebarLink
                            :href="route('deals.pipeline')"
                            :active="route().current('deals.pipeline')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </template>
                            Pipeline
                        </SidebarLink>

                        <div
                            class="mt-4 px-4 py-2 text-xs uppercase tracking-wider text-blue-300"
                            v-if="!collapsed"
                        >
                            Productivity
                        </div>
                        <div class="h-4" v-else></div>
                        <SidebarLink
                            :href="route('tasks.index')"
                            :active="route().current('tasks.*')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                    />
                                </svg>
                            </template>
                            Tasks
                        </SidebarLink>
                        <SidebarLink
                            :href="route('documents.index')"
                            :active="route().current('documents.*')"
                            :collapsed="collapsed"
                        >
                            <template #icon>
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
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                    />
                                </svg>
                            </template>
                            Documents
                        </SidebarLink>

                        <!-- Admin section, only visible for admin users -->
                        <template
                            v-if="
                                $page.props.auth.user &&
                                ($page.props.auth.user.roles.includes(
                                    'Admin'
                                ) ||
                                    $page.props.auth.user.roles.includes(
                                        'Manager'
                                    ))
                            "
                        >
                            <div
                                class="mt-4 px-4 py-2 text-xs uppercase tracking-wider text-blue-300"
                                v-if="!collapsed"
                            >
                                Admin
                            </div>
                            <div class="h-4" v-else></div>
                            <SidebarLink
                                :href="route('users.index')"
                                :active="route().current('users.*')"
                                :collapsed="collapsed"
                            >
                                <template #icon>
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
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                        />
                                    </svg>
                                </template>
                                Users
                            </SidebarLink>
                            <SidebarLink
                                :href="route('settings.index')"
                                :active="route().current('settings.*')"
                                :collapsed="collapsed"
                            >
                                <template #icon>
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
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                </template>
                                Settings
                            </SidebarLink>
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Navigation Bar -->
                <div class="bg-white shadow-sm z-10">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-14">
                            <div class="flex">
                                <!-- Mobile Sidebar Toggle -->
                                <button
                                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-600 transition"
                                    @click.prevent="
                                        showMobileSidebar = !showMobileSidebar
                                    "
                                >
                                    <svg
                                        class="h-5 w-5"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{
                                                hidden: showMobileSidebar,
                                                'inline-flex':
                                                    !showMobileSidebar,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{
                                                hidden: !showMobileSidebar,
                                                'inline-flex':
                                                    showMobileSidebar,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                                <div class="flex items-center ml-2 md:ml-0">
                                    <slot name="header-title"></slot>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <!-- User Menu -->
                                <div class="relative">
                                    <div>
                                        <button
                                            @click="
                                                userMenuOpen = !userMenuOpen
                                            "
                                            class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800 focus:outline-none transition duration-150 ease-in-out"
                                        >
                                            <span
                                                class="mr-2 hidden sm:inline-block"
                                                >{{
                                                    $page.props.auth.user.name
                                                }}</span
                                            >
                                            <svg
                                                class="h-5 w-5"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                    <div
                                        v-show="userMenuOpen"
                                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                                        role="menu"
                                    >
                                        <Link
                                            :href="route('profile.edit')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            role="menuitem"
                                            >Your Profile</Link
                                        >
                                        <form
                                            method="POST"
                                            @submit.prevent="logout"
                                        >
                                            <button
                                                type="submit"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                role="menuitem"
                                            >
                                                Sign out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Header with Actions -->
                <div class="bg-white border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between py-4">
                            <!-- Left side: Additional header content if needed -->
                            <div class="flex items-center">
                                <slot name="header-content"></slot>
                            </div>

                            <!-- Right side: Action buttons -->
                            <div class="flex items-center">
                                <slot name="header-actions"></slot>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Sidebar -->
                <div
                    v-show="showMobileSidebar"
                    class="fixed inset-0 flex z-40 md:hidden"
                >
                    <div
                        class="fixed inset-0"
                        @click="showMobileSidebar = false"
                    >
                        <div
                            class="absolute inset-0 bg-gray-600 opacity-75"
                        ></div>
                    </div>
                    <div
                        class="relative flex-1 flex flex-col max-w-xs w-full bg-blue-600"
                    >
                        <div class="absolute top-0 right-0 -mr-12 pt-2">
                            <button
                                class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                                @click="showMobileSidebar = false"
                            >
                                <span class="sr-only">Close sidebar</span>
                                <svg
                                    class="h-6 w-6 text-white"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div
                            class="flex-shrink-0 flex items-center px-4 py-3 border-b border-blue-500"
                        >
                            <div class="text-xl font-bold text-white">
                                CRM System
                            </div>
                        </div>
                        <div class="mt-2 flex-1 h-0 overflow-y-auto">
                            <nav class="px-2 space-y-1">
                                <!-- Dashboard -->
                                <Link
                                    :href="route('dashboard')"
                                    class="group flex items-center px-2 py-2 text-base font-medium rounded-md"
                                    :class="
                                        route().current('dashboard')
                                            ? 'bg-blue-700 text-white'
                                            : 'text-blue-100 hover:bg-blue-500'
                                    "
                                >
                                    <svg
                                        class="mr-4 h-6 w-6 text-blue-200"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                        />
                                    </svg>
                                    Dashboard
                                </Link>

                                <!-- Contacts -->
                                <div class="pt-4 pb-2">
                                    <h3
                                        class="text-xs uppercase tracking-wider text-blue-300 px-2"
                                    >
                                        Contacts
                                    </h3>
                                </div>
                                <Link
                                    :href="route('contacts.index')"
                                    class="group flex items-center px-2 py-2 text-base font-medium rounded-md"
                                    :class="
                                        route().current('contacts.*')
                                            ? 'bg-blue-700 text-white'
                                            : 'text-blue-100 hover:bg-blue-500'
                                    "
                                >
                                    <svg
                                        class="mr-4 h-6 w-6 text-blue-200"
                                        xmlns="http://www.w3.org/2000/svg"
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
                                    Contacts
                                </Link>
                                <Link
                                    :href="route('companies.index')"
                                    class="group flex items-center px-2 py-2 text-base font-medium rounded-md"
                                    :class="
                                        route().current('companies.*')
                                            ? 'bg-blue-700 text-white'
                                            : 'text-blue-100 hover:bg-blue-500'
                                    "
                                >
                                    <svg
                                        class="mr-4 h-6 w-6 text-blue-200"
                                        xmlns="http://www.w3.org/2000/svg"
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
                                    Companies
                                </Link>

                                <!-- Add other mobile sidebar links for Sales, Productivity, etc. following the same pattern -->
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-gray-100">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            <slot></slot>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import Banner from "@/Components/Banner.vue";
import SidebarLink from "@/Components/SidebarLink.vue";

defineProps({
    title: String,
});

const showMobileSidebar = ref(false);
const collapsed = ref(false);
const userMenuOpen = ref(false);

const logout = () => {
    router.post(route("logout"));
};
</script>
