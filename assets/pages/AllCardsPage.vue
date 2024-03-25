<template>
    <div>
        <h1>Toutes les cartes</h1>
        <div class="card-list">
            <div v-if="loadingCards">Chargement en cours...</div>
            <div v-else>
                <div class="card-result" v-for="card in cards" :key="card.id">
                    <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                        {{ card.name }} <span>({{ card.uuid }})</span>
                    </router-link>
                </div>
            </div>
        </div>
        <div class="pagination">
            <button @click="prevPage" :disabled="currentPage === 1">Page précédente</button>
            <span>Page {{ currentPage }}</span>
            <button @click="nextPage" :disabled="cards.length < pageSize">Page suivante</button>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const cards = ref([]);
const loadingCards = ref(true);
const currentPage = ref(1);
const pageSize = 100;

async function loadCards() {
    loadingCards.value = true;
    const response = await fetch(`/api/card/all?page=${currentPage.value}&pageSize=${pageSize}`);
    const data = await response.json();
    cards.value = data;
    loadingCards.value = false;
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
        loadCards();
    }
}

function nextPage() {
    currentPage.value++;
    loadCards();
}

onMounted(() => {
    loadCards();
});
</script>
