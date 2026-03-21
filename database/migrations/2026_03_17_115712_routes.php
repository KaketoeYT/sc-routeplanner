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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commodity_id')->constrained();

            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('destination_id')->constrained('locations');

            $table->integer('scu_origin');
            $table->integer('scu_destination');

            $table->decimal('price_origin', 10, 2);
            $table->decimal('price_destination', 10, 2);

            $table->string('container_sizes_origin')->nullable();
            $table->string('container_sizes_destination')->nullable();

            $table->integer('distance')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
