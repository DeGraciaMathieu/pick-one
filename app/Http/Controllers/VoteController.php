<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index()
    {
        $hasVoted = session()->has('has_voted');
        $votedFor = session('voted_for');

        return view('vote', compact('hasVoted', 'votedFor'));
    }

    public function store(Request $request): JsonResponse
    {
        if (session()->has('has_voted')) {
            return response()->json(['error' => 'Vous avez déjà voté.'], 422);
        }

        $validated = $request->validate([
            'choice' => 'required|in:a,b',
        ]);

        Vote::create([
            'choice' => $validated['choice'],
            'voter_ip' => $request->ip(),
        ]);

        session(['has_voted' => true, 'voted_for' => $validated['choice']]);

        return response()->json(['success' => true]);
    }

    public function results(): JsonResponse
    {
        $a = Vote::where('choice', 'a')->count();
        $b = Vote::where('choice', 'b')->count();
        $total = $a + $b;

        return response()->json([
            'a' => $a,
            'b' => $b,
            'total' => $total,
            'percent_a' => $total > 0 ? round(($a / $total) * 100, 1) : 0,
            'percent_b' => $total > 0 ? round(($b / $total) * 100, 1) : 0,
        ]);
    }
}
