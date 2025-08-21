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
        Schema::table('breweries', function (Blueprint $table) {
            $table->index('name');
            $table->index('city');
            $table->index('state_province');
            $table->index('country');
            $table->index('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('breweries', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['city']);
            $table->dropIndex(['state_province']);
            $table->dropIndex(['country']);
            $table->dropIndex(['postal_code']);
        });
    }
};
