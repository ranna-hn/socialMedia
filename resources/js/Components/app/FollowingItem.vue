<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { UserMinusIcon, UserPlusIcon } from '@heroicons/vue/24/outline';
import { useI18n } from '@/i18n.js';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    showFollowButton: {
        type: Boolean,
        default: false,
    },
});

const authUser = usePage().props.auth.user;
const { t } = useI18n();

const isCurrentUser = computed(() =>
    props.user?.is_current_user || (authUser && authUser.id === props.user?.id)
);

const canShowFollowButton = computed(() =>
    props.showFollowButton && props.user && !isCurrentUser.value
);

const isFollowing = computed(() => Boolean(props.user?.is_following));

function refreshSocialSidebar() {
    window.dispatchEvent(new Event('social-sidebar:refresh'));
}

function followUser() {
    router.post(route('users.follow', props.user.username), {}, {
        preserveScroll: true,
        onSuccess: refreshSocialSidebar,
    });
}

function unfollowUser() {
    router.delete(route('users.unfollow', props.user.username), {
        preserveScroll: true,
        onSuccess: refreshSocialSidebar,
    });
}

</script>

<template>
    <div class="group flex items-center justify-between gap-3 rounded-lg p-3 transition duration-150 ease-in-out hover:bg-lime-100">
        <Link :href="route('profile', user.username)" class="flex min-w-0 flex-1 items-center gap-2 rounded-lg py-2">
            <img :src="user.avatar_url || '/storage/default_avatar.png'" :alt="user.name" class="w-14 h-14 object-cover rounded-full" />
            <div class="min-w-0">
                <h3 class="truncate text-sm font-black">{{ user.name }}</h3>
                <div class="text-xs text-gray-500">@{{ user.username }}</div>
            </div>
        </Link>

        <button
            v-if="canShowFollowButton && !isFollowing"
            type="button"
            @click="followUser"
            class="inline-flex shrink-0 items-center rounded-md bg-lime-900 px-3 py-2 text-xs font-semibold text-white hover:bg-lime-800"
        >
            <UserPlusIcon class="mr-1 h-4 w-4" />
            {{ t('profile.follow') }}
        </button>

        <button
            v-else-if="canShowFollowButton"
            type="button"
            @click="unfollowUser"
            class="inline-flex shrink-0 items-center rounded-md bg-gray-200 px-3 py-2 text-xs font-semibold text-gray-800 hover:bg-gray-300"
        >
            <UserMinusIcon class="mr-1 h-4 w-4" />
            {{ t('profile.unfollow') }}
        </button>
    </div>
</template>

<style scoped>

</style>
