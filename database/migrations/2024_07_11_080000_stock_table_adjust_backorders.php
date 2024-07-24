<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_stocks', function (Blueprint $table): void {
            $table->dropColumn(['magento_backorders_enabled']);
            $table->integer('backorders')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('magento_stocks', function (Blueprint $table): void {
            $table->boolean('magento_backorders_enabled');
            $table->boolean('backorders')->default(false)->change();
        });
    }
};
