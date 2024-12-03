<template>
    <menu class="mt-16 grid grid-cols-3 gap-1.5 w-0 min-w-fit mx-auto">
        <li v-for="(square,index) in props.boardState" class="bg-gray-300 size-32 grid place-items-center">
            <button @click="fillSquare(index)" v-if="square === 0" class="place-self-stretch bg-gray-200 hover:bg-gray-300 transition-colors"></button>
            <span v-else v-text="square === -1? 'X': 'O' " class="text-4xl font-bold"></span>
        </li>
    </menu>
</template>


<script setup>
import { gameStates } from '@/Composables/useGameState';
import { computed, onMounted } from 'vue';
const props = defineProps(['gameState','boardState','game','page']);
const emit = defineEmits(['updateStatus']);

onMounted(() => {
    if (props.boardState.length === 0) {
        for (let i = 0; i < 9; i++){
            props.boardState.push(0);
        }
    }
})

const yourTurn = computed(() => {
    if (props.game.player_one_id === props.page.props.auth.user.id && props.gameState.current() === gameStates.P1Turn) {
        return true;
    } else if (props.game.player_two_id === props.page.props.auth.user.id && props.gameState.current() === gameStates.P2Turn) {
        return true;
    }
    return false;
});

const lines = [
    // rows
    [0 , 1 , 2],
    [3 , 4 , 5],
    [6 , 7 , 8],

    // cols
    [0 , 3 , 6],
    [1 , 4 , 7],
    [2 , 5 , 8],

    // Diagonals
    [0 , 4 , 8],
    [2 , 4 , 6],
];

const fillSquare = (index) => {
    if (!yourTurn.value) {
        return;
    }

    if (props.gameState.current() === gameStates.P1Turn) {
        props.boardState[index] = -1;
    } else {
        props.boardState[index] = 1;
    }
    props.gameState.change(props.gameState.current() === gameStates.P1Turn ? gameStates.P2Turn: gameStates.P1Turn);

    checkForVictory();

    updateStatus();
};

const checkForVictory = () => {
    const winningLine = lines.map((line) => line.reduce((carry, index) => carry + props.boardState[index],0))
        .find((sum) => Math.abs(sum) == 3);

    if (winningLine == 3) {
        props.gameState.change(gameStates.P2Wins);
        return;
    } 
    if ( winningLine == -3 ) {
        props.gameState.change(gameStates.P1Wins);
        return;
    }

    if (! props.boardState.includes(0)) {
        props.gameState.change(gameStates.Draw);
        return;
    }
    
};

const mergeBoardState = (newState) => {
    for (var i = 0; i < newState.length; i ++) {
        props.boardState[i] = newState[i];
    }
}

const resetGame = () => {
    for(var i = 0; i < props.boardState.length ; i++) {
        props.boardState[i] = 0;
    }
    props.gameState.change(gameStates.P1Turn);
}

defineExpose({
    checkForVictory,
    mergeBoardState,
    resetGame
});


const updateStatus = () => {
    emit('updateStatus');
}
</script>