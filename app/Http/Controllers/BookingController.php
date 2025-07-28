<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings (Admin).
     */
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle.category', 'user']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'message' => 'Bookings retrieved successfully'
        ]);
    }

    /**
     * Store a newly created booking (Client).
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'pickup_location' => 'required|string|max:255',
            'return_location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Verificar se o veículo está disponível
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        
        if ($vehicle->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle is not available'
            ], 400);
        }

        // Verificar conflitos de reserva
        $hasConflict = Booking::where('vehicle_id', $request->vehicle_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($subQuery) use ($request) {
                          $subQuery->where('start_date', '<=', $request->start_date)
                                   ->where('end_date', '>=', $request->end_date);
                      });
            })->exists();

        if ($hasConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle is not available for the selected dates'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Calcular total
            $days = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
            $totalAmount = $vehicle->daily_rate * $days;

            // Criar reserva
            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'user_id' => Auth::id(),
                'vehicle_id' => $request->vehicle_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pickup_location' => $request->pickup_location,
                'return_location' => $request->return_location,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $request->notes
            ]);

            // Atualizar status do veículo
            $vehicle->update(['status' => 'booked']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $booking->load(['vehicle.category']),
                'message' => 'Booking created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show($id)
    {
        $booking = Booking::with(['vehicle.category', 'user'])->findOrFail($id);

        // Verificar se o usuário pode ver esta reserva
        if (Auth::user()->role === 'client' && $booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking,
            'message' => 'Booking retrieved successfully'
        ]);
    }

    /**
     * Update the specified booking (Admin only).
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Só permite atualizar se status for 'pending' ou 'approved'
        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be updated'
            ], 400);
        }

        $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'pickup_location' => 'sometimes|string|max:255',
            'return_location' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:pending,approved,active,completed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $booking->status;

            // Atualizar reserva
            $booking->update($request->only([
                'start_date', 'end_date', 'pickup_location', 
                'return_location', 'status', 'notes'
            ]));

            // Gerenciar status do veículo
            if ($request->filled('status')) {
                $vehicle = $booking->vehicle;
                
                if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
                    $vehicle->update(['status' => 'available']);
                } elseif ($request->status === 'completed' && $oldStatus !== 'completed') {
                    $vehicle->update(['status' => 'available']);
                } elseif (in_array($request->status, ['approved', 'active']) && $oldStatus === 'cancelled') {
                    $vehicle->update(['status' => 'booked']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $booking->fresh(['vehicle.category', 'user']),
                'message' => 'Booking updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified booking (Admin only).
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);

        // Só permite deletar se status for 'cancelled'
        if ($booking->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Only cancelled bookings can be deleted'
            ], 400);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully'
        ]);
    }

    /**
     * Get user's bookings (Client).
     */
    public function myBookings(Request $request)
    {
        $query = Booking::with(['vehicle.category'])
                       ->where('user_id', Auth::id());

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'message' => 'My bookings retrieved successfully'
        ]);
    }

    /**
     * Approve a booking (Admin).
     */
    public function approve($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be approved'
            ], 400);
        }

        $booking->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $booking->fresh(['vehicle.category', 'user']),
            'message' => 'Booking approved successfully'
        ]);
    }

    /**
     * Reject a booking (Admin).
     */
    public function reject(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be rejected'
            ], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $booking->update([
                'status' => 'cancelled',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
                'rejected_by' => Auth::id()
            ]);

            // Liberar o veículo
            $booking->vehicle->update(['status' => 'available']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $booking->fresh(['vehicle.category', 'user']),
                'message' => 'Booking rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return a vehicle (Admin).
     */
    public function return(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active bookings can be returned'
            ], 400);
        }

        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
            'additional_charges' => 'nullable|numeric|min:0',
            'fuel_level' => 'nullable|in:empty,quarter,half,three_quarters,full',
            'condition' => 'nullable|in:excellent,good,fair,poor'
        ]);

        DB::beginTransaction();

        try {
            $booking->update([
                'status' => 'completed',
                'actual_return_date' => now(),
                'return_notes' => $request->return_notes,
                'additional_charges' => $request->additional_charges ?? 0,
                'fuel_level_return' => $request->fuel_level,
                'condition_return' => $request->condition,
                'returned_by' => Auth::id()
            ]);

            // Liberar o veículo
            $booking->vehicle->update(['status' => 'available']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $booking->fresh(['vehicle.category', 'user']),
                'message' => 'Vehicle returned successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error returning vehicle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique booking number.
     */
    private function generateBookingNumber()
    {
        $prefix = 'BK';
        $date = Carbon::now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $bookingNumber = $prefix . $date . $random;
        
        // Verificar se já existe
        while (Booking::where('booking_number', $bookingNumber)->exists()) {
            $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $bookingNumber = $prefix . $date . $random;
        }
        
        return $bookingNumber;
    }
}