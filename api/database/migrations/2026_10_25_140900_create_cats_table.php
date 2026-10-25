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
        Schema::create('cats', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('name')->index();
            $table->string('sex', 6)->nullable(); // male | female
            $table->string('sire_id', 36)->nullable()->index();
            $table->string('dam_id', 36)->nullable()->index();
            $table->date('born_on')->nullable();
            $table->date('died_on')->nullable();
            $table->string('title_prefix')->nullable();
            $table->string('title_suffix')->nullable();
            $table->string('alt_name')->nullable();
            $table->string('print_name_1')->nullable();
            $table->string('print_name_2')->nullable();
            // Soft references: legacy data may name codes that no longer exist in breeds/ems_colors.
            $table->string('breed_code')->nullable()->index();
            $table->string('ems_code')->nullable();
            $table->string('chip_number')->nullable()->index();
            $table->text('genetic_tests')->nullable();
            $table->string('genetic_tests_file')->nullable();
            $table->string('reg_number')->nullable()->index();
            $table->string('reg_number_last')->nullable();
            $table->string('reg_number_2')->nullable();
            $table->string('reg_number_3')->nullable();
            $table->string('breeding_station')->nullable();
            $table->string('breeder')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->text('ownership_notes')->nullable();
            $table->text('personal_info')->nullable();
            $table->string('photo')->nullable();
            $table->string('vet_confirmation')->nullable();
            // Legacy values with no column of their own (doo, cemetery location).
            $table->json('metadata')->nullable();
            // The account that registered the cat; null for imported records.
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('moderation_status', 8)->default('approved')->index(); // approved | pending | rejected
            $table->timestamps();
        });

        // Added once the table exists: PostgreSQL needs the primary key in place before a self-reference.
        Schema::table('cats', function (Blueprint $table) {
            $table->foreign('sire_id')->references('id')->on('cats')->nullOnDelete();
            $table->foreign('dam_id')->references('id')->on('cats')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cats');
    }
};
