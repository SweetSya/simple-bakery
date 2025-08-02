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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_reference')->unique()->nullable();
            $table->uuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('server_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transaction_date')->useCurrent();
            $table->decimal('total_amount', 10, 2);
            $table->json('payment_details')->nullable();
            $table->enum('status', ['pending', 'payment', 'paid', 'delivered'])->default('pending');
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
