<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel,  Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { PencilIcon, TrashIcon, EllipsisVerticalIcon, ArrowDownTrayIcon, PaperClipIcon } from '@heroicons/vue/20/solid'
import PostUserHeader from './PostUserHeader.vue'
import {router, useForm, usePage} from '@inertiajs/vue3';
import {isImage} from '@/helpers.js';
import { HandThumbUpIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';
import axiosClient from '@/axiosClient.js';
import { ref } from 'vue';
import { useI18n } from '@/i18n.js';


const props = defineProps({
    post: Object
});

const emit = defineEmits(['editClick', 'attachmentClick']);
const authUser = usePage().props.auth.user;
const showComments = ref(false);
const { t } = useI18n();
const commentForm = useForm({
    comment: '',
});

function openEditModal() {
    emit('editClick',props.post);
}

function deletePost() {
    if (window.confirm(t('post.confirm_delete_post'))) {
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


function openAttachment(ind) {
    emit('attachmentClick',props.post , ind);
}

function sendReaction() {
    axiosClient.post(route('post.reaction', props.post), {
        reaction : 'like'
    })
    .then(({data})=>{
        props.post.num_of_reactions = data.num_of_reactions;
        props.post.current_user_has_reaction = data.current_user_has_reaction;
    })
}

function submitComment() {
    commentForm.post(route('comments.store', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
            showComments.value = true;
        },
    });
}

function deleteComment(comment) {
    if (!window.confirm(t('post.confirm_delete_comment'))) {
        return;
    }

    router.delete(route('comments.destroy', comment.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="bg-white border rounded-lg shadow-md mb-3">
        <div class="p-4">

            <div class="flex items-center justify-between mb-3">
                <PostUserHeader :post="post" />
                <!-- template du headlessui -->

                   
                        <Menu v-if="post.can_update || post.can_delete" as="div" class="relative inline-block text-left z-10">
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
                                    v-if="post.can_update"
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
                                    {{ t('post.edit') }}
                                </button>
                                </MenuItem>
                            </div>

                            <div class="px-1 py-1">
                                <MenuItem v-slot="{ active }">
                                <button
                                v-if="post.can_delete"
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
                                    {{ t('post.delete') }}
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
                            {{ open ? t('post.show_less') : t('post.read_more') }}
                        </DisclosureButton>
                    </div>
                </template>
                    </Disclosure>

                <div class="grid gap-3 mb-3" :class="post.attachments.length===1 ? 'grid-cols-1':'grid-cols-2'">
                    <template
                        v-for="(attachment, ind) of post.attachments.slice(0, 4)"
                        :key="attachment.id">
                        <div @click="openAttachment( ind)"
                         class="group gap-2 w-full bg-gray-100 aspect-square rounded items-center justify-center text-gray-500 relative  cursor-pointer">

                            <div v-if="ind===3 && post.attachments.length>4" class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/30 text-white
                            flex items-center justify-center text-xl ">
                                +{{ post.attachments.length- 4 }} more
                            </div>

                            <!-- download -->
                            <a @click.stop :href="route('posts.download', attachment.id)"
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
                                <div class="flex flex-col justify-center items-center w-full h-full">
                            <PaperClipIcon class="w-10 h-10 mb-3 " />

                                <span>{{ attachment.name }}</span>

                                </div>

                            </template>

                        </div>
                    </template>
                </div>

                <div class="flex gap-2">
                    <button @click="sendReaction"
                     class="text-gray-800 flex gap-1 item-center justify-center rounded-lg
                      py-2 px-4 flex-1"
                     :class="[
                        post.current_user_has_reaction ? 
                        'bg-sky-100 hover:bg-sky-200' : 'bg-gray-100 hover:bg-gray-200',
                     ]">
                        <HandThumbUpIcon class="w-5 h-5 mr-2"  />
                        <span class="mr-2">{{ post.num_of_reactions }}</span>
                         {{ post.current_user_has_reaction ? t('post.unlike') : t('post.like') }}
                       </button>

                    <button @click="showComments = !showComments" class="text-gray-800 flex gap-1 item-center justify-center bg-gray-100 rounded-lg
                     hover:bg-gray-200 py-2 px-4 flex-1">
                        <ChatBubbleLeftRightIcon class="w-5 h-5 mr-2" />
                        <span class="mr-1">{{ post.num_of_comments || 0 }}</span>
                        {{ t('post.comment') }}
                    </button>
                </div>

                <div v-if="showComments" class="mt-4 border-t pt-3">
                    <form @submit.prevent="submitComment" class="flex gap-2">
                        <img
                            :src="authUser.avatar_url || '/storage/default_avatar.png'"
                            alt="Avatar"
                            class="w-8 h-8 rounded-full object-cover"
                        />
                        <div class="flex-1">
                            <textarea
                                v-model="commentForm.comment"
                                rows="2"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-lime-700 focus:ring-lime-700"
                                :placeholder="t('post.write_comment')"
                            />
                            <div class="mt-1 flex items-center justify-between">
                                <small v-if="commentForm.errors.comment" class="text-red-500">
                                    {{ commentForm.errors.comment }}
                                </small>
                                <button
                                    type="submit"
                                    class="ml-auto rounded-md bg-lime-900 px-3 py-1.5 text-sm font-semibold text-white hover:bg-lime-800"
                                    :disabled="commentForm.processing"
                                >
                                    {{ t('post.send') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3 space-y-3">
                        <div
                            v-for="comment in post.comments || []"
                            :key="comment.id"
                            class="flex gap-2 rounded-md bg-gray-50 p-2"
                        >
                            <img
                                :src="comment.user.avatar_url || '/storage/default_avatar.png'"
                                alt="Avatar"
                                class="w-8 h-8 rounded-full object-cover"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="text-sm font-bold">{{ comment.user.name }}</div>
                                        <div class="break-words text-sm text-gray-700">{{ comment.comment }}</div>
                                    </div>
                                    <button
                                        v-if="comment.can_delete"
                                        @click="deleteComment(comment)"
                                        class="text-xs font-semibold text-red-600 hover:text-red-800"
                                    >
                                        {{ t('post.delete_comment') }}
                                    </button>
                                </div>
                                <div class="text-[11px] text-gray-400">{{ comment.created_at }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
