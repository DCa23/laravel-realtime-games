<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg h-56"
                >
                    <div class="p-6 textp-gray-900">
                        <div class="flex justify-between">
                            <Dropdown align="down">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                                        >
                                            Create a game
                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('games.store',{'type' : 'tictactoe'})" method="post" as="button">
                                        Tic Tac Toe
                                    </DropdownLink>
                                    <DropdownLink :href="route('games.store',{'type' : 'warship'})" method="post" as="button">
                                        Warship
                                    </DropdownLink>
                                    <DropdownLink :href="route('games.store',{'type' : 'chess'})" method="post" as="button">
                                        Chess
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                            <button @click="reloadGameList()" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                                Refresh game list
                            </button>
                        </div>
                        <ul class="divide-y my-6">
                            <li v-for="game in games" :key="game.id" class="py-2 px-4 flex justify-between items-center">
                                <span> {{ game.player_one.name }} </span>
                                <span> {{ game.game_type }} </span>
                                <Link as="button" method="post" :href="route('games.join',game)" class="hover:bg-gray-200 transition-colors p-2">Join Game</Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
const props = defineProps(['games']);
const games = ref(props.games.data);
let intervalId = null;

Echo.private('lobby')
    .listen('GameJoined', (event) => {
        games.value = games.value.filter((game) => game.id !== event.game.id);
    });

const startReloadJob = () => {
    intervalId = setInterval(reloadGameList, 60 * 1000);
};

const reloadGameList = () => {
    router.reload({ only: ['games'], onSuccess: () => games.value = props.games.data })
};

const stopReloadJob = () => {
    if (intervalId) {
        clearInterval(intervalId);
    }
};

onBeforeUnmount(() => {
    stopReloadJob();
})

onMounted(() => {
    startReloadJob();
})

</script>