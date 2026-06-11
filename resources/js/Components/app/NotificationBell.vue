<script setup>
import axiosClient from '@/axiosClient.js';
import { router } from '@inertiajs/vue3';
import { BellIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from '@/i18n.js';

const open = ref(false);
const loading = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
let pollTimer = null;
const { t } = useI18n();

const badgeCount = computed(() => unreadCount.value > 99 ? '99+' : unreadCount.value);

async function fetchNotifications() {
    loading.value = true;

    try {
        const { data } = await axiosClient.get(route('notifications.index'));
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } finally {
        loading.value = false;
    }
}

async function openDropdown() {
    open.value = !open.value;

    if (open.value) {
        await fetchNotifications();
    }
}

async function markRead(notification) {
    if (notification.unread) {
        const { data } = await axiosClient.post(route('notifications.read', notification.id));
        unreadCount.value = data.unread_count || 0;
        notifications.value = notifications.value.map((item) =>
            item.id === notification.id ? data.notification : item
        );
    }

    open.value = false;
    router.visit(notification.link || route('dashboard'));
}

async function markAllRead() {
    const { data } = await axiosClient.post(route('notifications.readAll'));
    unreadCount.value = data.unread_count || 0;
    notifications.value = notifications.value.map((notification) => ({
        ...notification,
        unread: false,
        read_at: notification.read_at || new Date().toISOString(),
    }));
}

onMounted(() => {
    fetchNotifications();
    pollTimer = window.setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollTimer) {
        window.clearInterval(pollTimer);
    }
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="openDropdown"
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-lime-700"
            :aria-label="t('nav.notifications')"
        >
            <BellIcon class="h-6 w-6" />
            <span
                v-if="unreadCount"
                class="absolute -right-0.5 -top-0.5 min-w-[1.15rem] rounded-full bg-red-600 px-1 text-center text-[10px] font-bold leading-5 text-white"
            >
                {{ badgeCount }}
            </span>
        </button>

        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        ></div>

        <div
            v-show="open"
            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black/10"
        >
            <div class="flex items-center justify-between border-b px-3 py-2">
                <h3 class="text-sm font-bold text-gray-900">{{ t('nav.notifications') }}</h3>
                <button
                    v-if="unreadCount"
                    @click.stop="markAllRead"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold text-lime-900 hover:bg-lime-50"
                >
                    <CheckIcon class="h-4 w-4" />
                    {{ t('nav.mark_all_read') }}
                </button>
            </div>

            <div class="max-h-96 overflow-auto">
                <div v-if="loading && notifications.length === 0" class="p-4 text-sm text-gray-500">
                    {{ t('nav.loading') }}
                </div>
                <div v-else-if="notifications.length === 0" class="p-4 text-sm text-gray-500">
                    {{ t('nav.no_notifications') }}
                </div>

                <button
                    v-for="notification in notifications"
                    :key="notification.id"
                    @click="markRead(notification)"
                    type="button"
                    class="flex w-full gap-3 border-b px-3 py-3 text-left hover:bg-lime-50"
                    :class="notification.unread ? 'bg-lime-50/70' : 'bg-white'"
                >
                    <img
                        :src="notification.actor?.avatar_url || '/storage/default_avatar.png'"
                        :alt="notification.actor?.name || t('nav.notifications')"
                        class="h-10 w-10 rounded-full object-cover"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-800">
                            {{ notification.message }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ notification.created_at_human }}
                        </p>
                    </div>
                    <span
                        v-if="notification.unread"
                        class="mt-1 h-2 w-2 shrink-0 rounded-full bg-red-600"
                    ></span>
                </button>
            </div>
        </div>
    </div>
</template>
