<script setup>
defineProps({
    selected: Boolean,
        text: String

})

</script>

<template>
    <button
                    :class="[
                    'px-3 py-2.5 outline-none focus:outline-none focus:ring-none text-sm',
                    '',
                    selected
                        ? ' bg-green-50 text-lime-700 border-b-2  shadow border-b-yellow-950' 
                        : 'text-yellow-950 hover:bg-gray-100 transition duration-150 ease-in-out', ,
                    ]"
                >
                    {{ text }}
                </button>


</template>

<style scoped>

</style>