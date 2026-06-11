<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { GlobeAltIcon } from '@heroicons/vue/24/outline';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from '@/i18n.js';

const page = usePage();
const open = ref(false);
const { t } = useI18n();
const languages = [
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' },
];

const currentLocale = computed(() => page.props.locale || 'en');

function switchLocale(locale) {
    localStorage.setItem('locale', locale);
    open.value = false;

    router.post(route('locale.switch', locale), {}, {
        preserveScroll: true,
    });
}

onMounted(() => {
    const savedLocale = localStorage.getItem('locale');

    if (['fr', 'en'].includes(savedLocale) && savedLocale !== currentLocale.value) {
        router.post(route('locale.switch', savedLocale), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
});
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-lime-700"
            :aria-label="t('nav.language')"
        >
            <GlobeAltIcon class="h-6 w-6" />
        </button>

        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        ></div>

        <div
            v-show="open"
            class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-md bg-white py-1 shadow-lg ring-1 ring-black/10"
        >
            <button
                v-for="language in languages"
                :key="language.code"
                @click="switchLocale(language.code)"
                type="button"
                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-lime-50"
                :class="language.code === currentLocale ? 'font-bold text-lime-900' : 'text-gray-700'"
            >
                <span>{{ language.flag }}</span>
                <span>{{ language.label }}</span>
            </button>
        </div>
    </div>
</template>
