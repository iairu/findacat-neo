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
        Schema::create('ems_colors', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('breed_id', 36)->nullable();
            $table->string('code')->index();
            $table->string('english_name')->nullable();
            $table->timestamps();

            $table->foreign('breed_id')->references('id')->on('breeds')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ems_colors');
    }
};
