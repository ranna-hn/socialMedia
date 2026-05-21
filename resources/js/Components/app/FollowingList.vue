<script setup>
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import FollowingItem from '@/Components/App/FollowingItem.vue';

const searchKeyword = ref('');

</script>

<template>
    <div class="px-3 rounded-lg mb-4 border shadow-2xl py-3 overflow-hidden flex flex-col h-[500px] lg:h-full">
        <h2 class="text-xl font-bold mb-4 px-3">My Friends</h2>

        <TextInput :model-value="searchKeyword"
            placeholder="Search for friends..."
            class="w-full"
        />
            <div class=" mt-3 lg:flex-1 h-[300px] lg:h-auto overflow-auto">

                <div class="mb-4 text-gray-500 text-center p-3" v-if="false">
                    You don't have fiends yet.
                </div>
                
                <div class="space-y-3" v-else>
                    <FollowingItem
                        image="/storage/profilF1.jpg"
                        title="Sarah Amine"
                    />

                    <FollowingItem
                        image="/storage/profilM1.png"
                        title="Elon Musk"
                    />
                    <FollowingItem
                        image="/storage/profilF1.jpg"
                        title="Sarah Amine"
                    />

                    <FollowingItem
                        image="/storage/profilM1.png"
                        title="Elon Musk"
                    />
                    <FollowingItem
                        image="/storage/profilF1.jpg"
                        title="Sarah Amine"
                    />

                    <FollowingItem
                        image="/storage/profilM1.png"
                        title="Elon Musk"
                    />

                </div>
            </div>
    </div>

</template>

<style scoped>

</style>