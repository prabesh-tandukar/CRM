<!-- resources/js/Jetstream/NavBar.vue -->
<template>
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <slot name="brand"></slot>
                    </div>
                </div>

                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <!-- Right Side Navigation -->
                    <div class="ml-3 relative">
                        <slot name="right"></slot>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
