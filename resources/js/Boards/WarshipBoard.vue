<template>
    <div class="flex gap-2 items-stretch">
        <div class="flex-1 p-4 text-center" :class="{'bg-green-100': yourTurn && !isPlayer1}">
            <div v-if="isPlayer1 && gameState.gettingReady()" class="flex justify-between">
                <div class="text-2xl">P1 Board </div>
                <div class="text-2xl">ShipsLeft: {{shipsLeft}}</div>
                <button @click="setBoardReady" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900" type="button" :disabled="!gameState.gettingReady() || !gameState.current() == gameStates.PreparingP2">ready</button>
            </div>
            <div v-else class="text-2xl">P1 Board </div>
            <menu class="mt-16 grid grid-cols-10 gap-1.5 w-0 min-w-fit mx-auto">
                <li v-for="(square,index) in props.boardState[0]" class="bg-gray-300 size-16 grid place-items-center">
                    <button @click="fillSquare(0,index)" :class="boardStyles[0][index] + ' place-self-stretch transition-colors'"></button>
                </li>
            </menu>
        </div>
        <div class="flex-1 p-4 text-center" :class="{'bg-green-100': yourTurn && isPlayer1}">
            <div v-if="!isPlayer1 && gameState.gettingReady()" class="flex justify-between">
                <div class="text-2xl">P2 Board </div>
                <div class="text-2xl">ShipsLeft: {{shipsLeft}}</div>
                <button @click="setBoardReady" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900" type="button" :disabled="!gameState.gettingReady() || !gameState.current() == gameStates.PreparingP2">ready</button>
            </div>
            <div v-else class="text-2xl">P2 Board </div>
            <menu class="mt-16 grid grid-cols-10 gap-1.5 w-0 min-w-fit mx-auto">
                <li v-for="(square,index) in props.boardState[1]" class="bg-gray-300 size-16 grid place-items-center">
                    <button @click="fillSquare(1,index)" :class="boardStyles[1][index] + ' place-self-stretch transition-colors'"></button>
                </li>
            </menu>
        </div>
    </div>
</template>


<script setup>
import { gameStates } from '@/Composables/useGameState';
import { computed, onBeforeMount, ref } from 'vue';

const props = defineProps(['gameState','boardState','game','page']);
const emit = defineEmits(['updateStatus']);
const shipsLeft = ref(10);
const boardStyles = ref([]);

const prepareStyles = () => {
    for (let x = 0; x < 2; x++){
        if (boardStyles.value[x] === undefined) {
            boardStyles.value.push([]);
        }
        for (let i = 0; i < 100; i++){
            if (props.boardState[x][i] == 0) {
                boardStyles.value[x].push('bg-blue-200 hover:bg-gray-300');
            } else if (props.boardState[x][i] == 1) {
                boardStyles.value[x].push('bg-blue-800 hover:bg-gray-300');
            } else if (props.boardState[x][i] == 2) {
                if (x == 0 && isPlayer1.value) {
                    boardStyles.value[x].push('bg-black');
                    shipsLeft.value = shipsLeft.value -1;
                } else if(x == 1 && !isPlayer1.value) {
                    boardStyles.value[x].push('bg-black');
                    shipsLeft.value = shipsLeft.value -1;
                } else {
                    boardStyles.value[x].push('bg-blue-200 hover:bg-gray-300');
                }
            } else if (props.boardState[x][i] == 3) {
                boardStyles.value[x].push('bg-red-600 hover:bg-gray-300');
            }
        }
    }
}

onBeforeMount(() => {
    // // If no game was loaded prevously get the board ready, and set the state to both preparing
    if (props.boardState.length === 0) {
        for (let x = 0; x < 2; x++){
            props.boardState.push([]);
            for (let i = 0; i < 100; i++){
                props.boardState[x].push(0);
            }
        }
        props.gameState.change(gameStates.PreparingB);
    }
    prepareStyles();
})

const isPlayer1 = computed(() => {
    return props.game.player_one_id == props.page.props.auth.user.id;
});

const yourTurn = computed(() => {
    if (isPlayer1.value && props.gameState.current() === gameStates.P1Turn) {
        return true;
    }
    if (!isPlayer1.value && props.gameState.current() === gameStates.P2Turn) {
        return true;
    }
    return false;
});

const setBoardReady = () => {
    if (isPlayer1.value) {
        if (props.gameState.current() === gameStates.PreparingP1) {
            props.gameState.change(gameStates.P1Turn);
        } else if(props.gameState.current() === gameStates.PreparingB) {
            props.gameState.change(gameStates.PreparingP2);
        }
    } else {
        if (props.gameState.current() === gameStates.PreparingP2) {
            props.gameState.change(gameStates.P1Turn);
        } else if(props.gameState.current() === gameStates.PreparingB) {
            props.gameState.change(gameStates.PreparingP1);
        }
    }
    emit('updateStatus');
};

const canFillBoard = (board) => {
    if (board === 0 && isPlayer1.value && props.gameState.gettingReady()) {
        return true;
    }

    if (board === 1 && !isPlayer1.value && props.gameState.gettingReady()) {
        return true;
    }

    if (board === 1 && isPlayer1.value && props.gameState.current() === gameStates.P1Turn) {
        return true;
    }

    if (board === 0 && !isPlayer1.value && props.gameState.current() === gameStates.P2Turn) {
        return true;
    }

    return false;
};

// Board reference
// 0 = Water
// 1 = Water 'tocado'
// 2 = Ship
// 3 = Ship 'tocado'
const fillSquare = (board,index) => {
    if (! canFillBoard(board)) {
        return;
    }

    if (props.gameState.gettingReady()) {
            if(props.boardState[board][index] == 0) {
                if (shipsLeft.value > 0) {
                    props.boardState[board][index] = 2;
                    boardStyles.value[board][index] = 'bg-black';
                    shipsLeft.value = shipsLeft.value -1;
                }
            }else if (props.boardState[board][index] == 2) {
                props.boardState[board][index] = 0;
                boardStyles.value[board][index] = 'bg-blue-200 hover:bg-gray-300';
                shipsLeft.value = shipsLeft.value +1;
            }
        return;
    }

    if (!yourTurn.value || props.boardState[board][index] == 1 || props.boardState[board][index] == 3) {
        return;
    }

    // You hit water
    if (props.boardState[board][index] == 0) {
        props.boardState[board][index] = 1;
        boardStyles.value[board][index] = 'bg-blue-800';
    // You actually hit a ship tile
    } else if (props.boardState[board][index] == 2) {
        props.boardState[board][index] = 3;
        boardStyles.value[board][index] = 'bg-red-600';
    }

    changeTurn();
    checkForVictory();

    emit('updateStatus');
};


const checkForVictory = () => {
    if (props.gameState.gettingReady()) {
        return;
    }
    if (props.boardState[0].indexOf(2) === -1 ) {
        props.gameState.change(gameStates.P2Wins);
        return;
    }
    if (props.boardState[1].indexOf(2) === -1 ) {
        props.gameState.change(gameStates.P1Wins);
        return;
    }
};

const changeTurn = () => {
    if (isPlayer1.value) {
        props.gameState.change(gameStates.P2Turn);
    } else {
        props.gameState.change(gameStates.P1Turn);
    }
};

const mergeBoardState = (newState) => {
    if (props.gameState.gettingReady()){
        if (isPlayer1.value) {
            for (var i = 0; i < newState[1].length; i ++) {
                props.boardState[1][i] = newState[1][i];
            }
        } else  {
            for (var i = 0; i < newState[0].length; i ++) {
                props.boardState[0][i] = newState[0][i];
            }
        }
    } else {
        if (isPlayer1.value) {
            for (var i = 0; i < newState[1].length; i ++) {
                props.boardState[0][i] = newState[0][i];
            }
        } else  {
            for (var i = 0; i < newState[0].length; i ++) {
                props.boardState[1][i] = newState[1][i];
            }
        }
        recalculateStyles();
        checkForVictory();
    }
};

const recalculateStyles = () => {
    for (let x = 0; x < 2; x++){
        if (boardStyles.value[x] === undefined) {
            boardStyles.value.push([]);
        }
        for (let i = 0; i < 100; i++){
            if (props.boardState[x][i] == 0) {
                boardStyles.value[x][i] = 'bg-blue-200 hover:bg-gray-300';
            } else if (props.boardState[x][i] == 1) {
                boardStyles.value[x][i] = 'bg-blue-800 hover:bg-gray-300';
            } else if (props.boardState[x][i] == 2) {
                if (x == 0 && isPlayer1.value) {
                    boardStyles.value[x][i] = 'bg-black';
                    shipsLeft.value = shipsLeft.value -1;
                } else if(x == 1 && !isPlayer1.value) {
                    boardStyles.value[x][i] = 'bg-black';
                    shipsLeft.value = shipsLeft.value -1;
                } else {
                    boardStyles.value[x][i] = 'bg-blue-200 hover:bg-gray-300';
                }
            } else if (props.boardState[x][i] == 3) {
                boardStyles.value[x][i] = 'bg-red-600 hover:bg-gray-300';
            }
        }
    }
};

const resetGame = () => {
    shipsLeft.value = 10;
    for (let x = 0; x < 2; x++){
        for (let i = 0; i < 100; i++){
            props.boardState[x][i] = 0;
        }
    }
    props.gameState.change(gameStates.PreparingB);
    recalculateStyles();
}

defineExpose({
    checkForVictory,
    mergeBoardState,
    resetGame
});

</script>