<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('booking_code')->unique();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->datetime('pickup_date')->nullable();
            $table->datetime('return_date')->nullable();
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned', 'expired'])->default('pending');
            $table->enum('payment_method', ['multicaixa', 'transfer', 'cash'])->nullable();
            $table->enum('payment_status', ['paid', 'pending'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['vehicle_id', 'start_date', 'end_date']);
            $table->index('booking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
