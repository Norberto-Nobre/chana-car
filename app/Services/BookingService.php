<?php

namespace App\Services;

use App\Contracts\BookingRepositoryInterface;
use App\Contracts\VehicleRepositoryInterface;
use App\Contracts\ContractServiceInterface;
use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private VehicleRepositoryInterface $vehicleRepository,
        private ContractServiceInterface $contractService
    ) {}

    public function createBooking(array $data): Booking
    {
        DB::beginTransaction();
        
        try {
            // Verificar disponibilidade do veículo
            if (!$this->bookingRepository->isVehicleAvailable(
                $data['vehicle_id'],
                Carbon::parse($data['start_date']),
                Carbon::parse($data['end_date'])
            )) {
                throw new \Exception('Veículo não disponível para o período selecionado');
            }

            // Calcular total
            $vehicle = $this->vehicleRepository->find($data['vehicle_id']);
            $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
            $data['total'] = $days * $vehicle->price_per_day;

            $booking = $this->bookingRepository->create($data);
            
            DB::commit();
            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveBooking(int $bookingId): bool
    {
        DB::beginTransaction();
        
        try {
            $booking = $this->bookingRepository->find($bookingId);
            
            if (!$booking || !$booking->isPending()) {
                throw new \Exception('Reserva não encontrada ou não está pendente');
            }

            // Marcar veículo como em uso
            $this->vehicleRepository->updateStatus($booking->vehicle_id, Vehicle::STATUS_IN_USE);
            
            // Atualizar status da reserva
            $this->bookingRepository->update($bookingId, ['status' => Booking::STATUS_APPROVED]);
            
            // Gerar contrato
            $this->contractService->generateContract($booking);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function returnVehicle(int $bookingId): bool
    {
        DB::beginTransaction();
        
        try {
            $booking = $this->bookingRepository->find($bookingId);
            
            if (!$booking || !$booking->isApproved()) {
                throw new \Exception('Reserva não encontrada ou não está aprovada');
            }

            // Marcar veículo como disponível
            $this->vehicleRepository->updateStatus($booking->vehicle_id, Vehicle::STATUS_AVAILABLE);
            
            // Atualizar status da reserva
            $this->bookingRepository->update($bookingId, [
                'status' => Booking::STATUS_RETURNED,
                'return_date' => Carbon::now()
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processOverdueBookings(): void
    {
        $overdueBookings = $this->bookingRepository->getOverdueBookings();
        
        foreach ($overdueBookings as $booking) {
            $booking->markAsExpired();
        }
    }
}