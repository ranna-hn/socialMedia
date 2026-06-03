<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel,  Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { PencilIcon, TrashIcon, EllipsisVerticalIcon, ArrowDownTrayIcon } from '@heroicons/vue/20/solid'
import PostUserHeader from './PostUserHeader.vue'
import {router} from '@inertiajs/vue3';
import {isImage} from '@/helpers.js';
import { HandThumbUpIcon, ChatBubbleLeftRightIcon, DocumentIcon } from '@heroicons/vue/24/outline';


const props = defineProps({
    post: Object
});

const emit = defineEmits(['editClick']);

function openEditModal() {
    emit('editClick',props.post);
}

function deletePost() {
    if (window.confirm('Are you sure you want to delete this post?')) {
        router.delete(route('posts.destroy', props.post), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Post deleted successfully');
            },
            onError: (errors) => {
                console.log('Error deleting post:', errors);
            },
        });
        }
}
</script>

<template>
    <div class="bg-white border rounded-lg shadow-md mb-3">
        <div class="p-4">

            <div class="flex items-center justify-between mb-3">
                <PostUserHeader :post="post" />
                <!-- template du headlessui -->

                   
                        <Menu as="div" class="relative inline-block text-left z-10">
                        <div>
                            <MenuButton
                            class="w-8 h-8 rouded-full hover:bg-black/10 transition flex items-center justify-center rounded-full"
                            >
                            <EllipsisVerticalIcon
                                class="w-4 h-4"
                                aria-hidden="true"
                            />
                            </MenuButton>
                        </div>

                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <MenuItems
                            class="absolute right-0 mt-2 w-33 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                            >
                            <div class="px-1 py-1">
                                <MenuItem v-slot="{ active }">
                                <button
                                    @click="openEditModal"
                                    :class="[
                                    active ? 'bg-lime-200 text-black' : 'text-gray-900',
                                    'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                    ]"
                                >
                                    <PencilIcon
                                    class="mr-2 h-5 w-5 "
                                    aria-hidden="true"
                                    />
                                    Edit
                                </button>
                                </MenuItem>
                            </div>

                            <div class="px-1 py-1">
                                <MenuItem v-slot="{ active }">
                                <button
                                @click="deletePost"
                                    :class="[
                                    active ? 'bg-lime-200 text-black' : 'text-gray-900',
                                    'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                    ]"
                                >
                                    <TrashIcon
                                    :active="active"
                                    class="mr-2 h-5 w-5 text-black"
                                    aria-hidden="true"
                                    />
                                    Delete
                                </button>
                                </MenuItem>
                            </div>
                            </MenuItems>
                        </transition>
                        </Menu>


            </div>

            <div class="mb-3">
                <Disclosure v-slot="{ open }">
                <div class="ck-content-output" v-if="!open"  v-html="post.body.substring(0, 200)" />

                <template v-if="post.body.length > 200 ">
                    <DisclosurePanel>
                    <div class="ck-content-output" v-html="post.body" />
                </DisclosurePanel>
                    <div class="flex justify-end">
                        <DisclosureButton class="mt-2 text-sm text-blue-500 hover:underline">
                            {{ open ? 'Show less' : 'Read more' }}
                        </DisclosureButton>
                    </div>
                </template>
                    </Disclosure>

                <div class="grid gap-3 mb-3" :class="post.attachments.length===1 ? 'grid-cols-1':'grid-cols-2'">
                    <template
                        v-for="(attachment, ind) of post.attachments.slice(0, 4)"
                        :key="attachment.id">
                        <div class="group gap-2 w-full bg-blue-100 aspect-square rounded items-center justify-center text-gray-500 relative ">

                            <div v-if="ind===3 && post.attachments.length>4" class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/30 text-white
                            flex items-center justify-center text-xl ">
                                +{{ post.attachments.length- 4 }} more
                            </div>

                            <!-- download -->
                            <a :href="route('posts.download', attachment.id)"
                            class="z-20 opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center text-gray-100 bg-gray-600 
                            rouded absolute right-1 top-2 cursor-pointer hover:bg-gray-800 duration-150 ease-in-out">
                                <ArrowDownTrayIcon class="w-4 h-4" />
                            </a>   

                            <img
                            v-if="isImage(attachment)"
                            :src="attachment.url"
                            :alt="attachment.name"
                            class="object-contain aspect-square w-full h-full rounded"/>

                            <template v-else>
                                <div class="flex flex-col items-center justify-center w-full h-full p-4 text-center">
                           <DocumentIcon class="w-8 h-8 mb-3 justify-center items-center " />

                            <small>{{ attachment.name }}</small>
                            </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="flex gap-2">
                    <button  class="text-gray-800 flex gap-1 item-center justify-center bg-gray-100 rounded-lg
                     hover:bg-gray-200 py-2 px-4 flex-1">
                        <HandThumbUpIcon class="w-5 h-5 mr-2"  />
                         Like
                       
                    </button>

                    <button class="text-gray-800 flex gap-1 item-center justify-center bg-gray-100 rounded-lg
                     hover:bg-gray-200 py-2 px-4 flex-1">
                        <ChatBubbleLeftRightIcon class="w-5 h-5 mr-2" />
                        Comment
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>