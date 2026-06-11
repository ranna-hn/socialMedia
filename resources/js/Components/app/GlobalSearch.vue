<script setup>
import { Link } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import axiosClient from '@/axiosClient.js';
import { computed, ref, watch } from 'vue';
import { useI18n } from '@/i18n.js';

const query = ref('');
const results = ref({ users: [], groups: [], posts: [] });
const loading = ref(false);
let timer = null;
const { t } = useI18n();

const hasResults = computed(() =>
    results.value.users.length || results.value.groups.length || results.value.posts.length
);

watch(query, (value) => {
    clearTimeout(timer);

    if (value.trim().length < 2) {
        results.value = { users: [], groups: [], posts: [] };
        return;
    }

    timer = setTimeout(async () => {
        loading.value = true;
        const { data } = await axiosClient.get(route('search.global'), {
            params: { q: value },
        });
        results.value = data;
        loading.value = false;
    }, 250);
});

function clearSearch() {
    query.value = '';
    results.value = { users: [], groups: [], posts: [] };
}
</script>

<template>
    <div class="relative w-full max-w-xl">
        <div class="relative">
            <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
                v-model="query"
                type="search"
                class="w-full rounded-md border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-lime-700 focus:ring-lime-700"
                :placeholder="t('search.placeholder')"
            />
        </div>

        <div
            v-if="query.trim().length >= 2"
            class="absolute left-0 right-0 top-11 z-40 max-h-96 overflow-auto rounded-md border bg-white shadow-lg"
        >
            <div v-if="loading" class="p-3 text-sm text-gray-500">{{ t('search.searching') }}</div>
            <div v-else-if="!hasResults" class="p-3 text-sm text-gray-500">{{ t('search.no_results') }}</div>
            <template v-else>
                <div v-for="section in ['users', 'groups', 'posts']" :key="section">
                    <div v-if="results[section].length" class="border-b px-3 py-2 text-xs font-bold uppercase text-gray-400">
                        {{ section }}
                    </div>
                    <Link
                        v-for="item in results[section]"
                        :key="`${item.type}-${item.id}`"
                        :href="item.url"
                        @click="clearSearch"
                        class="block border-b px-3 py-2 hover:bg-lime-50"
                    >
                        <div class="text-sm font-semibold text-gray-900">{{ item.title }}</div>
                        <div class="truncate text-xs text-gray-500">{{ item.subtitle }}</div>
                    </Link>
                </div>
            </template>
        </div>
    </div>
</template>
