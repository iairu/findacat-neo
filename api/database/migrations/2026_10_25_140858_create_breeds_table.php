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
        // Ids are strings so rows imported from the legacy database keep their old ids.
        Schema::create('breeds', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            // Short breed code as used in registrations, e.g. PER. Category headings reuse this column.
            $table->string('code')->index();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breeds');
    }
};
