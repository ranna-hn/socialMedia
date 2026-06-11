<script setup>
import axiosClient from '@/axiosClient.js';
import { useI18n } from '@/i18n.js';
import { computed, onMounted, ref } from 'vue';

const WEATHER_CACHE_KEY = 'econature_weather_cache';
const WEATHER_UNIT_KEY = 'econature_weather_unit';
const CACHE_TTL = 10 * 60 * 1000;

const open = ref(false);
const weather = ref(null);
const loading = ref(false);
const unit = ref('metric');
const { t } = useI18n();

const hasWeather = computed(() => Boolean(weather.value?.main));
const unitLabel = computed(() => unit.value === 'imperial' ? 'F' : 'C');

const icon = computed(() => {
    if (!weather.value?.weather?.length) {
        return '';
    }

    const condition = weather.value.weather[0];
    const main = String(condition.main || '').toLowerCase();
    const description = String(condition.description || '').toLowerCase();
    const cloudiness = Number(weather.value.clouds?.all || 0);
    const windSpeed = Number(weather.value.wind?.speed || 0);

    if (main.includes('thunderstorm')) return '⛈️';
    if (main.includes('snow')) return '❄️';
    if (main.includes('rain') || main.includes('drizzle')) return '🌧️';
    if (['mist', 'fog', 'haze', 'smoke', 'dust', 'sand', 'ash'].some((value) => main.includes(value))) return '🌫️';
    if (main.includes('squall') || main.includes('tornado') || windSpeed >= 10) return '💨';
    if (main.includes('cloud')) return cloudiness <= 55 || description.includes('few') ? '⛅' : '☁️';
    if (main.includes('clear')) return '☀️';

    return '🌡️';
});

const cityName = computed(() => weather.value?.name || weather.value?.fallbackCity || '');
const description = computed(() => {
    const text = weather.value?.weather?.[0]?.description || '';
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
});

const currentTemp = computed(() => formatTemp(weather.value?.main?.temp));
const minTemp = computed(() => formatTemp(weather.value?.main?.temp_min));
const maxTemp = computed(() => formatTemp(weather.value?.main?.temp_max));

function formatTemp(value) {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
        return null;
    }

    const metricValue = Number(value);
    const displayValue = unit.value === 'imperial'
        ? (metricValue * 9 / 5) + 32
        : metricValue;

    return Math.round(displayValue);
}

function toggleUnit() {
    unit.value = unit.value === 'metric' ? 'imperial' : 'metric';
    localStorage.setItem(WEATHER_UNIT_KEY, unit.value);
}

function readCachedWeather() {
    try {
        const cached = JSON.parse(localStorage.getItem(WEATHER_CACHE_KEY) || 'null');

        if (cached?.data && cached?.expiresAt > Date.now()) {
            weather.value = cached.data;
            return true;
        }
    } catch {
        localStorage.removeItem(WEATHER_CACHE_KEY);
    }

    return false;
}

function writeCachedWeather(data) {
    localStorage.setItem(WEATHER_CACHE_KEY, JSON.stringify({
        data,
        expiresAt: Date.now() + CACHE_TTL,
    }));
}

function getBrowserLocation(defaultLocation) {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            resolve(defaultLocation);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => resolve({
                lat: position.coords.latitude,
                lon: position.coords.longitude,
            }),
            () => resolve(defaultLocation),
            {
                enableHighAccuracy: false,
                timeout: 5000,
                maximumAge: CACHE_TTL,
            },
        );
    });
}

async function loadWeather() {
    if (readCachedWeather()) {
        return;
    }

    loading.value = true;

    try {
        const { data: config } = await axiosClient.get(route('weather.config'));

        if (!config?.apiKey) {
            return;
        }

        const defaultLocation = config.defaultLocation || {
            city: 'Paris',
            lat: 48.8566,
            lon: 2.3522,
        };

        const location = await getBrowserLocation(defaultLocation);
        const params = new URLSearchParams({
            lat: location.lat,
            lon: location.lon,
            appid: config.apiKey,
            units: 'metric',
        });

        const response = await fetch(`${config.endpoint}?${params.toString()}`);

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        data.fallbackCity = location.city;
        weather.value = data;
        writeCachedWeather(data);
    } catch {
        weather.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    unit.value = localStorage.getItem(WEATHER_UNIT_KEY) || 'metric';
    loadWeather();
});
</script>

<template>
    <div
        class="relative ms-3"
        @mouseenter="open = true"
        @mouseleave="open = false"
    >
        <!-- <button
            type="button"
            @click="open = !open"
            class="inline-flex h-10 items-center gap-1.5 rounded-full border border-lime-100 bg-lime-50 px-2.5 text-sm font-semibold text-lime-950 shadow-sm transition hover:bg-lime-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
            :aria-label="t('weather.label')"
        >
            <span class="text-lg leading-none">{{ icon }}</span>
            <span v-if="hasWeather && currentTemp !== null" class="hidden sm:inline">
                {{ currentTemp }}°{{ unitLabel }}
            </span>
        </button> -->

        <div
            v-if="open && hasWeather"
            class="absolute left-0 z-50 mt-2 w-64 rounded-lg border border-lime-100 bg-white p-4 text-sm shadow-lg ring-1 ring-black/5"
        >
            <div class="mb-3 flex items-start justify-between gap-3">
                <div>
                    <div class="font-black text-gray-900">{{ cityName }}</div>
                    <div class="text-gray-500">{{ description }}</div>
                </div>
                <button
                    type="button"
                    @click.stop="toggleUnit"
                    class="rounded-full bg-lime-900 px-3 py-1 text-xs font-bold text-white hover:bg-lime-800"
                >
                    °{{ unitLabel }}
                </button>
            </div>

            <dl class="grid grid-cols-2 gap-2 text-gray-600">
                <div class="rounded-md bg-lime-50 p-2">
                    <dt class="text-xs font-bold uppercase text-lime-900">{{ t('weather.now') }}</dt>
                    <dd class="text-base font-black text-gray-900">{{ currentTemp }}°{{ unitLabel }}</dd>
                </div>
                <div class="rounded-md bg-gray-50 p-2">
                    <dt class="text-xs font-bold uppercase text-gray-500">{{ t('weather.min_max') }}</dt>
                    <dd class="font-semibold text-gray-900">{{ minTemp }}° / {{ maxTemp }}°</dd>
                </div>
                <div class="rounded-md bg-gray-50 p-2">
                    <dt class="text-xs font-bold uppercase text-gray-500">{{ t('weather.humidity') }}</dt>
                    <dd class="font-semibold text-gray-900">{{ weather.main.humidity }}%</dd>
                </div>
                <div class="rounded-md bg-gray-50 p-2">
                    <dt class="text-xs font-bold uppercase text-gray-500">{{ t('weather.wind') }}</dt>
                    <dd class="font-semibold text-gray-900">{{ weather.wind?.speed ?? 0 }} m/s</dd>
                </div>
            </dl>
        </div>
    </div>
</template>
