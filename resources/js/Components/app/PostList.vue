<script setup>
import PostItem from './PostItem.vue';

defineProps({
    posts: Array,
});

const post1={
    user:{
        id:1,
        avatar:"/storage/profilM2.jpg",
        name:"Sami Alaoui",
    },

    group:null,
    attachments:[
        {
            id:1,
            name:"3.jpg",
            mime:"image/jpg",
            url:"/storage/3.jpg"

        },
        {
            id:2,
            name:"3.jpg",
            mime:"image/jpg",
            url:"/storage/3.jpg"
        },
        {
            id:3,
            name:"mydoc.docx",
            mime:'application/msword',
            url:"#"
        }
    ],

    body:
    `<p>Voici un post de test pour vérifier l'affichage des posts dans la liste des posts. 
    Ce post contient du texte et une image pour tester la mise en page et le rendu des éléments multimédias.</p>
    <p>Le but de ce post est de s'assurer que les composants fonctionnent correctement et que les données sont affichées comme prévu. 
    Nous allons également vérifier que les interactions avec les posts, comme les likes et les commentaires, sont bien gérées.</p>`,

    created_at:"2024-06-01 14:30:00"
}

const post2={
    user:{
        id:1,
        avatar:"/storage/profilF2.jpg",
        name:"Sarah Amine",
    },

    group:{
        id:1,
        name:"Nature Lovers",
    },

    body:
    `<p>Voici un autre post de test pour vérifier l'affichage des posts dans la liste des posts. 
    Ce post contient du texte et une image pour tester la mise en page et le rendu des éléments multimédias.</p>
    <p>Le but de ce post est de s'assurer que les composants fonctionnent correctement et que les données sont affichées comme prévu. 
    Nous allons également vérifier que les interactions avec les posts, comme les likes et les commentaires, sont bien gérées.</p>`,

    created_at:"2024-06-02 10:15:00"
}

</script>

<template>
    <div class=" overflow-auto ">
        <PostItem v-for="post of posts" :key="post.id" :post="post"/>
    </div>
</template>