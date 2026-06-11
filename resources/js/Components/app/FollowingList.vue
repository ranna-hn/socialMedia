<script setup>
import TextInput from '@/Components/TextInput.vue';
import { computed, ref } from 'vue';
import FollowingItem from '@/Components/app/FollowingItem.vue';
import { useI18n } from '@/i18n.js';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const searchKeyword = ref('');
const { t } = useI18n();

const filteredUsers = computed(() => {
    const q = searchKeyword.value.trim().toLowerCase();
    if (!q) {
        return props.users;
    }

    return props.users.filter((user) =>
        `${user.name} ${user.username}`.toLowerCase().includes(q)
    );
});

</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-lg border px-3 py-3 shadow-2xl"
        style="min-height: 0;"
    >
        <h2 class="text-xl font-bold mb-4 px-3">{{ t('sidebar.my_friends') }}</h2>

        <TextInput v-model="searchKeyword"
            :placeholder="t('sidebar.search_friends')"
            class="w-full"
        />
            <div
                class="mt-3 flex-1 pr-1"
                style="min-height: 0; overflow-y: auto; overscroll-behavior: contain;"
            >

                <div class="mb-4 text-gray-500 text-center p-3" v-if="filteredUsers.length === 0">
                    {{ t('sidebar.no_friends') }}
                </div>
                
                <div class="space-y-3" v-else>
                    <FollowingItem
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                    />

                </div>
            </div>
    </div>

</template>

<style scoped>

</style>
