<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('client_id');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount', 'discount_amount']);
        });
    }
};