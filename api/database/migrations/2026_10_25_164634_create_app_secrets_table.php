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
        // Generated secrets (APP_KEY, setup token) for hosts without a writable .env, such as Vercel.
        Schema::create('app_secrets', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->text('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_secrets');
    }
};
