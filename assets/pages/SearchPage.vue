<template>
    <div>
        <h1>Rechercher une carte</h1>
        <input type="text" v-model="searchTerm" placeholder="Entrez le nom de la carte...">
        <select v-model="selectedSetCode">
            <option value="">Tous les setCodes</option>
            <option v-for="code in setCodes" :key="code">{{ code.setCode }}</option>
        </select>
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
const searchTerm = ref('');
const selectedSetCode = ref('');
const loadingCards = ref(false);
const cards = ref([]);
const setCodes = ref([]);

// Fonction pour récupérer la liste des setCodes
const fetchSetCodes = async () => {
    try {
        const response = await fetch('/api/card/set-codes');
        if (!response.ok) {
            throw new Error('Failed to fetch set codes');
        }
        setCodes.value = await response.json();
    } catch (error) {
        console.error('Error fetching set codes:', error);
    }
};

// Appel la fonction pour récupérer les setCodes au montage du composant
fetchSetCodes();

// Fonction de recherche avec filtre setCode
const searchCards = async () => {
    loadingCards.value = true;
    try {
        if (searchTerm.value.length >= 3) {
            const response = await fetch(`/api/card/search?q=${searchTerm.value}&setCode=${selectedSetCode.value}`);
            if (!response.ok) {
                throw new Error('Failed to search cards');
            }
            cards.value = await response.json();
        } else {
            cards.value = []; // Réinitialiser les cartes si la recherche est vide
        }
    } catch (error) {
        console.error('Error searching cards:', error);
    }
    loadingCards.value = false;
};

</script>
