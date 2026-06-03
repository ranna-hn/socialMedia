<script setup>
import { computed , ref} from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TabItem from './Partials/TabItem.vue'
import Edit from './Edit.vue'
import { usePage, useForm} from '@inertiajs/vue3'
import {XMarkIcon, CheckCircleIcon, CameraIcon} from '@heroicons/vue/24/outline'

const imagesForm = useForm({
  avatar: null,
  cover: null,
});



const coverImageSrc = ref('')
const avatarImageSrc = ref('')

function resetCoverImage() {
    imagesForm.cover= null;
    coverImageSrc.value = null;
}

function submitCoverImage() {
    imagesForm.post(route('profile.updateImages'),{
        onSuccess: (user) => {
        console.log(user)
        resetCoverImage()
        setTimeout(() => {
            showNotification.value = false;
        }, 3000);
    },
    
    })
}

const authUser= usePage().props.auth.user;

const isMyProfile = computed(() => authUser && authUser.id === props.user.id);

const props = defineProps({
    errors: Object,
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    success: {
        type: String,
    },
    user:{
      type:Object,
    }

});

function onCoverChange(event) {
    imagesForm.cover = event.target.files[0];
    if(imagesForm.cover) {
        const reader= new FileReader()
        reader.onload= () => {
            coverImageSrc.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.cover)
    }
    
}

const showNotification = ref(true);

// avatar:

function onAvatarChange(event) {
    imagesForm.avatar = event.target.files[0];
    if(imagesForm.avatar) {
        const reader= new FileReader()
        reader.onload= () => {
            avatarImageSrc.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.avatar)
    }
    
}

function resetAvatarImage() {
    imagesForm.avatar= null;
    avatarImageSrc.value = null;
}

function submitAvatarImage() {
    showNotification.value = true
    imagesForm.post(route('profile.updateImages'),{
        onSuccess: (user) => {
        console.log(user)
        resetAvatarImage()
        setTimeout(() => {
            showNotification.value = false;
        }, 3000);
    },
    
    })
}




</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-[768px] mx-auto h-full overflow-auto">
            <div
                v-show="showNotification && success"
                class="my-2 py-2 px-3 text-sm font-medium bg-emerald-500 text-white rounded-lg">
                {{ success }}
            </div>

            <div
                    v-show="errors.cover"
                    class="my-2 py-2 px-3 text-sm font-medium bg-red-500 text-white rounded-lg">
                    {{ errors.cover }}
                </div>
            

            <div class="group relative bg-white">
                

                <img :src="coverImageSrc || user.cover_url || '/storage/cover7.jpg'" alt="Cover" class=" h-[200px] object-cover w-full rounded-t-lg" />

                <div class="absolute top-2 right-2">

                    <button v-if="!coverImageSrc" class= "bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 rounded-lg shadow-md cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 p-1 ">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    Update Cover Image
    
                        <input type="file" class="absolute left-0 top-0 bottom-0 right-0 opacity-0 cursor-pointer"  @change="onCoverChange" />
                    </button>

                    <div v-else class="flex gap-2 bg-white p-2 rounded-lg shadow-md opacity-0 group-hover:opacity-100">

                        <button
                        @click="resetCoverImage"
                        class= "bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex  items-center opacity-0 group-hover:opacity-100 rounded-lg shadow-md">
                    <XMarkIcon class="w-6 h-6  " />
                    Cancel
                    </button>

                    <button 
                    @click="submitCoverImage"
                    class= "bg-black  text-white py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 rounded-lg shadow-md">
                    <CheckCircleIcon class="w-6 h-6  mr-2" />
                    Submit
                    </button>
                </div>
                </div>

                <!-- avatar -->

                <div class="flex">
                  <div class=" flex items-center justify-center relative group/avatar -mt-[64px] ml-[48px] w-[120px] h-[120px] rounded-full " >
                    <img
                        :src="avatarImageSrc || user.avatar_url || '/storage/default_avatar.png'"
                        alt="Profile"
                        class="rounded-full w-full h-full object-cover"
                    />

                    <button v-if="!avatarImageSrc" 
                    class= "absolute left-0 top-0 right-0 bottom-0 bg-black/50 text-white opacity-0 flex items-center justify-center group-hover/avatar:opacity-100 transition rounded-full cursor-pointer">
                    <CameraIcon class="w-8 h-8 " />
    
                        <input type="file" class="absolute left-0 top-0 bottom-0 right-0 opacity-0 cursor-pointer"  @change="onAvatarChange" />
                    </button>

                    <div v-else class="absolute top-1 right-0 flex flex-col gap-2 rounded-lg shadow-md">

                        <button
                        @click="resetAvatarImage"
                        class= "w-7 h-7 flex items-center justify-center bg-red-500/80 rounded-full shadow-md text-white cursor-pointer">
                        <XMarkIcon class="w-5 h-5  " />
                        </button>

                        <button 
                        @click="submitAvatarImage"
                        class= "w-7 h-7 flex items-center justify-center bg-emerald-500/80 rounded-full shadow-md text-white cursor-pointer">
                        <CheckCircleIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div> 
                  

                <div class="flex justify-between items-center flex-1 p-4 ">
                  <h2 class="font-bold text-lg">{{ user.name }}</h2>
                </div>
            </div>    
                </div>

            <div class="border-t">
            <TabGroup>
            <TabList class="flex bg-white ">
                
                <Tab v-slot="{ selected }" as="template">
                <TabItem text="Posts" :selected="selected"  />
                </Tab>

                <Tab v-slot="{ selected }" as="template">
                <TabItem text="Followers" :selected="selected"  />
                </Tab>

                <Tab v-slot="{ selected }" as="template">
                <TabItem text="Followings" :selected="selected" />
                </Tab>

                <Tab v-slot="{ selected }" as="template">
                <TabItem text="Photos" :selected="selected" />
                </Tab>

                <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                <TabItem text="My Profile" :selected="selected"  />
                </Tab>
                
            </TabList>

            <TabPanels class="mt-2">

                <TabPanel class="bg-white p-3">
                    Posts
                </TabPanel>

                <TabPanel class="bg-white p-3">
                    Followers
                </TabPanel>

                <TabPanel class="bg-white p-3">
                    Followings
                </TabPanel>

                <TabPanel class="bg-white p-3">
                    Photos
                </TabPanel>

                <TabPanel v-if="isMyProfile">
                    <Edit :must-verify-email="mustVerifyEmail" :status="status" />
                </TabPanel>
            </TabPanels>

            </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>

    
</template>





