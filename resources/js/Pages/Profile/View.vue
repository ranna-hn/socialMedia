<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TabItem from './Partials/TabItem.vue'
import Edit from './Edit.vue'
import { router, usePage, useForm} from '@inertiajs/vue3'
import {
    XMarkIcon,
    CheckCircleIcon,
    CameraIcon,
    UserMinusIcon,
    UserPlusIcon,
    PhotoIcon,
    FolderPlusIcon,
    PlusIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline'
import PostList from '@/Components/app/PostList.vue'
import FollowingItem from '@/Components/app/FollowingItem.vue'
import { useI18n } from '@/i18n.js'

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
    },
    posts: Object,
    photos: Object,
    albums: Object,
    followers: Object,
    followings: Object,
    stats: Object,
    current_user_is_following: Boolean,
});

const resourceItems = (resource) => {
    if (Array.isArray(resource)) {
        return resource;
    }

    if (Array.isArray(resource?.data)) {
        return resource.data;
    }

    return [];
};

const imagesForm = useForm({
  avatar: null,
  cover: null,
});

const albumForm = useForm({
    name: '',
    description: '',
});

const addPhotosForm = useForm({
    photo_ids: [],
});



const coverImageSrc = ref('')
const avatarImageSrc = ref('')
const showAlbumForm = ref(false)
const selectedPhotoIds = ref([])
const selectedAlbumId = ref('')
const activeAlbumId = ref(null)
const selectedProfileTab = ref(0)
const draggedPhotoId = ref(null)
const albumPhotoOrders = ref({})
let profileRefreshTimer = null

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
const { t } = useI18n();

const isMyProfile = computed(() => authUser && authUser.id === props.user.id);
const profileStats = computed(() => props.stats || {});
const profilePhotos = computed(() => resourceItems(props.photos));
const profileAlbums = computed(() => resourceItems(props.albums));
const profileFollowers = computed(() => resourceItems(props.followers));
const profileFollowings = computed(() => resourceItems(props.followings));
const selectedPhotoCount = computed(() => selectedPhotoIds.value.length);
const activeAlbum = computed(() =>
    profileAlbums.value.find((album) => album.id === activeAlbumId.value) || null
);

function albumPhotosFromResource(album) {
    return resourceItems(album?.photos);
}

function albumPhotos(album) {
    if (!album) {
        return [];
    }

    const resourcePhotos = albumPhotosFromResource(album);
    const orderedPhotos = albumPhotoOrders.value[album.id];

    if (!orderedPhotos) {
        return resourcePhotos;
    }

    const resourcePhotoIds = resourcePhotos.map((photo) => photo.id);
    const visibleOrderedPhotos = orderedPhotos.filter((photo) => resourcePhotoIds.includes(photo.id));
    const missingPhotos = resourcePhotos.filter((photo) =>
        !visibleOrderedPhotos.some((orderedPhoto) => orderedPhoto.id === photo.id)
    );

    return [...visibleOrderedPhotos, ...missingPhotos];
}

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

function followUser() {
    router.post(route('users.follow', props.user.username), {}, {
        preserveScroll: true,
        onSuccess: () => window.dispatchEvent(new Event('social-sidebar:refresh')),
    });
}

function unfollowUser() {
    router.delete(route('users.unfollow', props.user.username), {
        preserveScroll: true,
        onSuccess: () => window.dispatchEvent(new Event('social-sidebar:refresh')),
    });
}

function togglePhoto(photo) {
    if (!isMyProfile.value) {
        return;
    }

    const photoId = photo.id;

    if (selectedPhotoIds.value.includes(photoId)) {
        selectedPhotoIds.value = selectedPhotoIds.value.filter((id) => id !== photoId);
        return;
    }

    selectedPhotoIds.value = [...selectedPhotoIds.value, photoId];
}

function createAlbum() {
    albumForm.post(route('profile.albums.store'), {
        preserveScroll: true,
        onSuccess: () => {
            albumForm.reset();
            showAlbumForm.value = false;
        },
    });
}

function addSelectedPhotosToAlbum() {
    const albumId = selectedAlbumId.value || activeAlbumId.value;

    if (!albumId || selectedPhotoIds.value.length === 0) {
        return;
    }

    addPhotosForm.photo_ids = [...selectedPhotoIds.value];
    addPhotosForm.post(route('profile.albums.photos.store', albumId), {
        preserveScroll: true,
        onSuccess: () => {
            selectedPhotoIds.value = [];
            addPhotosForm.reset();
            if (activeAlbumId.value) {
                const nextAlbumPhotoOrders = { ...albumPhotoOrders.value };
                delete nextAlbumPhotoOrders[activeAlbumId.value];
                albumPhotoOrders.value = nextAlbumPhotoOrders;
            }
        },
    });
}

function openAlbum(album) {
    activeAlbumId.value = album.id;
    selectedAlbumId.value = album.id;
    albumPhotoOrders.value = {
        ...albumPhotoOrders.value,
        [album.id]: albumPhotosFromResource(album),
    };
}

function closeAlbum() {
    activeAlbumId.value = null;
}

function startDraggingPhoto(photo) {
    if (!isMyProfile.value) {
        return;
    }

    draggedPhotoId.value = photo.id;
}

function dropPhoto(album, targetPhoto) {
    if (!isMyProfile.value || !draggedPhotoId.value || draggedPhotoId.value === targetPhoto.id) {
        draggedPhotoId.value = null;
        return;
    }

    const photos = [...albumPhotos(album)];
    const fromIndex = photos.findIndex((photo) => photo.id === draggedPhotoId.value);
    const toIndex = photos.findIndex((photo) => photo.id === targetPhoto.id);

    draggedPhotoId.value = null;

    if (fromIndex === -1 || toIndex === -1) {
        return;
    }

    const [movedPhoto] = photos.splice(fromIndex, 1);
    photos.splice(toIndex, 0, movedPhoto);

    albumPhotoOrders.value = {
        ...albumPhotoOrders.value,
        [album.id]: photos,
    };

    router.put(route('profile.albums.photos.reorder', album.id), {
        photo_ids: photos.map((photo) => photo.id),
    }, {
        preserveScroll: true,
    });
}

function stopDraggingPhoto() {
    draggedPhotoId.value = null;
}

function refreshProfileData() {
    router.reload({
        only: ['user', 'followers', 'followings', 'stats', 'current_user_is_following', 'photos', 'albums'],
        preserveScroll: true,
        preserveState: true,
    });
}

onMounted(() => {
    profileRefreshTimer = window.setInterval(refreshProfileData, 12000);
});

onUnmounted(() => {
    if (profileRefreshTimer) {
        window.clearInterval(profileRefreshTimer);
    }
});




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
                    {{ t('profile.update_cover') }}
    
                        <input type="file" class="absolute left-0 top-0 bottom-0 right-0 opacity-0 cursor-pointer"  @change="onCoverChange" />
                    </button>

                    <div v-else class="flex gap-2 bg-white p-2 rounded-lg shadow-md opacity-0 group-hover:opacity-100">

                        <button
                        @click="resetCoverImage"
                        class= "bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex  items-center opacity-0 group-hover:opacity-100 rounded-lg shadow-md">
                    <XMarkIcon class="w-6 h-6  " />
                    {{ t('profile.cancel') }}
                    </button>

                    <button 
                    @click="submitCoverImage"
                    class= "bg-black  text-white py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 rounded-lg shadow-md">
                    <CheckCircleIcon class="w-6 h-6  mr-2" />
                    {{ t('profile.submit') }}
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
                  

                <div class="flex flex-1 items-center justify-between gap-3 p-4 ">
                  <div>
                    <h2 class="font-bold text-lg">{{ user.name }}</h2>
                    <div class="text-sm text-gray-500">@{{ user.username }}</div>
                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-600">
                        <span>{{ profileStats.posts_count || posts?.data?.length || 0 }} {{ t('profile.posts_count') }}</span>
                        <button
                            type="button"
                            @click="selectedProfileTab = 1"
                            class="rounded-sm text-left hover:text-lime-900 hover:underline"
                        >
                            {{ user.followers_count || 0 }} {{ t('profile.followers_count') }}
                        </button>
                        <button
                            type="button"
                            @click="selectedProfileTab = 2"
                            class="rounded-sm text-left hover:text-lime-900 hover:underline"
                        >
                            {{ user.following_count || 0 }} {{ t('profile.following_count') }}
                        </button>
                    </div>
                  </div>
                  <button
                    v-if="!isMyProfile && !current_user_is_following"
                    @click="followUser"
                    class="inline-flex items-center rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800"
                  >
                    <UserPlusIcon class="mr-2 h-4 w-4" />
                    {{ t('profile.follow') }}
                  </button>
                  <button
                    v-else-if="!isMyProfile"
                    @click="unfollowUser"
                    class="inline-flex items-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300"
                  >
                    <UserMinusIcon class="mr-2 h-4 w-4" />
                    {{ t('profile.unfollow') }}
                  </button>
                </div>
            </div>    
                </div>

            <div class="border-t">
            <TabGroup :selected-index="selectedProfileTab" @change="selectedProfileTab = $event">
            <TabList class="flex bg-white ">
                
                <Tab v-slot="{ selected }" as="template">
                <TabItem :text="t('profile.tabs.posts')" :selected="selected"  />
                </Tab>

                <Tab v-slot="{ selected }" as="template">
                <TabItem :text="t('profile.tabs.followers')" :selected="selected"  />
                </Tab>

                <Tab v-slot="{ selected }" as="template">
                <TabItem :text="t('profile.tabs.followings')" :selected="selected" />
                </Tab>

                <Tab v-slot="{ selected }" as="template">
                <TabItem :text="t('profile.tabs.photos')" :selected="selected" />
                </Tab>

                <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                <TabItem :text="t('profile.tabs.my_profile')" :selected="selected"  />
                </Tab>
                
            </TabList>

            <TabPanels class="mt-2">

                <TabPanel class="bg-white p-3">
                    <PostList :posts="posts.data" />
                </TabPanel>

                <TabPanel class="bg-white p-3">
                    <div v-if="profileFollowers.length === 0" class="p-3 text-center text-gray-500">
                        {{ t('profile.no_followers') }}
                    </div>
                    <div v-else class="grid gap-2 sm:grid-cols-2">
                        <FollowingItem
                            v-for="follower in profileFollowers"
                            :key="follower.id"
                            :user="follower"
                            show-follow-button
                        />
                    </div>
                </TabPanel>

                <TabPanel class="bg-white p-3">
                    <div v-if="profileFollowings.length === 0" class="p-3 text-center text-gray-500">
                        {{ t('profile.no_followings') }}
                    </div>
                    <div v-else class="grid gap-2 sm:grid-cols-2">
                        <FollowingItem
                            v-for="following in profileFollowings"
                            :key="following.id"
                            :user="following"
                            show-follow-button
                        />
                    </div>
                </TabPanel>

                <TabPanel class="bg-white p-3">
                    <div class="space-y-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="flex items-center gap-2 text-lg font-black text-gray-900">
                                    <PhotoIcon class="h-5 w-5 text-lime-900" />
                                    {{ t('profile.photos_title') }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ t('profile.photos_from_posts', { count: profilePhotos.length }) }}
                                </p>
                            </div>

                            <button
                                v-if="isMyProfile"
                                @click="showAlbumForm = !showAlbumForm"
                                class="inline-flex items-center justify-center rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800"
                            >
                                <FolderPlusIcon class="mr-2 h-4 w-4" />
                                {{ t('profile.create_album') }}
                            </button>
                        </div>

                        <form
                            v-if="isMyProfile && showAlbumForm"
                            @submit.prevent="createAlbum"
                            class="space-y-3 rounded-lg border border-lime-100 bg-lime-50 p-3"
                        >
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div>
                                    <input
                                        v-model="albumForm.name"
                                        class="w-full rounded-md border-gray-300 text-sm focus:border-lime-700 focus:ring-lime-700"
                                        :placeholder="t('profile.album_title')"
                                    />
                                    <div v-if="albumForm.errors.name" class="mt-1 text-xs text-red-600">
                                        {{ albumForm.errors.name }}
                                    </div>
                                </div>
                                <input
                                    v-model="albumForm.description"
                                    class="w-full rounded-md border-gray-300 text-sm focus:border-lime-700 focus:ring-lime-700"
                                    :placeholder="t('profile.description')"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800 disabled:cursor-not-allowed disabled:bg-gray-300"
                                :disabled="albumForm.processing"
                            >
                                <PlusIcon class="mr-2 h-4 w-4" />
                                {{ t('profile.save_album') }}
                            </button>
                        </form>

                        <div
                            v-if="isMyProfile && profilePhotos.length && profileAlbums.length && !activeAlbum"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-3"
                        >
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                                <div class="flex-1">
                                    <select
                                        v-model="selectedAlbumId"
                                        class="w-full rounded-md border-gray-300 text-sm focus:border-lime-700 focus:ring-lime-700"
                                    >
                                        <option value="">{{ t('profile.choose_album') }}</option>
                                        <option
                                            v-for="album in profileAlbums"
                                            :key="album.id"
                                            :value="album.id"
                                        >
                                            {{ album.name }}
                                        </option>
                                    </select>
                                    <div v-if="addPhotosForm.errors.photo_ids" class="mt-1 text-xs text-red-600">
                                        {{ addPhotosForm.errors.photo_ids }}
                                    </div>
                                </div>
                                <button
                                    @click="addSelectedPhotosToAlbum"
                                    type="button"
                                    class="rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800 disabled:cursor-not-allowed disabled:bg-gray-300"
                                    :disabled="!selectedAlbumId || selectedPhotoCount === 0 || addPhotosForm.processing"
                                >
                                    {{ t('profile.add_selected', { count: selectedPhotoCount }) }}
                                </button>
                            </div>
                        </div>

                        <div v-if="activeAlbum" class="space-y-3">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <button
                                        @click="closeAlbum"
                                        type="button"
                                        class="mt-0.5 rounded-full p-2 text-gray-600 hover:bg-gray-100"
                                    >
                                        <ArrowLeftIcon class="h-5 w-5" />
                                    </button>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-900">{{ activeAlbum.name }}</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ activeAlbum.description || t('profile.photos_count', { count: albumPhotos(activeAlbum).length }) }}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    v-if="isMyProfile"
                                    @click="addSelectedPhotosToAlbum"
                                    type="button"
                                    class="rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800 disabled:cursor-not-allowed disabled:bg-gray-300"
                                    :disabled="selectedPhotoCount === 0 || addPhotosForm.processing"
                                >
                                    {{ t('profile.add_selected', { count: selectedPhotoCount }) }}
                                </button>
                            </div>

                            <div
                                v-if="albumPhotos(activeAlbum).length"
                                class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                            >
                                <div
                                    v-for="photo in albumPhotos(activeAlbum)"
                                    :key="photo.id"
                                    class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 ring-2 ring-transparent transition hover:ring-lime-200"
                                    :class="draggedPhotoId === photo.id ? 'opacity-60 ring-lime-700' : ''"
                                    :draggable="isMyProfile"
                                    @dragstart="startDraggingPhoto(photo)"
                                    @dragover.prevent
                                    @drop.prevent="dropPhoto(activeAlbum, photo)"
                                    @dragend="stopDraggingPhoto"
                                >
                                    <img
                                        :src="photo.url"
                                        :alt="photo.name"
                                        class="h-full w-full object-cover transition duration-200 group-hover:scale-105"
                                    />
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent p-2 text-xs font-semibold text-white">
                                        {{ photo.name }}
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">
                                {{ t('profile.no_album_photos') }}
                            </div>
                        </div>

                        <template v-else>
                            <div
                                v-if="profilePhotos.length"
                                class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                            >
                                <button
                                    v-for="photo in profilePhotos"
                                    :key="photo.id"
                                    type="button"
                                    @click="togglePhoto(photo)"
                                    class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 text-left ring-2 transition"
                                    :class="selectedPhotoIds.includes(photo.id) ? 'ring-lime-700' : 'ring-transparent hover:ring-lime-200'"
                                >
                                    <img
                                        :src="photo.url"
                                        :alt="photo.name"
                                        class="h-full w-full object-cover transition duration-200 group-hover:scale-105"
                                    />
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent p-2 text-xs font-semibold text-white">
                                        {{ photo.name }}
                                    </div>
                                    <div
                                        v-if="isMyProfile"
                                        class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-white/80"
                                        :class="selectedPhotoIds.includes(photo.id) ? 'text-lime-900' : 'text-transparent'"
                                    >
                                        <CheckCircleIcon class="h-5 w-5" />
                                    </div>
                                </button>
                            </div>

                            <div
                                v-else
                                class="rounded-lg border border-dashed border-lime-200 bg-lime-50 p-6 text-center text-sm text-lime-900"
                            >
                                {{ t('profile.no_photos') }}
                            </div>

                            <div class="space-y-3 pt-2">
                                <h3 class="text-base font-black text-gray-900">{{ t('profile.albums') }}</h3>

                                <div
                                    v-if="profileAlbums.length"
                                    class="grid gap-3 sm:grid-cols-2"
                                >
                                    <button
                                        v-for="album in profileAlbums"
                                        :key="album.id"
                                        @click="openAlbum(album)"
                                        type="button"
                                        class="rounded-lg border border-gray-200 p-3 text-left transition hover:border-lime-300 hover:bg-lime-50"
                                    >
                                        <div class="mb-3 flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h4 class="truncate font-black text-gray-900">{{ album.name }}</h4>
                                                <p class="truncate text-sm text-gray-500">
                                                    {{ album.description || t('profile.photos_count', { count: album.photos_count || albumPhotos(album).length || 0 }) }}
                                                </p>
                                            </div>
                                            <span class="rounded-full bg-lime-100 px-2 py-1 text-xs font-bold text-lime-900">
                                                {{ album.photos_count || albumPhotos(album).length || 0 }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="albumPhotos(album).length"
                                            class="grid grid-cols-4 gap-1"
                                        >
                                            <img
                                                v-for="photo in albumPhotos(album).slice(0, 8)"
                                                :key="photo.id"
                                                :src="photo.url"
                                                :alt="photo.name"
                                                class="aspect-square rounded object-cover"
                                            />
                                        </div>
                                        <div v-else class="rounded-md bg-gray-50 p-3 text-sm text-gray-500">
                                            {{ t('profile.no_album_photos') }}
                                        </div>
                                    </button>
                                </div>

                                <div v-else class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">
                                    {{ t('profile.no_albums') }}
                                </div>
                            </div>
                        </template>
                    </div>
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
