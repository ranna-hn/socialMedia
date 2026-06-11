<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreatePost from '@/Components/app/CreatePost.vue';
import PostList from '@/Components/app/PostList.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    CheckIcon,
    XMarkIcon,
    UserMinusIcon,
    UserGroupIcon,
    LockClosedIcon,
    GlobeAltIcon,
    InformationCircleIcon,
    EnvelopeIcon,
    ClockIcon,
    CameraIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    group: Object,
    posts: Object,
    members: Object,
    pendingMembers: Object,
    inviteUsers: Object,
});

const resourceData = (resource, fallback = null) => resource?.data ?? resource ?? fallback;
const resourceItems = (resource) => {
    if (Array.isArray(resource)) {
        return resource;
    }

    if (Array.isArray(resource?.data)) {
        return resource.data;
    }

    return [];
};

const group = computed(() => resourceData(props.group, {}));
const posts = computed(() => resourceItems(props.posts));
const members = computed(() => resourceItems(props.members));
const pendingMembers = computed(() => resourceItems(props.pendingMembers));
const inviteUsers = computed(() => resourceItems(props.inviteUsers));
const owner = computed(() => resourceData(group.value.owner, null));

const canPost = computed(() =>
    group.value.is_admin || group.value.membership_status === 'approved'
);

const memberCount = computed(() => group.value.member_count ?? members.value.length);
const coverStyle = computed(() => ({
    backgroundImage: `linear-gradient(90deg, rgba(20, 83, 45, .88), rgba(63, 98, 18, .55), rgba(255,255,255,.05)), url('${group.value.cover_url || '/storage/cover7.jpg'}')`,
}));

const approvalText = computed(() =>
    group.value.auto_approval ? 'Auto approval' : 'Manual approval'
);

const membershipText = computed(() => {
    if (group.value.membership_status === 'approved') {
        return 'Member';
    }

    if (group.value.membership_status === 'pending') {
        return 'Request pending';
    }

    return 'Public preview';
});

const groupDescription = computed(() =>
    group.value.about || 'A fresh space for nature lovers to share ideas, actions, and updates.'
);

const coverInput = ref(null);
const coverForm = useForm({
    cover: null,
});

const inviteForm = useForm({
    user_ids: [],
});

function groupSlug() {
    return group.value.slug;
}

function joinGroup() {
    if (!groupSlug()) {
        return;
    }

    router.post(route('groups.join', groupSlug()), {}, {
        preserveScroll: true,
    });
}

function submitInvite() {
    if (!groupSlug()) {
        return;
    }

    inviteForm.post(route('groups.invite', groupSlug()), {
        preserveScroll: true,
        onSuccess: () => inviteForm.reset(),
    });
}

function openCoverPicker() {
    coverInput.value?.click();
}

function updateCover(event) {
    if (!groupSlug()) {
        return;
    }

    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    coverForm.cover = file;
    coverForm.post(route('groups.cover.update', groupSlug()), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            coverForm.reset();
            if (coverInput.value) {
                coverInput.value.value = '';
            }
        },
    });
}

function removeCover() {
    if (!groupSlug() || !window.confirm('Remove this group cover?')) {
        return;
    }

    router.delete(route('groups.cover.destroy', groupSlug()), {
        preserveScroll: true,
    });
}

function approve(membership) {
    if (!groupSlug()) {
        return;
    }

    router.patch(route('groups.members.approve', [groupSlug(), membership.id]), {}, {
        preserveScroll: true,
    });
}

function reject(membership) {
    if (!groupSlug()) {
        return;
    }

    router.patch(route('groups.members.reject', [groupSlug(), membership.id]), {}, {
        preserveScroll: true,
    });
}

function updateRole(membership, role) {
    if (!groupSlug()) {
        return;
    }

    router.patch(route('groups.members.role', [groupSlug(), membership.id]), { role }, {
        preserveScroll: true,
    });
}

function removeMember(membership) {
    if (!window.confirm('Remove this member?') || !groupSlug()) {
        return;
    }

    router.delete(route('groups.members.destroy', [groupSlug(), membership.id]), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="group.name || 'Group'" />

    <AuthenticatedLayout>
        <div class="h-full overflow-auto bg-stone-100/70">
            <div class="mx-auto max-w-6xl px-4 py-5">
                <section
                    class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5"
                >
                    <div
                        class="relative min-h-[260px] bg-cover bg-center px-5 py-6 text-white md:min-h-[310px] md:px-8 md:py-8"
                        :style="coverStyle"
                    >
                        <div v-if="group.is_admin" class="absolute right-4 top-4 flex flex-wrap justify-end gap-2">
                            <input
                                ref="coverInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="updateCover"
                            />
                            <button
                                type="button"
                                @click="openCoverPicker"
                                class="inline-flex items-center rounded-md bg-white/90 px-3 py-2 text-xs font-bold text-lime-950 shadow-sm backdrop-blur hover:bg-white disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="coverForm.processing"
                            >
                                <CameraIcon class="mr-1 h-4 w-4" />
                                {{ group.has_cover ? 'Change cover' : 'Add cover' }}
                            </button>
                            <button
                                v-if="group.has_cover"
                                type="button"
                                @click="removeCover"
                                class="inline-flex items-center rounded-md bg-red-600/90 px-3 py-2 text-xs font-bold text-white shadow-sm backdrop-blur hover:bg-red-700"
                            >
                                <TrashIcon class="mr-1 h-4 w-4" />
                                Remove cover
                            </button>
                        </div>
                        <div
                            v-if="coverForm.errors.cover"
                            class="absolute left-4 top-4 max-w-xs rounded-md bg-red-600/95 px-3 py-2 text-xs font-semibold text-white shadow-sm"
                        >
                            {{ coverForm.errors.cover }}
                        </div>
                        <div class="flex h-full min-h-[220px] flex-col justify-end gap-5 md:min-h-[250px]">
                            <div class="max-w-3xl">
                                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                                    <GlobeAltIcon class="h-4 w-4" />
                                    EchoNature Group
                                </div>
                                <h1 class="text-3xl font-black leading-tight md:text-5xl">
                                    {{ group.name }}
                                </h1>
                                <p class="mt-3 max-w-2xl text-sm font-medium text-white/90 md:text-base">
                                    {{ groupDescription }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-white/95">
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1.5 backdrop-blur">
                                    <UserGroupIcon class="h-4 w-4" />
                                    {{ memberCount }} members
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1.5 backdrop-blur">
                                    <LockClosedIcon class="h-4 w-4" />
                                    {{ approvalText }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1.5 backdrop-blur">
                                    <ClockIcon class="h-4 w-4" />
                                    {{ membershipText }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 border-b border-stone-200 px-5 py-4 md:flex-row md:items-center md:justify-between md:px-8">
                        <nav class="flex flex-wrap gap-2 text-sm font-bold text-stone-600">
                            <a href="#about" class="rounded-md px-3 py-2 hover:bg-lime-50 hover:text-lime-900">About</a>
                            <a href="#posts" class="rounded-md px-3 py-2 hover:bg-lime-50 hover:text-lime-900">Posts</a>
                            <a href="#members" class="rounded-md px-3 py-2 hover:bg-lime-50 hover:text-lime-900">Members</a>
                            <a v-if="group.is_admin" href="#manage" class="rounded-md px-3 py-2 hover:bg-lime-50 hover:text-lime-900">Manage</a>
                        </nav>

                        <button
                            v-if="!group.membership_status"
                            @click="joinGroup"
                            class="rounded-md bg-lime-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-lime-800"
                        >
                            Join group
                        </button>
                        <div
                            v-else-if="group.membership_status === 'pending'"
                            class="rounded-md bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800"
                        >
                            Request pending
                        </div>
                    </div>
                </section>

                <div class="mt-4 grid gap-4 lg:grid-cols-12">
                    <main id="posts" class="space-y-4 lg:col-span-8">
                        <section class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-black text-stone-900">Group posts</h2>
                                    <p class="text-sm text-stone-500">Share updates, photos, and eco ideas with this community.</p>
                                </div>
                            </div>

                            <CreatePost v-if="canPost" :group-id="group.id" />
                            <div v-else class="rounded-md border border-lime-100 bg-lime-50 p-4 text-sm text-lime-900">
                                Join this group before publishing.
                            </div>
                        </section>

                        <PostList v-if="posts.length" :posts="posts" />
                        <div
                            v-else
                            class="rounded-lg border border-dashed border-lime-200 bg-white p-8 text-center shadow-sm"
                        >
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-lime-100 text-lime-900">
                                <InformationCircleIcon class="h-6 w-6" />
                            </div>
                            <h3 class="text-base font-black text-stone-900">No posts yet</h3>
                            <p class="mt-1 text-sm text-stone-500">The first group post will appear here.</p>
                        </div>
                    </main>

                    <aside class="space-y-4 lg:col-span-4">
                        <section id="about" class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
                            <h2 class="mb-3 text-lg font-black text-stone-900">About</h2>
                            <p class="text-sm leading-6 text-stone-600">{{ groupDescription }}</p>

                            <div class="mt-4 space-y-3 text-sm">
                                <div class="flex items-start gap-3 rounded-md bg-stone-50 p-3">
                                    <LockClosedIcon class="mt-0.5 h-5 w-5 text-lime-900" />
                                    <div>
                                        <div class="font-bold text-stone-900">{{ approvalText }}</div>
                                        <p class="text-stone-500">
                                            {{ group.auto_approval ? 'New members can join quickly.' : 'An admin approves new member requests.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 rounded-md bg-stone-50 p-3">
                                    <UserGroupIcon class="mt-0.5 h-5 w-5 text-lime-900" />
                                    <div>
                                        <div class="font-bold text-stone-900">{{ memberCount }} members</div>
                                        <p class="text-stone-500">
                                            Owner: {{ owner?.name || 'Group admin' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section v-if="group.is_admin" id="manage" class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
                            <h2 class="mb-3 flex items-center gap-2 text-lg font-black text-stone-900">
                                <EnvelopeIcon class="h-5 w-5 text-lime-900" />
                                Invite users
                            </h2>
                            <form @submit.prevent="submitInvite" class="space-y-3">
                                <div class="max-h-48 space-y-2 overflow-auto rounded-md border border-stone-200 p-2">
                                    <label
                                        v-for="user in inviteUsers"
                                        :key="user.id"
                                        class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-lime-50"
                                    >
                                        <input
                                            v-model="inviteForm.user_ids"
                                            :value="user.id"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-lime-800 focus:ring-lime-800"
                                        />
                                        <span>{{ user.name }}</span>
                                    </label>
                                    <div v-if="inviteUsers.length === 0" class="p-2 text-sm text-stone-500">
                                        No users available.
                                    </div>
                                </div>
                                <div v-if="inviteForm.errors.user_ids" class="text-xs text-red-600">
                                    {{ inviteForm.errors.user_ids }}
                                </div>
                                <button class="w-full rounded-md bg-lime-900 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-800">
                                    Send invitation
                                </button>
                            </form>
                        </section>

                        <section v-if="group.is_admin && pendingMembers.length" class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
                            <h2 class="mb-3 text-lg font-black text-stone-900">Pending requests</h2>
                            <div class="space-y-3">
                                <div
                                    v-for="membership in pendingMembers"
                                    :key="membership.id"
                                    class="flex items-center justify-between gap-3 rounded-md bg-stone-50 p-2"
                                >
                                    <div class="flex min-w-0 items-center gap-2">
                                        <img :src="membership.user?.avatar_url || '/storage/default_avatar.png'" :alt="membership.user?.name" class="h-9 w-9 rounded-full object-cover" />
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold">{{ membership.user?.name }}</div>
                                            <div class="truncate text-xs text-stone-500">@{{ membership.user?.username }}</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button @click="approve(membership)" class="rounded-full bg-emerald-100 p-2 text-emerald-700 hover:bg-emerald-200">
                                            <CheckIcon class="h-4 w-4" />
                                        </button>
                                        <button @click="reject(membership)" class="rounded-full bg-red-100 p-2 text-red-700 hover:bg-red-200">
                                            <XMarkIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="members" class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
                            <h2 class="mb-3 text-lg font-black text-stone-900">Members</h2>
                            <div class="space-y-3">
                                <div
                                    v-for="membership in members"
                                    :key="membership.id"
                                    class="rounded-md bg-stone-50 p-2"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <img :src="membership.user?.avatar_url || '/storage/default_avatar.png'" :alt="membership.user?.name" class="h-9 w-9 rounded-full object-cover" />
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-bold">{{ membership.user?.name }}</div>
                                                <div class="truncate text-xs text-stone-500">@{{ membership.user?.username }}</div>
                                            </div>
                                        </div>
                                        <button
                                            v-if="group.is_admin"
                                            @click="removeMember(membership)"
                                            class="rounded-full p-2 text-red-600 hover:bg-red-50"
                                        >
                                            <UserMinusIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <select
                                        v-if="group.is_admin"
                                        :value="membership.role"
                                        @change="updateRole(membership, $event.target.value)"
                                        class="mt-2 w-full rounded-md border-gray-300 text-sm"
                                    >
                                        <option value="member">member</option>
                                        <option value="admin">admin</option>
                                    </select>
                                    <div v-else class="mt-1 text-xs text-stone-500">{{ membership.role }}</div>
                                </div>

                                <div v-if="members.length === 0" class="rounded-md bg-stone-50 p-3 text-sm text-stone-500">
                                    No members yet.
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
