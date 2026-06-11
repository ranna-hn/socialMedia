<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/i18n.js';

defineProps({
    group: Object,
});

const { t } = useI18n();

</script>

<template>
    <Link :href="route('groups.show', group.slug)" class=" group flex items-start gap-3 p-3 rounded-lg hover:bg-lime-100 transition duration-150 ease-in-out cursor-pointer">
        <div class="flex items-center gap-2 rounded-lg">
            <img :src="group.cover_url || '/storage/1.jpg'" :alt="group.name" class="w-14 h-14 object-cover rounded-full" />
            <div class="min-w-0">
                <h3 class="font-black text-sm truncate">{{ group.name }}</h3>
                <div class="text-xs text-gray-500 group-hover:text-black line-clamp-2">{{ group.about }}</div>
                <div class="text-[11px] text-gray-400">{{ group.member_count }} {{ t('sidebar.members') }}</div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
</style>
