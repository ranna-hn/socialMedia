<script setup>
import { ref } from 'vue';
import { usePage} from '@inertiajs/vue3';
import PostModal from './PostModal.vue';

const authUser = usePage().props.auth.user;

const showModal = ref(false);

const newPost =ref({
    id: null,
    body: '',
    user: authUser,
})



function showCreatePostModal(){
    showModal.value = true;
}

</script>

<template>
    <div class="p-4 rounded-lg border bg-white shadow mb-3">

        <div @click="showCreatePostModal" 
        class="py-2 px-3 border-2 rounded-md border-gray-300 text-gray-600 shadow-sm mb3 w-full" 
        :rows="1">
            What's in Your mind?
        </div>

            <!-- <div class="flex gap-2 justify-center">

                <button type="button" class=" relative rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white shadow-xs
                hover:bg-lime-700 focus-visible:outline-2 focus-visible:outline-offset-2
                focus-visible:outline-lime-300">
                    Attach files
                    <input type="file" class="absolute left-0 top-0 right-0 bottom-0 opacity-0 cursor-pointer" />
            
                </button>

                <button @click="submit" type="submit" class="rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white shadow-xsover:bg-lime-800 hover:bg-lime-700 focus-visible:outline-2 focus-visible:outline-offset-2
                focus-visible:outline-lime-300">
                submit
                </button>
            </div> -->


        <PostModal :post="newPost" v-model="showModal"/>
    </div>
</template>