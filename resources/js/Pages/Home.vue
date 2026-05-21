<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GroupList from '@/Components/App/GroupList.vue';
import CreatePost from '@/Components/App/CreatePost.vue';
import FollowingList from '@/Components/App/FollowingList.vue';
import PostList from '@/Components/App/PostList.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';


function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="MyWebsite" />

    <AuthenticatedLayout>
            <template #header>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                
                </h2>
            </template>

        <div class="grid lg:grid-cols-12 gap-3 p-4 h-full ">

            <div class="lg:col-span-3 lg:order-1 bg-lime-100 p-4 rounded-lg px-3 h-full overflow-hidden">
                <GroupList />
            </div>

            <div class="lg:col-span-6 lg:order-2 overflow-hidden flex flex-col"> 
                
                    <CreatePost />
                    <PostList class="flex-1" />
            </div>

            <div class="lg:col-span-3 lg:order-3  bg-lime-100 p-4 rounded-lg px-3 h-full overflow-hidden">
                    <FollowingList />
            </div>

        </div>
    </AuthenticatedLayout>
</template>