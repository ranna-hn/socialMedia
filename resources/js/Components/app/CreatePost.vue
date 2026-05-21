<script setup>
import { ref } from 'vue';

const postCreating = ref(false);
</script>

<template>
    <div class="p-4 rounded-lg border bg-white shadow mb-3">

        <div @click="postCreating =true" class="py-3 px-2 bg-white text-gray-500 border-black border-2 rounded-lg mb-4 cursor-pointer hover:bg-gray-100 transition duration-150 ease-in-out">
            Click to create a post
        </div>

            <div v-if="postCreating" class="flex gap-2 justify-center">

                <button type="submit" class=" relative rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white shadow-xs
                hover:bg-lime-700 focus-visible:outline-2 focus-visible:outline-offset-2
                focus-visible:outline-lime-300">
                    Attach files
                    <input type="file" class="absolute left-0 top-0 right-0 bottom-0 opacity-0 cursor-pointer" />
            
                </button>

                <button type="submit" class="rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white shadow-xs
                hover:bg-lime-700 focus-visible:outline-2 focus-visible:outline-offset-2
                focus-visible:outline-lime-300">
                submit
                </button>
            </div>

       

    </div>
</template>