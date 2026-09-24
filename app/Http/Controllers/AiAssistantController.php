<?php

namespace App\Http\Controllers;

use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display the Full-Screen AI Logistics Copilot & Interactive Command Center
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $greetingData = $this->aiService->generateGreeting($user);
        $occasions = config('ai_assistant.occasions', []);
        $schedules = config('ai_assistant.schedules', []);

        return view('ai.index', compact('greetingData', 'occasions', 'schedules', 'user'));
    }

    /**
     * Fetch dynamic greeting with gestures and festival notices
     */
    public function greeting(Request $request): JsonResponse
    {
        $data = $this->aiService->generateGreeting($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Process AI Chat & Voice Query
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
        ]);

        $query = $request->input('message');
        $history = $request->input('history', []);
        $user = $request->user();

        $result = $this->aiService->ask($query, $user, $history);

        return response()->json($result);
    }

    /**
     * Get list of upcoming occasions, festivals, and operational logistics schedules
     */
    public function occasions(Request $request): JsonResponse
    {
        $occasions = config('ai_assistant.occasions', []);
        $schedules = config('ai_assistant.schedules', []);
        $nearest = $this->aiService->getNearestOccasion();

        return response()->json([
            'success' => true,
            'occasions' => $occasions,
            'schedules' => $schedules,
            'current_occasion' => $nearest,
        ]);
    }
}
