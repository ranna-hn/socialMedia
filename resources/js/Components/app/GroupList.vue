<script setup>
import GroupItem from '@/Components/app/GroupItem.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/i18n.js';

const props = defineProps({
    groups: {
        type: Array,
        default: () => [],
    },
});

const searchKeyword = ref('');
const showCreate = ref(false);
const { t } = useI18n();

const filteredGroups = computed(() => {
    const q = searchKeyword.value.trim().toLowerCase();
    if (!q) {
        return props.groups;
    }

    return props.groups.filter((group) =>
        `${group.name} ${group.about || ''}`.toLowerCase().includes(q)
    );
});

const form = useForm({
    name: '',
    about: '',
    auto_approval: true,
    cover: null,
});

function submitGroup() {
    form.post(route('groups.store'), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            form.reset();
            showCreate.value = false;
            router.reload({
                only: ['groups'],
                preserveScroll: true,
                preserveState: false,
            });
        },
    });
}

</script>

<template>
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-lg border px-3 py-3 shadow-2xl"
        style="min-height: 0;"
    >
        <h2 class="text-xl font-bold mb-4 px-3">{{ t('sidebar.my_groups') }}</h2>

        <TextInput v-model="searchKeyword"
            :placeholder="t('sidebar.search_groups')"
            class="mb-4"/>
        <button
            @click="showCreate = !showCreate"
            class="mb-3 rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800"
        >
            {{ t('sidebar.create_group') }}
        </button>

        <form v-if="showCreate" @submit.prevent="submitGroup" class="mb-3 space-y-2 rounded-md border bg-gray-50 p-3">
            <input v-model="form.name" class="w-full rounded-md border-gray-300 text-sm" :placeholder="t('sidebar.group_name')" />
            <textarea v-model="form.about" class="w-full rounded-md border-gray-300 text-sm" rows="2" :placeholder="t('sidebar.description')"></textarea>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.auto_approval" type="checkbox" class="rounded border-gray-300 text-lime-800 focus:ring-lime-800" />
                {{ t('sidebar.auto_approval') }}
            </label>
            <input type="file" @change="form.cover = $event.target.files[0]" class="w-full text-xs" accept="image/*" />
            <div class="space-y-1 text-xs text-red-600">
                <div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
            </div>
            <button type="submit" class="w-full rounded-md bg-lime-800 px-3 py-1.5 text-sm font-semibold text-white">
                {{ t('sidebar.save') }}
            </button>
        </form>
            <div
                class="flex-1 pr-1"
                style="min-height: 0; overflow-y: auto; overscroll-behavior: contain;"
            >
                <div class="mb-4 text-gray-500 text-center p-3" v-if="filteredGroups.length === 0">
                    {{ t('sidebar.no_groups') }}
                </div>
                
                <div  v-else>
                    <GroupItem
                        v-for="group in filteredGroups"
                        :key="group.id"
                        :group="group"
                    />

                </div>
            </div>    
    </div>

</template>

<style scoped>

</style>


