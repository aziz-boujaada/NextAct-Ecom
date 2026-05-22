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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_email');
            $table->string('company_phone', 50);
            $table->text('company_address');
            $table->string('company_website')->nullable();
            $table->string('company_tax_number', 255)->nullable();
            $table->string('company_logo')->nullable();
            $table->string('primary_color', 7)->nullable()->default('#3B82F6');
            $table->string('secondary_color', 7)->nullable()->default('#8B5CF6');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
