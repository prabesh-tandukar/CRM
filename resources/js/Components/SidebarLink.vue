<!-- resources/js/Components/SidebarLink.vue -->
<template>
    <Link
        :href="href"
        :class="[
            'group flex items-center py-2 text-sm font-medium rounded-md transition-all',
            collapsed ? 'px-3 justify-center' : 'px-4',
            active
                ? 'bg-blue-700 text-white'
                : 'text-blue-100 hover:bg-blue-500',
        ]"
        :title="collapsed ? $slots.default()[0].children : ''"
    >
        <div :class="collapsed ? 'mr-0' : 'mr-3'">
            <slot name="icon"></slot>
        </div>
        <span v-show="!collapsed" class="truncate">
            <slot></slot>
        </span>
    </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    href: String,
    active: Boolean,
    collapsed: {
        type: Boolean,
        default: false,
    },
});
</script>
