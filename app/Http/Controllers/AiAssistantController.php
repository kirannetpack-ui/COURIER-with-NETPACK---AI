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

    /**
     * Parse and structure spoken voice text for form field auto-typing
     */
    public function voiceAutofillParse(Request $request): JsonResponse
    {
        $request->validate([
            'step' => 'required|string',
            'spoken_text' => 'required|string|max:1000',
            'mode' => 'nullable|string',
        ]);

        $step = $request->input('step');
        $text = $request->input('spoken_text');
        $mode = $request->input('mode', 'domestic');

        $result = $this->aiService->parseVoiceFormField($step, $text, $mode);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Fetch active operational intelligence exceptions and Win-Win-Win suggestions for Admins
     */
    public function adminOperationalIntelligence(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin() && !$user->isDomesticAdmin() && !$user->isInternationalAdmin() && !in_array($user->user_type ?? '', ['admin', 'staff', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to operational intelligence.',
            ], 403);
        }

        $issues = $this->aiService->getAdminOperationalIssuesAndWinWinSolutions();
        $clientInfo = $this->aiService->resolveClientName($user, 'operational status');
        $report = $this->aiService->generateAdminOperationalIntelligenceReport($clientInfo, 'status');

        return response()->json([
            'success' => true,
            'issues' => $issues,
            'report' => $report,
            'critical_count' => count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'critical')),
            'warning_count' => count(array_filter($issues, fn($i) => ($i['severity'] ?? '') === 'warning')),
        ]);
    }

    /**
     * Execute 1-Click Win-Win-Win Operational Action from Admin Panel
     */
    public function adminResolveIssueAction(Request $request): JsonResponse
    {
        $request->validate([
            'issue_id' => 'required|string',
            'action_type' => 'required|string',
            'shipment_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $result = $this->aiService->executeAdminWinWinAction(
            $request->input('issue_id'),
            $request->input('action_type'),
            $request->input('shipment_id'),
            $request->input('notes')
        );

        return response()->json($result);
    }
}

