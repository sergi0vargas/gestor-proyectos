<?php

namespace App\Http\Controllers;

use App\Services\AITaskDecomposerService;
use Illuminate\Http\Request;
use Throwable;

class AIController extends Controller
{
    public function __construct(private AITaskDecomposerService $decomposer) {}

    public function decomposeTask(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        try {
            return response()->json(
                $this->decomposer->decompose($validated['title'], $validated['description'] ?? null)
            );
        } catch (Throwable $e) {
            return response()->json(
                ['error' => 'No se pudo contactar la IA. Inténtalo de nuevo.'],
                503
            );
        }
    }
}
