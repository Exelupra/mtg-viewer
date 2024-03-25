<template>
    <div>
        <h1>Rechercher une carte</h1>
        <input type="text" v-model="searchTerm" placeholder="Entrez le nom de la carte...">
        <button @click="searchCards">Rechercher</button> <!-- Bouton de recherche -->
        <div v-if="loadingCards">Chargement en cours...</div>
        <div v-else>
            <div class="card" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }"> {{ card.name }} - {{ card.uuid }} </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { searchCardsByName } from '../services/cardService';

const searchTerm = ref('');
const loadingCards = ref(false);
const cards = ref([]);

const searchCards = async () => {
    loadingCards.value = true;
    try {
        if (searchTerm.value.length >= 3) {
            cards.value = await searchCardsByName(searchTerm.value);
        } else {
            cards.value = []; // Réinitialiser les cartes si la recherche est vide
        }
    } catch (error) {
        console.error('Error searching cards:', error);
    }
    loadingCards.value = false;
};
</script>
