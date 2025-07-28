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
        Schema::create('vehicles', function (Blueprint $table) {
           $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('plate')->unique();
            $table->integer('km')->default(0);
            $table->enum('type', ['SUV', 'Sedan', 'Pick-Up', 'Hatchback', 'Convertible']);
            $table->enum('status', ['available', 'in_use', 'maintenance'])->default('available');
            $table->decimal('price_per_day', 10, 2);
            $table->string('color');
            $table->integer('doors');
            $table->enum('fuel', ['gasoline', 'diesel', 'electric', 'hybrid']);
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index(['status', 'type']);
            $table->index('plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
