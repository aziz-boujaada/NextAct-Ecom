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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'stripe', 'bank_transfer']);
            $table->enum('status', ['pending', 'approved', 'paid', 'failed'])->default('pending');

            $table->string('external_reference')->nullable(); // stripe id etc

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
