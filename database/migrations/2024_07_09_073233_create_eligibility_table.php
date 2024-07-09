<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('eligibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarship')->onDelete('cascade');
            $table->string('criteria');
            $table->float('minimum_gpa')->nullable();
            $table->string('country')->nullable();
            $table->integer('experience')->nullable();
            $table->boolean('english_proficiency')->nullable();
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
        Schema::dropIfExists('eligibility');
    }
};
