<script setup>

defineProps({
    image: String,
    title: String,
    description: String
});

</script>

<template>
    <div class=" group flex items-start gap-3 p-3 rounded-lg hover:bg-lime-100 transition duration-150 ease-in-out cursor-pointer">
        <div class="flex items-center gap-2 rounded-lg">
            <img :src="image" alt="title" class="w-14 h-14 object-cover rounded-full" />
            <div>
                <h3 class="font-black text-sm">{{ title }}</h3>
                <div class="text-xs text-gray-500 group-hover:text-black">{{ description }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>