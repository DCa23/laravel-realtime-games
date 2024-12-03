<template>
    <AuthenticatedLayout>
        <div class="flex">
            <div class="w-full">
                <TicTacToeBoard 
                    v-if="props.game.game_type === 'tictactoe'"
                    :game-state="gameState"
                    :board-state="boardState"
                    :page="page"
                    :game="props.game"
                    @update-status="updateStatus()"
                    ref="gameBoard"
                ></TicTacToeBoard>
                <WarshipBoard
                    v-else-if="props.game.game_type === 'warship'"
                    :game-state="gameState"
                    :board-state="boardState"
                    :page="page"
                    :game="props.game"
                    ref="gameBoard"
                    @update-status="updateStatus()"
                ></WarshipBoard>
                <ChessBoard
                    v-else-if="props.game.game_type === 'chess'"
                    :game-state="gameState"
                    :board-state="boardState"
                    :page="page"
                    :game="props.game"
                    :channel="channel"
                    ref="gameBoard"
                    @update-status="updateStatus()"
                ></ChessBoard>
            </div>
            <ul class="max-w-sm mx-auto mt-6 space-y-2 w-40">
                <li class="flex items-center gap-2">
                    <span :class="{'!bg-green-200': gameState.current() === gameStates.P1Turn }" class="p-1.5 font-bold rounded bg-gray-200">X</span>
                    <span>{{ game.player_one.name }}</span>
                    <span :class="{'!bg-green-500': players.find(({id}) => id === game.player_one_id)}" class="bg-red-500 size-2 rounded-full"></span>
                </li>
                <li v-if="game.player_two" class="flex items-center gap-2">
                    <span :class="{'!bg-green-200': gameState.current() === gameStates.P2Turn }" class="p-1.5 font-bold rounded bg-gray-200">O</span>
                    <span> {{ game.player_two.name }}</span>
                    <span :class="{'!bg-green-500': players.find(({id}) => id === game.player_two_id)}" class="bg-red-500 size-2 rounded-full"></span>
                </li>
                <li v-else class="flex items-center gap-2">
                    <span>Waiting second player to connect ... </span>
                </li>
                <li>
                    <button @click="() => showLeaveModal = true" class="rounded-md border border-transparent bg-red-500 p-1 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-700 focus:bg-red-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 active:bg-red-900">
                        Leave the game    
                    </button>
                </li>
            </ul>
        </div>
        
        <Modal @close="resetGame()" :show="gameState.hasEnded()">
            <div class="p-6">
                <div class="text-6xl font-bold text-center my-12 font-mono uppercase">
                    <span class="text-green-600" v-if="gameState.current() === gameStates.P1Wins">P1 won the game</span>
                    <span class="text-green-600" v-else-if="gameState.current() === gameStates.P2Wins">P2 won the game</span>
                    <span class="text-orange-600" v-else-if="gameState.current() === gameStates.Draw">Draw</span>
                    <span class="text-orange-600" v-else-if="gameState.current() === gameStates.Stalemate">Stalemate</span>
                </div>

                <div class="mt-6 flex justify-end">
                    <button as="button" @click="resetGame()" class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                        Play again
                    </button>
                </div>
            </div>
        </Modal>

        <Modal @close="closeLeaveModal()" :show="showLeaveModal">
            <div v-if="!enemyLeft" class="p-6">
                <div class="text-xl font-bold text-center my-12 font-mono uppercase">
                    Are you sure you want to leave the game?
                </div>

                <div class="mt-6 flex gap-2 justify-center">
                    <button as="button"  class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                        Cancel
                    </button>
                    <Link :href="route('games.destroy', {game: props.game})" method="delete" as="button" class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-red-500 p-1 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-700 focus:bg-red-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 active:bg-red-900">
                        Leave
                    </Link>
                </div>
            </div>
            <div v-else class="p-6">
                <div class="text-xl font-bold text-center my-12 font-mono uppercase">
                    Your enemy left the game, you will be redirected to the dashboard
                </div>

                <div class="mt-6 flex gap-2 justify-center">
                    <button as="button"  class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                        Accept
                    </button>
                </div>
            </div>
        </Modal>
        
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from "@/Components/Modal.vue";
import {  onMounted, onUnmounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {useGameState, gameStates} from "@/Composables/useGameState.js";
import TicTacToeBoard from '@/Boards/TicTacToeBoard.vue';
import WarshipBoard from '@/Boards/WarshipBoard.vue';
import ChessBoard from '@/Boards/ChessBoard.vue';

const gameBoard = ref(null);
const props = defineProps(['game']);
const players = ref([]);
const page = usePage();
const boardState = ref(props.game.board_state ?? []);
const gameState = useGameState(props.game.game_state);
const showLeaveModal = ref(false);
const enemyLeft = ref(false);

onMounted(() => {
    checkForVictory();
});

const channel = Echo.join(`game.${props.game.id}`)
    .here((users) => players.value = users)
    .joining((user) => router.reload({
        onSuccess: () => players.value.push(user)
    }))
    .leaving((user) => players.value = players.value.filter(({id}) => id !== user.id))
    .listen('GameLeft', (game) => {
        // Redirect user to the lobby
        showLeaveModal.value = true;
        enemyLeft.value = true;
    })
    .listenForWhisper('PlayerMadeMove', (game) => {
        gameBoard.value.mergeBoardState(game.board_state);
        gameState.change(game.game_state);
        if (game.game_state == gameStates.PreparingB) {
            gameBoard.value.resetGame();
        }
    }); 

const checkForVictory = () => {
    gameBoard.value.checkForVictory();
}

const resetGame = () => {
    gameBoard.value.resetGame();

    updateStatus();
}

const updateStatus = () => {
    router.put(route('games.update', props.game.id),{
        board_state:  boardState.value,
        game_state: gameState.current()
    });
    channel.whisper('PlayerMadeMove', {
        board_state: boardState.value,
        game_state: gameState.current()
    });
}

const closeLeaveModal = () => {
    showLeaveModal.value = false;
    if (enemyLeft.value) {
        router.get(route('dashboard'));
    }
};

onUnmounted(() => {
    Echo.leave(`game.${props.game.id}`);
});
</script>
