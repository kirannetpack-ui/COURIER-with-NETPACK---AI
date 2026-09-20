<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShipmentIssueController extends Controller
{
    /**
     * Submit an issue / situation report for a shipment.
     */
    public function store(Request $request, $trackingNumberOrId)
    {
        $shipment = Shipment::where('tracking_number', $trackingNumberOrId)
            ->orWhere('id', $trackingNumberOrId)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'issue_type' => 'required|string|in:damage,delay,lost_item,billing_discrepancy,customs_hold,return_request,rider_conduct,general_inquiry,other',
            'situation_description' => 'required|string|min:8|max:5000',
            'title' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:150',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'claimed_amount' => 'nullable|numeric|min:0',
            'claimed_currency' => 'nullable|string|max:10',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Please provide the required details of the issue.'
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = $file->store('shipment_issues', 'public');
            }

            $user = Auth::user();
            $issue = new ShipmentIssue();
            $issue->issue_number = ShipmentIssue::generateIssueNumber();
            $issue->shipment_id = $shipment->id;
            $issue->customer_id = $user ? $user->id : ($shipment->customer_id ?? null);
            $issue->tracking_number = $shipment->tracking_number;
            $issue->issue_type = $request->input('issue_type');
            $issue->title = $request->input('title') ?: ('Issue reported for ' . $shipment->tracking_number);
            $issue->situation_description = $request->input('situation_description');
            $issue->contact_name = $request->input('contact_name') ?: ($user->name ?? $shipment->receiver_name ?? 'Client');
            $issue->contact_email = $request->input('contact_email') ?: ($user->email ?? null);
            $issue->contact_phone = $request->input('contact_phone') ?: ($user->phone ?? $shipment->receiver_phone ?? null);
            $issue->claimed_amount = $request->filled('claimed_amount') ? (float) $request->input('claimed_amount') : null;
            $issue->claimed_currency = $request->input('claimed_currency', 'NPR');
            $issue->attachment_file = $attachmentPath;
            $issue->status = 'open';
            $issue->save();

            // Update Shipment telemetry flags
            $shipment->has_complaint = true;
            $shipment->complaint_count = (int) ($shipment->complaint_count ?? 0) + 1;

            if ($request->input('issue_type') === 'damage') {
                $shipment->is_damaged = true;
                $shipment->damage_description = $request->input('situation_description');
                $shipment->damage_reported_at = now();
            } elseif ($request->input('issue_type') === 'delay') {
                $shipment->is_delayed = true;
            } elseif ($request->input('issue_type') === 'return_request') {
                $shipment->is_returned = true;
                $shipment->return_reason = $request->input('situation_description');
                $shipment->returned_at = now();
            }

            // Append to shipment tracking timeline
            $timeline = $shipment->tracking_timeline ?? [];
            $typeLabel = ucwords(str_replace('_', ' ', $issue->issue_type));
            $timeline[] = [
                'status' => 'issue_reported',
                'note' => "Issue Logged [{$issue->issue_number}]: {$typeLabel} - " . Str::limit($request->situation_description, 80),
                'location' => 'Support Telemetry Desk',
                'timestamp' => now()->toDateTimeString(),
            ];
            $shipment->tracking_timeline = $timeline;
            $shipment->save();

            // Sync with SupportTicket if user is authenticated
            if ($user) {
                try {
                    SupportTicket::create([
                        'user_id' => $user->id,
                        'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
                        'subject' => "[Shipment {$shipment->tracking_number}] {$typeLabel} Report",
                        'description' => $request->situation_description . ($attachmentPath ? "\nProof attached: " . asset('storage/' . $attachmentPath) : ""),
                        'category' => 'delivery',
                        'priority' => in_array($issue->issue_type, ['damage', 'lost_item']) ? 'high' : 'medium',
                        'status' => 'open',
                        'metadata' => [
                            'shipment_id' => $shipment->id,
                            'tracking_number' => $shipment->tracking_number,
                            'issue_number' => $issue->issue_number,
                            'issue_type' => $issue->issue_type,
                        ]
                    ]);
                } catch (\Throwable $e) {
                    // Non-blocking for support ticket creation
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your issue details have been successfully recorded under ticket #' . $issue->issue_number . '. Our operations team will investigate immediately.',
                    'issue' => $issue,
                    'issue_number' => $issue->issue_number,
                ]);
            }

            return redirect()->back()->with('success', 'Your issue report #' . $issue->issue_number . ' has been recorded. Our team is reviewing the situation.');

        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not save issue details: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error recording issue: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Get list of issues for a shipment.
     */
    public function getShipmentIssues($trackingNumberOrId)
    {
        $shipment = Shipment::where('tracking_number', $trackingNumberOrId)
            ->orWhere('id', $trackingNumberOrId)
            ->firstOrFail();

        $issues = ShipmentIssue::where('shipment_id', $shipment->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'issues' => $issues
        ]);
    }
}
