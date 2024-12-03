<?php

namespace App\Http\Controllers;

use App\Events\GameJoined;
use App\Events\GameLeft;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia('Dashboard', [
            'games' => Game::with('playerOne')
                ->whereNull('player_two_id')
                ->where('player_one_id', '!=', $request->user()->id)
                ->oldest()
                ->simplePaginate(100),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $game_type = $request->input('type', 'tictactoe');
        $game = Game::create(['player_one_id' => $request->user()->id, 'game_type' => $game_type]);

        return to_route('games.show', $game);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        $game->load(['playerOne', 'playerTwo']);

        return Inertia('Games/show', compact('game'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Game $game)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        if ($game->game_type == 'tictactoe') {
            $data = $request->validate([
                'board_state' => ['required', 'array', 'size:9'],
                'board_state.*' => ['integer', 'between:-1,1'],
                'game_state' => ['required', 'string'],
            ]);
        } elseif ($game->game_type == 'warship') {
            $data = $request->validate([
                'board_state' => ['required', 'array', 'size:2'],
                'board_state.*' => ['array', 'size:100'],
                'board_state.*.*' => ['integer', 'between:0,3'],
                'game_state' => ['required', 'string'],
            ]);
        } elseif ($game->game_type == 'chess') {
            $data = $request->validate([
                'board_state' => ['required', 'array', 'size:8'],
                'board_state.*' => ['required', 'size:8'],
                'board_state.*.*' => ['alpha_num'],
                'game_state' => ['required', 'string'],
            ]);
        }
        $game->update($data);

        return to_route('games.show', $game);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        GameLeft::dispatch($game->id);

        // Remove the game
        $game->delete();

        return to_route('dashboard');
    }

    public function join(Request $request, Game $game)
    {
        Gate::authorize('join', $game);

        $game->update(['player_two_id' => $request->user()->id]);

        GameJoined::dispatch($game);

        return to_route('games.show', $game);
    }
}
