<!-- resources/js/Components/DealCard.vue -->
<template>
    <div>
        <div class="mb-1">
            <Link
                :href="route('deals.show', deal.id)"
                class="text-indigo-600 font-medium hover:text-indigo-900"
            >
                {{ deal.name }}
            </Link>
        </div>
        <div class="text-sm text-gray-600">
            {{ deal.company ? deal.company.name : "No Company" }}
        </div>
        <div class="mt-2 flex justify-between items-center">
            <div class="text-sm font-bold">
                {{ formatCurrency(deal.amount) }}
            </div>
            <div class="text-xs text-gray-500">
                {{ formatDate(deal.expected_close_date) }}
            </div>
        </div>
        <div class="mt-1" v-if="deal.owner">
            <div class="flex items-center">
                <div
                    class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-xs text-indigo-600 mr-1"
                >
                    {{ deal.owner.name.charAt(0) }}
                </div>
                <div class="text-xs text-gray-600">{{ deal.owner.name }}</div>
            </div>
        </div>
        <!-- Status indicator based on won field -->
        <div
            v-if="deal.won !== null"
            class="mt-1 text-xs"
            :class="{
                'text-green-600': deal.won === true,
                'text-red-600': deal.won === false,
            }"
        >
            {{ deal.won ? "Won" : "Lost" }}
        </div>
    </div>
</template>

<script>
import { Link } from "@inertiajs/vue3";

export default {
    components: {
        Link,
    },
    props: {
        deal: {
            type: Object,
            required: true,
        },
    },
    methods: {
        formatCurrency(value) {
            if (!value) return "$0";
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(value);
        },
        formatDate(date) {
            if (!date) return "";
            const d = new Date(date);
            return d.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
            });
        },
    },
};
</script>
