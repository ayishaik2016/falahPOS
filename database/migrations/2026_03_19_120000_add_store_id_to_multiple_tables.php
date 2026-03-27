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
        $tables = [
            'customers',
            'parties',
            'items',
            'sales',
            'purchases',
            'sale_orders',
            'purchase_orders',
            'quotations',
            'expenses',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'store_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('company_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'customers',
            'parties',
            'items',
            'sales',
            'purchases',
            'sale_orders',
            'purchase_orders',
            'quotations',
            'expenses',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'store_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('store_id');
                });
            }
        }
    }
};
