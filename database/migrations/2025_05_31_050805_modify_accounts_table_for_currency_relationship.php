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
        // First, add the currency_id column (nullable initially)
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->after('balance')->constrained();
        });

        // Note: After running this migration, you should:
        // 1. Run the CurrencySeeder to populate the currencies table
        // 2. Run a script to update the currency_id based on the currency string
        // 3. Run another migration to make currency_id required and remove the currency string column
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });
    }
};
