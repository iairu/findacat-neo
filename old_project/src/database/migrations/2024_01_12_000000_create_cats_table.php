<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->boolean('gender_id')->unsigned();
            $table->uuid('sire_id')->nullable();
            $table->uuid('dam_id')->nullable();
            // $table->uuid('parent_id')->nullable();
            $table->string('dob')->nullable(); //date
            $table->string('titles_before_name')->nullable();
            $table->string('titles_after_name')->nullable();
            $table->string('ems_color')->nullable();
            $table->string('breed')->nullable();
            $table->string('chip_number')->nullable();
            $table->string('genetic_tests')->nullable();
            $table->string('breeding_station')->nullable();
            $table->string('country_code')->nullable();
            $table->string('alternative_name')->nullable();
            $table->string('print_name_r1')->nullable();
            $table->string('print_name_r2')->nullable();
            $table->string('dod')->nullable();
            $table->string('original_reg_num')->nullable();
            $table->string('last_reg_num')->nullable();
            $table->string('reg_num_2')->nullable();
            $table->string('reg_num_3')->nullable();
            $table->string('notes')->nullable();
            $table->string('breeder')->nullable();
            $table->string('current_owner')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('country')->nullable();
            $table->string('ownership_notes')->nullable();
            $table->string('personal_info')->nullable();
            $table->string('genetic_tests_file')->nullable();
            $table->string('photo')->nullable();
            $table->string('vet_confirmation')->nullable();
            $table->string('doo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cats');
    }
}
