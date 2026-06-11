<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ArrowDownTrayIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import { useI18n } from '@/i18n.js';

const authUser = usePage().props.auth.user;
const { t } = useI18n();

const form = useForm({
    type: 'posts',
    xml: null,
});

function importXml() {
    form.post(route('xml.import'), {
        preserveScroll: true,
        onSuccess: () => form.reset('xml'),
    });
}
</script>

<template>
    <section class="mb-4 border-b pb-4">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">{{ t('xml.title') }}</h2>
            <div class="flex gap-2">
                <a
                    :href="route('xml.posts.export')"
                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200"
                >
                    <ArrowDownTrayIcon class="mr-1 h-4 w-4" />
                    {{ t('xml.posts') }}
                </a>
                <a
                    v-if="authUser.is_admin"
                    :href="route('xml.users.export')"
                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-200"
                >
                    <ArrowDownTrayIcon class="mr-1 h-4 w-4" />
                    {{ t('xml.users') }}
                </a>
            </div>
        </div>

        <form @submit.prevent="importXml" class="space-y-2">
            <select v-model="form.type" class="w-full rounded-md border-gray-300 text-sm">
                <option value="posts">{{ t('xml.posts') }}</option>
                <option v-if="authUser.is_admin" value="users">{{ t('xml.users') }}</option>
            </select>
            <input
                type="file"
                accept=".xml,text/xml,application/xml"
                class="w-full text-xs"
                @change="form.xml = $event.target.files[0]"
            />
            <div v-if="form.errors.xml || form.errors.type" class="text-xs text-red-600">
                {{ form.errors.xml || form.errors.type }}
            </div>
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800"
            >
                <ArrowUpTrayIcon class="mr-2 h-4 w-4" />
                {{ t('xml.import_xml') }}
            </button>
        </form>
    </section>
</template>
