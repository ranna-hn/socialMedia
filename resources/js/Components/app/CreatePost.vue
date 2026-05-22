<script setup>
import { ref } from 'vue';
import InputTextarea from '../InputTextarea.vue';
import { useForm } from '@inertiajs/vue3';

const postCreating = ref(false);

const newPostForm =useForm({
    body: '',
})

function submit(){
    newPostForm.post(route('posts.create'),{
        onSuccess: () => {
            newPostForm.reset()
        }
    })
    
}

</script>

<template>
    <div class="p-4 rounded-lg border bg-white shadow mb-3">

        <InputTextarea @click="postCreating =true" 
        class="mb-3 w-full" 
        :rows="1"
        placeholder="What's on your mind?"
        v-model="newPostForm.body"
        />
            <div v-if="postCreating" class="flex gap-2 justify-center">

                <button type="button" class=" relative rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white shadow-xs
                hover:bg-lime-700 focus-visible:outline-2 focus-visible:outline-offset-2
                focus-visible:outline-lime-300">
                    Attach files
                    <input type="file" class="absolute left-0 top-0 right-0 bottom-0 opacity-0 cursor-pointer" />
            
                </button>

                <button @click="submit" type="submit" class="rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white shadow-xso              hover:bg-lime-700 focus-visible:outline-2 focus-visible:outline-offset-2
                focus-visible:outline-lime-300">
                submit
                </button>
            </div>

       

    </div>
</template>