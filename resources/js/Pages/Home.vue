<script setup>
import {Head, router} from '@inertiajs/vue3';
import GroupList from '@/Components/app/GroupList.vue';
import CreatePost from '@/Components/app/CreatePost.vue';
import FollowingList from '@/Components/app/FollowingList.vue';
import PostList from '@/Components/app/PostList.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import XmlTools from '@/Components/app/XmlTools.vue';
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    posts: Object,
    groups: Object,
    friends: Object,
    followings: Object,
})

const resourceItems = (resource) => {
    if (Array.isArray(resource)) {
        return resource;
    }

    if (Array.isArray(resource?.data)) {
        return resource.data;
    }

    return [];
};

const homePosts = computed(() => resourceItems(props.posts));
const homeGroups = computed(() => resourceItems(props.groups));
const sidebarFriends = computed(() => {
    const friends = resourceItems(props.friends);

    return friends.length ? friends : resourceItems(props.followings);
});

let sidebarRefreshTimer = null;

function refreshSidebar() {
    router.reload({
        only: ['groups', 'friends', 'followings'],
        preserveScroll: true,
        preserveState: true,
    });
}

onMounted(() => {
    window.addEventListener('social-sidebar:refresh', refreshSidebar);
    window.addEventListener('focus', refreshSidebar);
    refreshSidebar();
    sidebarRefreshTimer = window.setInterval(refreshSidebar, 10000);
});

onUnmounted(() => {
    window.removeEventListener('social-sidebar:refresh', refreshSidebar);
    window.removeEventListener('focus', refreshSidebar);

    if (sidebarRefreshTimer) {
        window.clearInterval(sidebarRefreshTimer);
    }
});

// function handleImageError() {
//     document.getElementById('screenshot-container')?.classList.add('!hidden');
//     document.getElementById('docs-card')?.classList.add('!row-span-1');
//     document.getElementById('docs-card-content')?.classList.add('!flex-row');
//     document.getElementById('background')?.classList.add('!hidden');
// }
</script>

<template>
    <Head title="MyWebsite ecological" />

    <AuthenticatedLayout>

        <div
            class="grid gap-3 overflow-hidden p-4 lg:grid-cols-12"
            style="height: calc(100vh - 4rem); min-height: 0;"
        >

            <div
                class="flex flex-col overflow-hidden rounded-lg bg-white p-4 px-3 lg:order-1 lg:col-span-3"
                style="height: 100%; min-height: 0;"
            >
                <GroupList :groups="homeGroups" class="min-h-0 flex-1" />
            </div>

            <div
                class="flex flex-col overflow-hidden lg:order-2 lg:col-span-6"
                style="min-height: 0;"
            > 
                
                    <CreatePost />
                    <PostList :posts="homePosts" class="flex-1" />
            </div>

            <div
                class="flex flex-col gap-3 overflow-hidden rounded-lg bg-white p-4 px-3 lg:order-3 lg:col-span-3"
                style="height: 100%; min-height: 0;"
            >
                    <XmlTools />
                    <FollowingList :users="sidebarFriends" class="min-h-0 flex-1" />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
