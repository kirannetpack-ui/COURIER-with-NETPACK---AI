<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\DomesticShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class ShippingDocumentController extends Controller
{
    /**
     * Display printable Commercial Invoice.
     */
    public function commercialInvoice($id)
    {
        $shipment = $this->findShipment($id);
        $this->authorizeDocumentAccess($shipment);

        $invoiceData = $this->prepareInvoiceData($shipment);
        $qrCode = $this->generateQRCode($shipment->tracking_number);

        return view('shipments.documents.invoice', compact('shipment', 'invoiceData', 'qrCode'));
    }

    /**
     * Display printable Packing List.
     */
    public function packingList($id)
    {
        $shipment = $this->findShipment($id);
        $this->authorizeDocumentAccess($shipment);

        $packingListData = $this->preparePackingListData($shipment);
        $qrCode = $this->generateQRCode($shipment->tracking_number);

        return view('shipments.documents.packing-list', compact('shipment', 'packingListData', 'qrCode'));
    }

    /**
     * View or download uploaded Seller Bill / Tax Voucher.
     */
    public function sellerBill($id)
    {
        $shipment = $this->findShipment($id);
        $this->authorizeDocumentAccess($shipment);

        $filePath = $shipment->seller_bill_file ?: $shipment->invoice_file;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'No uploaded seller bill or tax document found for this shipment.');
        }

        return response()->file(Storage::disk('public')->path($filePath));
    }

    /**
     * Find shipment by ID across standard and domestic models.
     */
    protected function findShipment($id)
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            $domestic = DomesticShipment::find($id);
            if ($domestic) {
                return $domestic;
            }
            abort(404, 'Shipment not found.');
        }

        return $shipment;
    }

    /**
     * Authorize user access to documents.
     */
    protected function authorizeDocumentAccess($shipment): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Admins, operators, managers always have access
        if ($user->isSystemAdmin() || $user->isSuperAdmin() || in_array($user->user_type, ['admin', 'super_admin', 'operator', 'agent'])) {
            return;
        }

        // Owner (customer, seller, rider) has access
        if ($shipment->customer_id == $user->id || $shipment->seller_id == $user->id) {
            return;
        }

        abort(403, 'Unauthorized access to shipping documents.');
    }

    /**
     * Prepare or construct standard Invoice Data.
     */
    protected function prepareInvoiceData($shipment): array
    {
        $saved = $shipment->invoice_data;
        if (!empty($saved) && is_array($saved) && !empty($saved['items'])) {
            return $saved;
        }

        // Fallback reconstruction for shipments without explicit invoice JSON
        $isInternational = ($shipment->shipment_type === 'international');
        $currency = $isInternational ? 'USD' : 'NPR';
        $desc = $shipment->description ?: ($isInternational ? 'General Commercial Merchandise' : 'Domestic Courier Goods');

        $unitValue = $isInternational ? 50.00 : 1500.00;
        $qty = 1;

        return [
            'invoice_number' => 'INV-' . date('Y', strtotime($shipment->created_at ?? now())) . '-' . substr($shipment->tracking_number, -6),
            'invoice_date' => date('Y-m-d', strtotime($shipment->created_at ?? now())),
            'currency' => $currency,
            'incoterm' => $isInternational ? 'DAP' : 'FOB',
            'export_reason' => $isInternational ? 'Commercial Sale / Export' : 'Domestic Sale',
            'shipper_pan_vat' => auth()->user()->pan_number ?? '100234567',
            'shipper_exim_code' => auth()->user()->exim_code ?? 'NP9800012345',
            'consignee_tax_id' => $shipment->receiver_tax_id ?? 'N/A',
            'items' => [
                [
                    'description' => $desc,
                    'hs_code' => $isInternational ? '6214.20.00' : '9803.00.00',
                    'origin_country' => 'Nepal',
                    'quantity' => $qty,
                    'uom' => 'PCS',
                    'unit_value' => $unitValue,
                    'total_value' => $unitValue * $qty,
                ],
            ],
            'subtotal' => $unitValue * $qty,
            'freight_charge' => (float) ($shipment->shipping_cost ?? 0),
            'insurance_charge' => (float) ($shipment->insurance_fee ?? 0),
            'grand_total' => ($unitValue * $qty) + (float) ($shipment->shipping_cost ?? 0),
            'declaration' => 'We declare that this commercial invoice shows the actual price of the goods described and that all particulars are true and correct.',
        ];
    }

    /**
     * Prepare or construct standard Packing List Data.
     */
    protected function preparePackingListData($shipment): array
    {
        $saved = $shipment->packing_list_data;
        if (!empty($saved) && is_array($saved) && !empty($saved['boxes'])) {
            return $saved;
        }

        // Check if boxes column has data
        $boxes = $shipment->boxes;
        if (!empty($boxes) && is_array($boxes)) {
            $totalGross = 0;
            $totalVol = 0;
            foreach ($boxes as $b) {
                $totalGross += (float) ($b['gross_weight'] ?? 0);
                $totalVol += (float) ($b['volumetric_weight'] ?? 0);
            }
            return [
                'total_boxes' => count($boxes),
                'total_gross_weight' => $totalGross ?: (float) $shipment->actual_weight,
                'total_volumetric_weight' => $totalVol ?: (float) ($shipment->volumetric_weight ?? 0),
                'boxes' => $boxes,
            ];
        }

        // Single box default fallback
        $grossWeight = (float) $shipment->actual_weight ?: 1.0;
        $l = (float) ($shipment->length ?: 30);
        $w = (float) ($shipment->width ?: 25);
        $h = (float) ($shipment->height ?: 20);
        $vol = ($l * $w * $h) / 5000;

        $desc = $shipment->description ?: 'Standard Consignment Package';

        return [
            'total_boxes' => 1,
            'total_gross_weight' => $grossWeight,
            'total_volumetric_weight' => $vol,
            'boxes' => [
                [
                    'box_number' => 1,
                    'length' => $l,
                    'width' => $w,
                    'height' => $h,
                    'gross_weight' => $grossWeight,
                    'volumetric_weight' => round($vol, 2),
                    'items' => [
                        [
                            'item_name' => $desc,
                            'hs_code' => '6214.20.00',
                            'quantity' => 1,
                            'uom' => 'PCS',
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * Generate QR Code data URI for printable view.
     */
    protected function generateQRCode(string $trackingNumber): string
    {
        try {
            $qrCode = new QrCode(
                data: route('tracking.show', $trackingNumber),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 140,
                margin: 5,
            );
            $dataUri = (new PngWriter())->write($qrCode)->getDataUri();

            return sprintf(
                '<img src="%s" alt="Scan to track %s" class="w-24 h-24 object-contain">',
                e($dataUri),
                e($trackingNumber)
            );
        } catch (\Throwable $e) {
            return '';
        }
    }
}
