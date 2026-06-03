
<script setup>
import { computed, onMounted, watch, ref } from 'vue'
import {TransitionRoot, TransitionChild, Dialog,DialogPanel,DialogTitle,} from '@headlessui/vue'
import PostUserHeader from './PostUserHeader.vue'
import {XMarkIcon, PaperClipIcon, BookmarkIcon, ArrowUturnLeftIcon} from '@heroicons/vue/24/solid'
import { useForm, usePage } from '@inertiajs/vue3'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { isImage } from '@/helpers';


const editor = ClassicEditor;

const editorConfig = {
    toolbar: [
            'heading',
            '|',
            'bold',
            'italic',
            'link',
            'bulletedList',
            'numberedList',
            '|',
            'undo',
            'redo',
            '|',
            'outdent',
            'indent',
            '|',
            'blockQuote'
        ]
    
};


const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    modelValue: Boolean
})

const attachmentExtensions  = usePage().props.attachmemtExtensions;


/*
*{
* file: File,
* url: '',
* }
* @type {Ref<UnwrapRef<*[]>>}

*/

const attachmentFiles = ref([]);

const attachmentErrors = ref([]);

const showExtensionsText = ref(false);

const showSizeText = ref(false);
const maxAttachmentSize = 50 * 1024 * 1024;


const emit = defineEmits(['update:modelValue','hide'])

const form = useForm({
    body: '',
    attachments: [],
    deleted_file_ids: [],
    _method:'POST',
})
 
const show =computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})


const computedAttachments = computed(() => {
    return [
        ...attachmentFiles.value, ...(props.post.attachments || []) ]
})

watch(() => props.post, () => {
    console.log("this is triggered", props.post)
    form.body = props.post.body || ''
    
}   )


function closeModal() {
  show.value = false
  emit('hide')
  resetModel()
}

function resetModel(){
    form.reset()
    attachmentFiles.value = []
    showExtensionsText.value = false;
    showSizeText.value = false;

    attachmentErrors.value = [];
    if(props.post.attachments){
        props.post.attachments.forEach(file=>file.deleted=false)
    }
}


onMounted(() => {
    console.log(props.post)
})


function submit()
{
    if(attachmentFiles.value.some(myFile => myFile.error)){
        return;
    }

    form.attachments = attachmentFiles.value.map(myFile => myFile.file)
    console.log(form)
    if (props.post.id){
        form._method='PUT'
    form.post(route('posts.update', props.post.id), {
        preserveScroll: true,
        onSuccess: (res) => {
            closeModal();
        },
        onError: (errors) => {
            processErrors(errors)
        }

    })
    }
    else{

        form.post(route('posts.create'),{
            preserveScroll: true,
            onSuccess: (res) => {
                closeModal();
            },
            onError: (errors) => {
            processErrors(errors)
        }
        })
    }
}

function processErrors(errors){

    for (const key in errors){
        if(key.includes('.')){
            const [index] = key.split('.')[1];
            attachmentErrors.value[index] = errors[key] 
        
        }
}
}


async function onAttachmentChoose($event) {
    showExtensionsText.value = false;
    showSizeText.value = false;
        attachmentErrors.value = [];
    form.clearErrors();

    for(const file of $event.target.files){
        let part = file.name.split('.');
        let ext = part.pop().toLowerCase();

        const invalidExtension = !attachmentExtensions.includes(ext);
        const invalidSize = file.size > maxAttachmentSize; 

        if(invalidExtension){
           showExtensionsText.value = true 
        }

        if(invalidSize){
            showSizeText.value = true;
        }
        let error = null;

        if(invalidExtension){
            error = 'Invalid file type';
            
        } else if(invalidSize){
            error = 'File size must not exceed 50 MB.';
        }

        const myFile = {
            file,
            url: await readFile(file),
            error

        }
        
        myFile.src = await readFile(file)
        attachmentFiles.value.push(myFile)
    }

    $event.target.value = null;
}


async function readFile(file) {
    return new Promise((res, rej) => {
        if(isImage(file)){
        const reader = new FileReader();

        reader.onload = () => {
            res(reader.result)
        }
        reader.onerror = rej

        reader.readAsDataURL(file);
    }
    else{
        res(null)
    }
    })
    
} 



function removeFile(myFile){
    if(myFile.file){
    attachmentFiles.value = attachmentFiles.value.filter(f => f !== myFile)
    }
    else{
        form.deleted_file_ids.push(myFile.id)
        myFile.deleted = true 
    }
}

function undoDelete(myFile){
    myFile.deleted = false;
    form.deleted_file_ids = form.deleted_file_ids.filter(id => myFile.id != id)
}



</script>

<template>

    <teleport to="body">
        <TransitionRoot appear :show="show" as="template">

        <Dialog as="div" @close="closeModal" class="relative z-50">
        <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-to="opacity-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100"
            leave-to="opacity-0"
        >
            <div class="fixed inset-0 bg-black/25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
            <div
            class="flex min-h-full items-center justify-center p-4 text-center"
            >
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0 scale-95"
                enter-to="opacity-100 scale-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100 scale-100"
                leave-to="opacity-0 scale-95"
            >
                <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded bg-white text-left align-middle shadow-xl transition-all"
                >
                <DialogTitle
                    as="h3"
                    class="flex items-center justify-between py-3 px-4 text-lg font-medium text-gray-900 bg-gray-100"
                >
                    {{ post.id ? 'Update Post' : 'Create Post' }}
                    <button @click="closeModal" class="w-8 h-8 rounded-full hover:bg-black/10 transition flex items-center justify-center"> 
                        <XMarkIcon class="w-4 h-4" @click="closeModal" />
                    </button>
                </DialogTitle>
                <div class="p-4">
                    <PostUserHeader :post="post" :show-time="false" class="mb-4"/>
                    <ckeditor :editor="editor" v-model="form.body" :config="editorConfig" ></ckeditor>

                    <div v-if="showExtensionsText" class="border-l-4 border-lime-800 py-2 px-3 bg-lime-100 mt-3 txt-gray-800">
                        Files must be one of the following extensions:
                        <small>
                            {{ attachmentExtensions.join(',  ') }}
                        </small>

                    </div>

                    <div v-if="showSizeText" class="border-l-4 border-red-600 py-2 px-3 bg-red-100 mt-3 text-gray-800">
                        File size must not exceed 50 MB.
                    </div>
                
                    <div class="grid gap-3 my-3" :class="[computedAttachments.length ===1 ? 'grid-cols-1' : 'grid-cols-2']">
                    <div
                        v-for="(myFile, ind) of computedAttachments">
                        <div 
                            class="group bg-gray-100 aspect-square rounded flex flex-col 
                            items-center justify-center text-gray-500 relative border"
                            :class="myFile.error || form.errors['attachments.' + ind] ? 'border-red-500 border-2' : 'border-transparent'">


                        <div v-if="myFile.deleted" class="absolute z-10 left-0 bottom-0 right-0 py-2 px-3 text-sm bg-black text-white flex justify-between items-center">
                            to be deleted
                            <ArrowUturnLeftIcon @click="undoDelete(myFile)" class="w-4 h-4 cursor-pointer"/>
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
                            v-if="isImage(( myFile.file || myFile))"
                            :src="myFile.url"
                            :alt="myFile.name"
                            class="object-contain aspect-square"
                            :class="myFile.deleted ? 'opacity-50': ''"/>

                            <div v-else class="flex flex-col justify-center items-center px-3 text-gray-500"
                            :class="myFile.deleted ? 'opacity-50': ''" >
                           <PaperClipIcon class="w-8 h-8 mb-3 " />

                            <small class="text-center">
                                {{ ( myFile.file || myFile).name }}
                            </small>
                        </div>
                        </div>


                        <small
                            v-if="myFile.error || form.errors['attachments.' + ind]"
                            class="text-red-500 block mt-1"
                        >
                            {{ myFile.error || form.errors['attachments.' + ind] }}
                        </small>
                    


                    </div>
                </div>

                </div>

                <div class="flex gap-1 py-3 px-4 ">
                    <button
                    type="button"
                    class=" flex items-center justify-center w-full rounded-md bg-lime-900 px-3 py-2 
                    text-sm font-semibold text-white shadow-xs hover:bg-lime-800 focus-visible:outline-2 focus-visible:outline-offset-2
                    focus-visible:outline-lime-300"
                    @click="submit"
                    >
                    <BookmarkIcon class="w-4 h-4 mr-2" />
                    submit
                    </button>
                    <button
                    type="button"
                    class="flex items-center justify-center w-full rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold
                     text-white shadow-xs hover:bg-lime-800 focus-visible:outline-2 focus-visible:outline-offset-2
            focus-visible:outline-lime-300 relative"
                    >
                    <PaperClipIcon class="w-4 h-4 mr-2 cursor-pointer" />
                    Attach Files
                    <input @click.stop @change="onAttachmentChoose" type="file" multiple class="absolute left-0 top-0 right-0 bottom-0 opacity-0 cursor-pointer" />
                    </button>
                </div>
                </DialogPanel>
            </TransitionChild>
            </div>
        </div>
        </Dialog>
        </TransitionRoot>
    </teleport>
</template>
