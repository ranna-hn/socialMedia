import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

function valueFromPath(source, path) {
    return path.split('.').reduce((value, segment) => value?.[segment], source);
}

export function useI18n() {
    const page = usePage();
    const locale = computed(() => page.props.locale || 'en');

    function t(key, replacements = {}) {
        let value = valueFromPath(page.props.translations || {}, key) || key;

        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replaceAll(`:${name}`, replacement);
        });

        return value;
    }

    return { locale, t };
}
