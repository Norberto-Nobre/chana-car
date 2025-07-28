<?php

namespace App\Services;

use App\Contracts\ContractServiceInterface;
use App\Models\Booking;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ContractService implements ContractServiceInterface
{
    public function generateContract(Booking $booking): Contract
    {
        $booking->load(['user', 'vehicle']);
        
        $data = [
            'booking' => $booking,
            'user' => $booking->user,
            'vehicle' => $booking->vehicle,
            'generated_at' => Carbon::now(),
        ];

        $pdf = Pdf::loadView('contracts.template', $data);
        
        $filename = "contract_booking_{$booking->id}.pdf";
        $path = "contracts/{$filename}";
        
        Storage::put($path, $pdf->output());
        
        return Contract::create([
            'booking_id' => $booking->id,
            'pdf_path' => $path,
            'generated_at' => Carbon::now(),
        ]);
    }

    public function getContractPath(Contract $contract): string
    {
        return Storage::path($contract->pdf_path);
    }
}