<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    post: {
        type: Object
    },
    showTime: {
        type:Boolean,
        default: true
    }
})




</script>

<template>
    <div class="flex items-center gap-2">
        <Link :href="route('profile', post.user.username)">
            <img
                :src="post.user.avatar_url || '/storage/default_avatar.png'"
                alt="Avatar"
                class="w-10 h-10 rounded-full hover:ring-2 hover:ring-blue-500 transition-all"
            />
        </Link>

        <div>
            <h4 class="font-bold">
                <Link :href="route('profile', post.user.username)" class="hover:underline">
                    {{ post.user.name }}
                </Link>

                <template v-if="post.group && post.group.slug">
                    >
                    <Link :href="route('groups.show', post.group.slug)" class="hover:underline">
                        {{ post.group.name }}
                    </Link>
                </template>
            </h4>

            <small v-if="showTime" class="text-gray-400">
                {{ post.updated_at }}
            </small>
            

        </div>
    </div>




</template>


