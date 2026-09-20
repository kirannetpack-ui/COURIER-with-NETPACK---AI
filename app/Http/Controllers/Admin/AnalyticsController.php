<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display the Operational Intelligence & Telemetry Analytics Console.
     */
    public function index(Request $request)
    {
        // Date range filtering
        $range = $request->input('range', 'all');
        $query = Shipment::query();

        if ($range === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($range === '7days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($range === '30days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($range === 'year') {
            $query->where('created_at', '>=', now()->subYear());
        }

        $totalShipments = (clone $query)->count();
        $totalSold = (clone $query)->sum(DB::raw('COALESCE(price_sold, total_amount)'));
        $totalCost = (clone $query)->sum('cost_amount');
        $totalMargin = (clone $query)->sum('gross_margin');
        $marginPercentage = $totalSold > 0 ? round(($totalMargin / $totalSold) * 100, 1) : 0;

        // Service & Operational SLA Reliability
        $delayedCount = (clone $query)->where('is_delayed', true)->count();
        $damagedCount = (clone $query)->where('is_damaged', true)->count();
        $returnedCount = (clone $query)->where('is_returned', true)->count();
        $complaintCount = (clone $query)->where('has_complaint', true)->count();
        $deliveredCount = (clone $query)->where('status', 'delivered')->count();

        $delayRate = $totalShipments > 0 ? round(($delayedCount / $totalShipments) * 100, 1) : 0;
        $damageRate = $totalShipments > 0 ? round(($damagedCount / $totalShipments) * 100, 1) : 0;
        $returnRate = $totalShipments > 0 ? round(($returnedCount / $totalShipments) * 100, 1) : 0;
        $complaintRate = $totalShipments > 0 ? round(($complaintCount / $totalShipments) * 100, 1) : 0;

        $avgTransitDays = (clone $query)->whereNotNull('transit_time_days')->avg('transit_time_days') ?? 0;

        // Customer Acquisition breakdown
        $acquisitionBreakdown = (clone $query)
            ->select('acquisition_source', DB::raw('count(*) as total'))
            ->groupBy('acquisition_source')
            ->pluck('total', 'acquisition_source')
            ->toArray();

        // Customer Retention: Repeat vs New
        $repeatCount = (clone $query)->where('is_repeat_customer', true)->count();
        $newCustomerCount = max(0, $totalShipments - $repeatCount);
        $repeatRate = $totalShipments > 0 ? round(($repeatCount / $totalShipments) * 100, 1) : 0;

        // Top Destination Countries
        $topDestinations = (clone $query)
            ->select(DB::raw('COALESCE(destination_country, receiver_country) as country'), DB::raw('count(*) as total'), DB::raw('sum(COALESCE(price_sold, total_amount)) as revenue'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Vendor / Partner distribution
        $vendorBreakdown = (clone $query)
            ->select('vendor_name', DB::raw('count(*) as count'), DB::raw('sum(cost_amount) as total_cost'))
            ->whereNotNull('vendor_name')
            ->groupBy('vendor_name')
            ->orderByDesc('count')
            ->get();

        // Shipment Type breakdown
        $typeBreakdown = (clone $query)
            ->select('shipment_type', DB::raw('count(*) as count'), DB::raw('sum(actual_weight) as total_weight'))
            ->groupBy('shipment_type')
            ->pluck('count', 'shipment_type')
            ->toArray();

        // Recent issues logged
        $recentIssues = ShipmentIssue::with(['shipment', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Recent shipments with telemetry
        $recentShipments = (clone $query)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics', compact(
            'totalShipments', 'totalSold', 'totalCost', 'totalMargin', 'marginPercentage',
            'delayedCount', 'damagedCount', 'returnedCount', 'complaintCount', 'deliveredCount',
            'delayRate', 'damageRate', 'returnRate', 'complaintRate', 'avgTransitDays',
            'acquisitionBreakdown', 'repeatCount', 'newCustomerCount', 'repeatRate',
            'topDestinations', 'vendorBreakdown', 'typeBreakdown', 'recentIssues',
            'recentShipments', 'range'
        ));
    }
}
