<script setup>

import { PaperClipIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { isImage } from '@/helpers'
</script>

<template
    v-for="(myFile, ind) of attachmentFiles.slice(0, 6)">

    <div 
    class="group bg-blue-100 aspect-square rounded flex flex-col 
    items-center justify-center  text-gray-500 relative ">

    <div v-if="ind===3" class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/30 text-white
    flex items-center justify-center text-xl ">
        +{{ attachmentFiles.length- 3 }} more
    </div>

    <button
    @click="removeFile(myFile)"
    class= "absolute z-20 top-1 right-1 w-7 h-7 flex items-center justify-center
    bg-yellow-950/30 rounded-full shadow-md text-white cursor-pointer
    hover:bg-black/40"
    >
    <XMarkIcon class="w-5 h-5  " />
    </button>

        <img
        v-if="isImage(myFile.file)"
        :src="myFile.url"
        :alt="myFile.name"
        class="object-cover aspect-square"/>

        <template v-else>
    <PaperClipIcon class="w-8 h-8 mb-3" />

        <small class="text-center">
            {{ myFile.file.name }}
        </small>
        </template>
    </div>



    <!-- <div v-if="ind===3" class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/30 text-white
                            flex items-center justify-center text-xl ">
                                +{{ attachmentFiles.length- 3 }} more
                            </div> -->


</template>