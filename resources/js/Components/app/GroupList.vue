<script setup>
import GroupItem from '@/Components/App/GroupItem.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const searchKeyword = ref('');

</script>

<template>
    <div class="px-3 rounded-lg mb-4 py-3 h-[500px] border shadow-2xl overflow-hidden flex flex-col ">
        <h2 class="text-xl font-bold mb-4 px-3">My Groups</h2>

        <TextInput :model-value="searchKeyword"
            placeholder="Search groups..."
            class="mb-4"/>
            <div class=" flex-1 overflow-auto">
                <div class="mb-4 text-gray-500 text-center p-3" v-if="false">
                    You are not a member of any groups yet.
                </div>
                
                <div  v-else>
                    <GroupItem
                        image="/storage/1.jpg"
                        title="Nature Lovers"
                        description="Nature Lovers : aimons, protégeons, partageons !"
                    />

                    <GroupItem
                        image="/storage/2.jpg"
                        title="EcoPulse"
                        description="Recyclez aujourd'hui, respirez demain !"
                    />
                    <GroupItem
                        image="/storage/1.jpg"
                        title="Nature Lovers"
                        description="Nature Lovers : aimons, protégeons, partageons !"
                    />

                    <GroupItem
                        image="/storage/2.jpg"
                        title="EcoPulse"
                        description="Recyclez aujourd'hui, respirez demain !"
                    />
                    <GroupItem
                        image="/storage/1.jpg"
                        title="Nature Lovers"
                        description="Nature Lovers : aimons, protégeons, partageons !"
                    />

                    <GroupItem
                        image="/storage/2.jpg"
                        title="EcoPulse"
                        description="Recyclez aujourd'hui, respirez demain !"
                    />
                    <GroupItem
                        image="/storage/1.jpg"
                        title="Nature Lovers"
                        description="Nature Lovers : aimons, protégeons, partageons !"
                    />

                    <GroupItem
                        image="/storage/2.jpg"
                        title="EcoPulse"
                        description="Recyclez aujourd'hui, respirez demain !"
                    />
                    <GroupItem
                        image="/storage/1.jpg"
                        title="Nature Lovers"
                        description="Nature Lovers : aimons, protégeons, partageons !"
                    />

                    <GroupItem
                        image="/storage/2.jpg"
                        title="EcoPulse"
                        description="Recyclez aujourd'hui, respirez demain !"
                    />

                </div>
            </div>    
    </div>

</template>

<style scoped>

</style>