<script setup>
import { computed , ref} from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TabItem from './Partials/TabItem.vue'
import Edit from './Edit.vue'
import { usePage, useForm} from '@inertiajs/vue3'
import PrimaryButton from '../../Components/PrimaryButton.vue'
import {XMarkIcon, CheckCircleIcon} from '@heroicons/vue/24/outline'

const imagesForm = useForm({
  avatar: null,
  cover: null,
});



const coverImageSrc = ref('')

function cancelCoverImage() {
    imagesForm.cover= null;
    coverImageSrc.value = null;
}

function submitCoverImage() {
    console.log(imagesForm.cover)
    imagesForm.post(route('profile.updateCover'),{
        onSuccess: (user) => {
        console.log(user)
        showNotification.value = true;
        cancelCoverImage()
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
    user:{
      type:Object,
    }

});

function onCoverChange(event) {
    console.log(event);
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




</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-[768px] mx-auto h-full overflow-auto">
            <div
                    v-show="showNotification && status === 'cover-image-update'"
                    class="my-2 py-2 px-3 text-sm font-medium bg-emerald-500 text-white rounded-lg">
                    Your cover image has been updated successfully.
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
                        @click="cancelCoverImage"
                        class= "bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex  items-center opacity-0 group-hover:opacity-100 rounded-lg shadow-md">
                    <XMarkIcon class="w-6 h-6  " />
                    Cancel
                    </button>

                    <button 
                    @click="submitCoverImage"
                    class= "bg-black  text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100  text-white rounded-lg shadow-md">
                    <CheckCircleIcon class="w-6 h-6  mr-2" />
                    Submit
                    </button>
                </div>
                </div>
                


                <div class="flex">
                  <img src="/storage/default_avatar.png" alt="Profile" class="ml-[48px] w-[120px] h-[120px] -mt-[64px] rounded-full " />

                <div class="flex justify-between items-center flex-1 p-4 ">
                  <h2 class="font-bold text-lg">{{ user.name }}</h2>
                  <PrimaryButton v-if="isMyProfile" >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 cursor-pointer">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>

                    Edit Profile
                  </PrimaryButton>
                </div>
            </div>    
                </div>

            <div class="border-t">
            <TabGroup>
            <TabList class="flex bg-white ">

                <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                <TabItem text="Abouts" :selected="selected"  />
                </Tab>
                
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
                
            </TabList>

            <TabPanels class="mt-2">
                <TabPanel v-if="isMyProfile">
                    <Edit :must-verify-email="mustVerifyEmail" :status="status" />
                </TabPanel>

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
            </TabPanels>

            </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>

    
</template>





