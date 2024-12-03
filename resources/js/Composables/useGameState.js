import { ref } from "vue";

export const gameStates = {
    P1Turn: 'P1Turn',
    P2Turn: 'P2Turn',
    Stalemate: 'Stalemate',
    Draw: 'Draw',
    P1Wins: 'P1Wins',
    P2Wins: 'P2Wins',
    PreparingP1: 'PreparingP1',
    PreparingP2: 'PreparingP2',
    PreparingB: 'PreparingB'
};

export const useGameState = (gameState) => {
    const state = ref(gameState ?? gameStates.P1Turn);

    return {
        change: (newState) => state.value = newState,
        current: () => state.value,
        isInProgress: () => [gameStates.P1Turn, gameStates.P2Turn].includes(state.value),
        hasEnded: () => [gameStates.P1Wins, gameStates.P2Wins, gameStates.Stalemate, gameStates.Draw].includes(state.value),
        gettingReady: () => [gameStates.PreparingP1, gameStates.PreparingP2, gameStates.PreparingB].includes(state.value),
    };
};