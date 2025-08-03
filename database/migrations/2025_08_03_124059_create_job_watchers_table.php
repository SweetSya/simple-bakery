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
        Schema::create('job_watchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('job_id')->unique();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('job_type');
            $table->json('job_data')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_watchers');
    }
};
