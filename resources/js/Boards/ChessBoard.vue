<template>
    <div class="flex">
        <div>
            <button as="button" @click="callForSatlemate()" class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                Call Stalmate
            </button>
        </div>
        <div class="w-full" :class="{'rotate-180' : !isPlayer1 }">
            <menu v-for="(row,indexRow) in props.boardState" class="grid grid-cols-8 w-0 min-w-fit mx-auto border border-black">
                <li v-for="(square,indexSquare) in row" class="relative size-28 flex place-items-center" :class="{'bg-white': (indexRow + indexSquare) % 2 == 0, 'bg-gray-700': (indexRow + indexSquare) % 2 == 1}">
                    <ChessPiece @click="selectPiece(indexRow,indexSquare)" :rotate="!isPlayer1" v-if="square != 0" :alias="square"></ChessPiece>
                    <ClickableTile @click="movePiece(indexRow,indexSquare)" v-show="highlitedTiles.find((element) => element[0] == indexRow && element[1] == indexSquare)"></ClickableTile>
                    <SelectedTile v-show="selectedPiece[0] == indexRow && selectedPiece[1] == indexSquare"></SelectedTile>
                </li>
            </menu>
        </div>
    </div>
    <Modal :show="showStalemate">
        <div v-if="!waitingStalemateResponse" class="p-6">
            <div class="text-4xl font-bold text-center my-12 font-mono uppercase">
                <p>Rival is calling for Stalmate</p>
                <p> do you accept?</p>
            </div>

            <div class="mt-6 flex gap-2 justify-center">
                <button as="button" @click="answerStalemate(false)" class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                    Deny
                </button>
                <button as="button" @click="answerStalemate(true)" class="mt-2 mb-8 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                    Accept
                </button>
            </div>
        </div>
        <div v-else="!waitingStalemateResponse" class="p-6">
            <div class="text-4xl font-bold text-center my-12 font-mono uppercase">
                <div> Waiting for rivals response</div>

                <div class="mt-8">
                    <svg aria-hidden="true" class="w-8 h-8 mx-auto text-gray-400 animate-spin dark:text-gray-400 fill-blue-800" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                    <span class="sr-only">Loading...</span>
                </div>


            </div>
        </div>
    </Modal>
</template>


<script setup>
import { gameStates } from '@/Composables/useGameState';
import { computed, onMounted, ref } from 'vue';
import ChessPiece from './Components/ChessPiece.vue';
import ClickableTile from './Components/ClickableTile.vue';
import SelectedTile from './Components/SelectedTile.vue';
import Modal from "@/Components/Modal.vue";
const props = defineProps(['gameState','boardState','game','page','channel']);
const emit = defineEmits(['updateStatus']);
const highlitedTiles = ref([]);
const selectedPiece = ref([undefined,undefined]);
const showStalemate = ref(false);
const waitingStalemateResponse = ref(false);
let piece = {alias: undefined, position: undefined};

onMounted(() => {
    if (props.boardState.length === 0) {
        for (let x = 0; x < 8; x++){
            props.boardState.push([])
            for (let i = 0; i < 8; i++){
                props.boardState[x].push(0);
            }
        }
        props.boardState[0] = ['br','bh','bb','bq','bk','bb','bh','br'];
        props.boardState[1] = ['bp','bp','bp','bp','bp','bp','bp','bp'];

        props.boardState[6] = ['wp','wp','wp','wp','wp','wp','wp','wp'];
        props.boardState[7] = ['wr','wh','wb','wq','wk','wb','wh','wr'];
    }
    props.channel.listenForWhisper('CallingForStalemate', () => {
        showStalemate.value = true;
        waitingStalemateResponse.value = false;
    });
})

const yourTurn = computed(() => {
    if (props.game.player_one_id === props.page.props.auth.user.id && props.gameState.current() === gameStates.P1Turn) {
        return true;
    } else if (props.game.player_two_id === props.page.props.auth.user.id && props.gameState.current() === gameStates.P2Turn) {
        return true;
    }
    return false;
});

const isPlayer1 = computed(() => {
    return props.game.player_one_id == props.page.props.auth.user.id;
});

const selectPiece = (row,col) => {
    if (!yourTurn.value) {
        return;
    }

    if (isPlayer1.value && props.boardState[row][col].startsWith('b') || !isPlayer1.value && props.boardState[row][col].startsWith('w')) {
        return;
    }
    
    highlitedTiles.value = [];
    piece.alias = props.boardState[row][col];
    piece.position = { "row": row,"col": col};
    selectedPiece.value = [row,col];
    highlightAllowedMovements(piece);
};

const checkForVictory = () => {
    let whiteKing=false,blackKing=false;
    for (let x = 0; x < 8; x++) {
        for (let i = 0; i < 8; i++) {
            if( props.boardState[x][i] === 'bk') {
                blackKing = true;
            } else if( props.boardState[x][i] === 'wk')  {
                whiteKing = true;
            }
        }
    }
    if (!whiteKing) {
        props.gameState.change(gameStates.P2Wins);
        return;
    }

    if (!blackKing) {
        props.gameState.change(gameStates.P1Wins);
        return;
    }
};


const movePiece = (row,col) => {
    props.boardState[row][col] = piece.alias;
    props.boardState[row][col] = piece.alias;
    props.boardState[piece.position.row][piece.position.col] = 0;
    highlitedTiles.value = [];
    selectedPiece.value = [];

    changeTurn();
    checkForVictory();
    updateStatus();
};

const mergeBoardState = (newState) => {
    for (let x = 0; x < 8; x++) {
        for (let i = 0; i < 8; i++) {
            props.boardState[x][i] = newState[x][i];
        }
    }
}

const resetGame = () => {
    for (let x = 0; x < 8; x++){
        for (let i = 0; i < 8; i++){
            props.boardState[x][i] = 0;
        }
    }
    props.boardState[0] = ['br','bh','bb','bq','bk','bb','bh','br'];
    props.boardState[1] = ['bp','bp','bp','bp','bp','bp','bp','bp'];

    props.boardState[6] = ['wp','wp','wp','wp','wp','wp','wp','wp'];
    props.boardState[7] = ['wr','wh','wb','wq','wk','wb','wh','wr'];
    props.gameState.change(gameStates.P1Turn);
}

const callForSatlemate = () => {
    showStalemate.value = true;
    waitingStalemateResponse.value = true;
    props.channel.whisper('CallingForStalemate', {});
    props.channel.listenForWhisper('AnswerStalemate', (response) => {
        if(response) {
            props.gameState.change(gameStates.Stalemate);
        }
        showStalemate.value = false;
    });
};

const answerStalemate = (response) => {
    props.channel.whisper('AnswerStalemate', response);
    if(response) {
        props.gameState.change(gameStates.Stalemate);
    }
    showStalemate.value = false;
};

const changeTurn = () => {
    if (isPlayer1.value) {
        props.gameState.change(gameStates.P2Turn);
    } else {
        props.gameState.change(gameStates.P1Turn);
    }
};

defineExpose({
    checkForVictory,
    mergeBoardState,
    resetGame
});

const highlightAllowedMovements = (piece) => {
    highlitedTiles.value =  getPossibleMoves(piece);
}

const updateStatus = () => {
    emit('updateStatus');
}

const getPossibleMoves = (piece) => {
    let possibleMoves = [];
    // Black Pawn
    if (piece.alias == 'bp') {
        if (piece.position.row == 1 && props.boardState[piece.position.row + 2][piece.position.col] == 0) {
            possibleMoves.push([piece.position.row + 2,piece.position.col])
        }
        if (props.boardState[piece.position.row + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col] == 0
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col])
        }
        if (props.boardState[piece.position.row + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col + 1] != 0 &&
            props.boardState[piece.position.row + 1][piece.position.col + 1].startsWith('w')
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col + 1])
        }

        if (props.boardState[piece.position.row + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col + 1] != 0 &&
            props.boardState[piece.position.row + 1][piece.position.col + 1].startsWith('w')
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col - 1])
        }
        return possibleMoves;
    }

    //White Pawn
    if (piece.alias == 'wp') {
        if (piece.position.row == 6 &&
            props.boardState[piece.position.row - 2][piece.position.col] == 0
        ) {
            possibleMoves.push([piece.position.row - 2,piece.position.col]);
        }
        if (props.boardState[piece.position.row - 1] &&
            props.boardState[piece.position.row - 1][piece.position.col] == 0
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col]);
        }
        if (props.boardState[piece.position.row - 1] &&
            props.boardState[piece.position.row - 1][piece.position.col + 1] &&
            props.boardState[piece.position.row - 1][piece.position.col + 1] != 0 &&
            props.boardState[piece.position.row - 1][piece.position.col + 1].startsWith('b')
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col + 1]);
        }
        if (props.boardState[piece.position.row - 1] &&
            props.boardState[piece.position.row - 1][piece.position.col - 1] &&
            props.boardState[piece.position.row - 1][piece.position.col - 1] != 0 &&
            props.boardState[piece.position.row - 1][piece.position.col - 1].startsWith('b')
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col - 1]);
        }
        return possibleMoves;
    }

    // Horses (Knight)
    let pieceColor = piece.alias[0];
    let pieceType = piece.alias[1];
    let enemyColor = pieceColor == 'b' ? 'w' : 'b';
    if (pieceType === 'h') {
        if (props.boardState[piece.position.row + 2] &&
            (props.boardState[piece.position.row + 2][piece.position.col + 1] == 0 ||
            props.boardState[piece.position.row + 2][piece.position.col + 1] &&
            !props.boardState[piece.position.row + 2][piece.position.col + 1].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row +2,piece.position.col + 1]);
        }
        
        if (props.boardState[piece.position.row + 2] &&
            (props.boardState[piece.position.row + 2][piece.position.col - 1] == 0 ||
            props.boardState[piece.position.row + 2][piece.position.col - 1] &&
            !props.boardState[piece.position.row + 2][piece.position.col - 1].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row +2,piece.position.col - 1]);
        }

        if (props.boardState[piece.position.row + 1] &&
            (props.boardState[piece.position.row + 1][piece.position.col - 2] == 0 ||
            props.boardState[piece.position.row + 1][piece.position.col - 2] &&
            !props.boardState[piece.position.row + 1][piece.position.col - 2].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col - 2]);
        }

        if (props.boardState[piece.position.row + 1] &&
            (props.boardState[piece.position.row + 1][piece.position.col + 2] == 0 ||
            props.boardState[piece.position.row + 1][piece.position.col + 2] &&
            !props.boardState[piece.position.row + 1][piece.position.col + 2].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col + 2]);
        }

        if (props.boardState[piece.position.row - 1] &&
            (props.boardState[piece.position.row - 1][piece.position.col + 2] == 0 ||
            props.boardState[piece.position.row - 1][piece.position.col + 2] &&
            !props.boardState[piece.position.row - 1][piece.position.col + 2].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col + 2]);
        }

        if (props.boardState[piece.position.row - 1] &&
            (props.boardState[piece.position.row - 1][piece.position.col - 2] == 0 ||
            props.boardState[piece.position.row - 1][piece.position.col - 2] &&
            !props.boardState[piece.position.row - 1][piece.position.col - 2].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col - 2]);
        }

        if (props.boardState[piece.position.row - 2] &&
            (props.boardState[piece.position.row - 2][piece.position.col - 1] == 0 ||
            props.boardState[piece.position.row - 2][piece.position.col - 1] &&
            !props.boardState[piece.position.row - 2][piece.position.col - 1].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row - 2,piece.position.col - 1]);
        }

        if (props.boardState[piece.position.row - 2] &&
            (props.boardState[piece.position.row - 2][piece.position.col + 1] == 0 ||
            props.boardState[piece.position.row - 2][piece.position.col + 1] &&
            !props.boardState[piece.position.row - 2][piece.position.col + 1].startsWith(pieceColor))
        ) {
            possibleMoves.push([piece.position.row - 2,piece.position.col + 1]);
        }

        return possibleMoves;
    }

    // Bishops
    if (pieceType === 'b') {
        let addition = 1;
        while ( props.boardState[piece.position.row + addition] && 
            (
                props.boardState[piece.position.row + addition][piece.position.col+addition] == 0 ||
                props.boardState[piece.position.row + addition][piece.position.col+addition] &&
                props.boardState[piece.position.row + addition][piece.position.col+addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + addition,piece.position.col + addition]);
            if (props.boardState[piece.position.row + addition][piece.position.col+addition] && props.boardState[piece.position.row + addition][piece.position.col+addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row + addition] && 
            (
                props.boardState[piece.position.row + addition][piece.position.col-addition] == 0 ||
                props.boardState[piece.position.row + addition][piece.position.col-addition] &&
                props.boardState[piece.position.row + addition][piece.position.col-addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + addition,piece.position.col - addition]);
            if(props.boardState[piece.position.row + addition][piece.position.col-addition] && props.boardState[piece.position.row + addition][piece.position.col-addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row - addition] && 
            (
                props.boardState[piece.position.row - addition][piece.position.col-addition] == 0 ||
                props.boardState[piece.position.row - addition][piece.position.col-addition] &&
                props.boardState[piece.position.row - addition][piece.position.col-addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - addition,piece.position.col - addition]);
            if(props.boardState[piece.position.row - addition][piece.position.col-addition] && props.boardState[piece.position.row - addition][piece.position.col-addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row - addition] && 
            (
                props.boardState[piece.position.row - addition][piece.position.col + addition] == 0 ||
                props.boardState[piece.position.row - addition][piece.position.col + addition] &&
                props.boardState[piece.position.row - addition][piece.position.col + addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - addition,piece.position.col + addition]);
            if(props.boardState[piece.position.row - addition][piece.position.col + addition] != 0 && props.boardState[piece.position.row - addition][piece.position.col + addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }
        return possibleMoves;
    }

    // Rooks
    if (pieceType === 'r') {
        let addition = 1;
        while ( props.boardState[piece.position.row + addition] && 
            (
                props.boardState[piece.position.row + addition][piece.position.col] == 0 ||
                props.boardState[piece.position.row + addition][piece.position.col] &&
                props.boardState[piece.position.row + addition][piece.position.col].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + addition,piece.position.col]);
            if (props.boardState[piece.position.row + addition][piece.position.col] && props.boardState[piece.position.row + addition][piece.position.col].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row - addition] && 
            (
                props.boardState[piece.position.row - addition][piece.position.col] == 0 ||
                props.boardState[piece.position.row - addition][piece.position.col] &&
                props.boardState[piece.position.row - addition][piece.position.col].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - addition,piece.position.col]);
            if (props.boardState[piece.position.row - addition][piece.position.col] && props.boardState[piece.position.row - addition][piece.position.col].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row] && 
            (
                props.boardState[piece.position.row][piece.position.col + addition] == 0 ||
                props.boardState[piece.position.row][piece.position.col + addition] &&
                props.boardState[piece.position.row][piece.position.col + addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row,piece.position.col + addition]);
            if(props.boardState[piece.position.row][piece.position.col + addition] && props.boardState[piece.position.row][piece.position.col + addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row] && 
            (
                props.boardState[piece.position.row][piece.position.col - addition] == 0 ||
                props.boardState[piece.position.row][piece.position.col - addition] &&
                props.boardState[piece.position.row][piece.position.col - addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row,piece.position.col - addition]);
            if(props.boardState[piece.position.row][piece.position.col - addition] && props.boardState[piece.position.row][piece.position.col - addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }
        
        return possibleMoves;
    }

    // Queens
    if (pieceType === 'q') {
        let addition = 1;
        while ( props.boardState[piece.position.row + addition] && 
            (
                props.boardState[piece.position.row + addition][piece.position.col+addition] == 0 ||
                props.boardState[piece.position.row + addition][piece.position.col+addition] &&
                props.boardState[piece.position.row + addition][piece.position.col+addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + addition,piece.position.col + addition]);
            if (props.boardState[piece.position.row + addition][piece.position.col+addition] && props.boardState[piece.position.row + addition][piece.position.col+addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row + addition] && 
            (
                props.boardState[piece.position.row + addition][piece.position.col-addition] == 0 ||
                props.boardState[piece.position.row + addition][piece.position.col-addition] &&
                props.boardState[piece.position.row + addition][piece.position.col-addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + addition,piece.position.col - addition]);
            if(props.boardState[piece.position.row + addition][piece.position.col-addition] && props.boardState[piece.position.row + addition][piece.position.col-addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row - addition] && 
            (
                props.boardState[piece.position.row - addition][piece.position.col-addition] == 0 ||
                props.boardState[piece.position.row - addition][piece.position.col-addition] &&
                props.boardState[piece.position.row - addition][piece.position.col-addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - addition,piece.position.col - addition]);
            if(props.boardState[piece.position.row - addition][piece.position.col-addition] && props.boardState[piece.position.row - addition][piece.position.col-addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row - addition] && 
            (
                props.boardState[piece.position.row - addition][piece.position.col + addition] == 0 ||
                props.boardState[piece.position.row - addition][piece.position.col + addition] &&
                props.boardState[piece.position.row - addition][piece.position.col + addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - addition,piece.position.col + addition]);
            if(props.boardState[piece.position.row - addition][piece.position.col + addition] != 0 && props.boardState[piece.position.row - addition][piece.position.col + addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row + addition] && 
            (
                props.boardState[piece.position.row + addition][piece.position.col] == 0 ||
                props.boardState[piece.position.row + addition][piece.position.col] &&
                props.boardState[piece.position.row + addition][piece.position.col].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + addition,piece.position.col]);
            if (props.boardState[piece.position.row + addition][piece.position.col] && props.boardState[piece.position.row + addition][piece.position.col].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row - addition] && 
            (
                props.boardState[piece.position.row - addition][piece.position.col] == 0 ||
                props.boardState[piece.position.row - addition][piece.position.col] &&
                props.boardState[piece.position.row - addition][piece.position.col].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - addition,piece.position.col]);
            if (props.boardState[piece.position.row - addition][piece.position.col] && props.boardState[piece.position.row - addition][piece.position.col].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row] && 
            (
                props.boardState[piece.position.row][piece.position.col + addition] == 0 ||
                props.boardState[piece.position.row][piece.position.col + addition] &&
                props.boardState[piece.position.row][piece.position.col + addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row,piece.position.col + addition]);
            if(props.boardState[piece.position.row][piece.position.col + addition] && props.boardState[piece.position.row][piece.position.col + addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }

        addition = 1;
        while ( props.boardState[piece.position.row] && 
            (
                props.boardState[piece.position.row][piece.position.col - addition] == 0 ||
                props.boardState[piece.position.row][piece.position.col - addition] &&
                props.boardState[piece.position.row][piece.position.col - addition].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row,piece.position.col - addition]);
            if(props.boardState[piece.position.row][piece.position.col - addition] && props.boardState[piece.position.row][piece.position.col - addition].startsWith(enemyColor)) {
                break;
            }
            addition++;
        }
        return possibleMoves;
    }

    

    // Kings
    if (pieceType == 'k') {
        if (props.boardState[piece.position.row - 1] &&
            (
                props.boardState[piece.position.row - 1][piece.position.col] == 0 || 
                props.boardState[piece.position.row - 1][piece.position.col] != 0 &&
                props.boardState[piece.position.row - 1][piece.position.col].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col]);
        }
        
        if (props.boardState[piece.position.row + 1] &&
            (
                props.boardState[piece.position.row + 1][piece.position.col] == 0 || 
                props.boardState[piece.position.row + 1][piece.position.col] != 0 &&
                props.boardState[piece.position.row + 1][piece.position.col].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col]);
        }
        if (props.boardState[piece.position.row + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col + 1] !== undefined &&
            (
                props.boardState[piece.position.row + 1][piece.position.col + 1] == 0 || 
                props.boardState[piece.position.row + 1][piece.position.col + 1] != 0 &&
                props.boardState[piece.position.row + 1][piece.position.col + 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col - 1]);
        }

        if (props.boardState[piece.position.row - 1] &&
            props.boardState[piece.position.row - 1][piece.position.col - 1] !== undefined &&
            (
                props.boardState[piece.position.row - 1][piece.position.col - 1] == 0 || 
                props.boardState[piece.position.row - 1][piece.position.col - 1] != 0 &&
                props.boardState[piece.position.row - 1][piece.position.col - 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col - 1]);
        }

        if (props.boardState[piece.position.row - 1] &&
            props.boardState[piece.position.row - 1][piece.position.col + 1] !== undefined &&
            (
                props.boardState[piece.position.row - 1][piece.position.col + 1] == 0 || 
                props.boardState[piece.position.row - 1][piece.position.col + 1] != 0 &&
                props.boardState[piece.position.row - 1][piece.position.col + 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row - 1,piece.position.col + 1]);
        }

        if (props.boardState[piece.position.row] &&
            props.boardState[piece.position.row][piece.position.col - 1] !== undefined &&
            (
                props.boardState[piece.position.row][piece.position.col - 1] == 0 || 
                props.boardState[piece.position.row][piece.position.col - 1] != 0 &&
                props.boardState[piece.position.row][piece.position.col - 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row,piece.position.col - 1]);
        }

        if (props.boardState[piece.position.row] &&
            props.boardState[piece.position.row][piece.position.col + 1] !== undefined &&
            (
                props.boardState[piece.position.row][piece.position.col + 1] == 0 || 
                props.boardState[piece.position.row][piece.position.col + 1] != 0 &&
                props.boardState[piece.position.row][piece.position.col + 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row,piece.position.col + 1]);
        }


        if (props.boardState[piece.position.row + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col + 1] !== undefined &&
            (
                props.boardState[piece.position.row + 1][piece.position.col + 1] == 0 || 
                props.boardState[piece.position.row + 1][piece.position.col + 1] != 0 &&
                props.boardState[piece.position.row + 1][piece.position.col + 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col + 1]);
        }

        if (props.boardState[piece.position.row + 1] &&
            props.boardState[piece.position.row + 1][piece.position.col - 1] !== undefined &&
            (
                props.boardState[piece.position.row + 1][piece.position.col - 1] == 0 || 
                props.boardState[piece.position.row + 1][piece.position.col - 1] != 0 &&
                props.boardState[piece.position.row + 1][piece.position.col - 1].startsWith(enemyColor)
            )
        ) {
            possibleMoves.push([piece.position.row + 1,piece.position.col - 1]);
        }


        return possibleMoves;
    }
};

</script>