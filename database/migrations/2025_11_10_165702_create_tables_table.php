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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();  // Table number
            $table->integer('capacity');         // How many people can sit
            $table->boolean('is_available')->default(true);
            $table->text('description')->nullable();  // Optional description/location
            $table->timestamps();
            $table->softDeletes();  // Add soft deletes for better record keeping
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
