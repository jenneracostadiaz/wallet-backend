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
        // First, update currency_id for all accounts based on their currency string
        $this->updateCurrencyIds();

        // Then make currency_id required
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable(false)->change();
        });

        // Finally, remove the currency string column
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the currency string column
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('balance');
        });

        // Make currency_id nullable again
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->change();
        });

        // Update currency string based on currency_id
        $this->updateCurrencyStrings();
    }

    /**
     * Update currency_id for all accounts based on their currency string.
     */
    private function updateCurrencyIds(): void
    {
        $db = Schema::getConnection();

        // Get all accounts with null currency_id
        $accounts = $db->table('accounts')
            ->whereNull('currency_id')
            ->get();

        foreach ($accounts as $account) {
            // Find the currency by code
            $currency = $db->table('currencies')
                ->where('code', $account->currency)
                ->first();

            if ($currency) {
                // Update the account with the currency_id
                $db->table('accounts')
                    ->where('id', $account->id)
                    ->update(['currency_id' => $currency->id]);
            } else {
                // If currency not found, use USD as default
                $usdCurrency = $db->table('currencies')
                    ->where('code', 'USD')
                    ->first();

                if ($usdCurrency) {
                    $db->table('accounts')
                        ->where('id', $account->id)
                        ->update(['currency_id' => $usdCurrency->id]);
                }
            }
        }
    }

    /**
     * Update currency string for all accounts based on their currency_id.
     */
    private function updateCurrencyStrings(): void
    {
        $db = Schema::getConnection();

        // Get all accounts
        $accounts = $db->table('accounts')->get();

        foreach ($accounts as $account) {
            if ($account->currency_id) {
                // Find the currency
                $currency = $db->table('currencies')
                    ->where('id', $account->currency_id)
                    ->first();

                if ($currency) {
                    // Update the account with the currency code
                    $db->table('accounts')
                        ->where('id', $account->id)
                        ->update(['currency' => $currency->code]);
                }
            }
        }
    }
};
