<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->index()->unique();

            $table->boolean('sync')->default(true);

            $table->boolean('in_stock')->default(false);
            $table->boolean('backorders')->default(false);
            $table->boolean('magento_backorders_enabled')->default(false);

            $table->bigInteger('quantity')->default(0);

            $table->json('msi_stock')->nullable();
            $table->json('msi_status')->nullable();

            $table->boolean('retrieve')->default(false);
            $table->boolean('update')->default(false);

            $table->dateTime('last_retrieved')->nullable();
            $table->dateTime('last_updated')->nullable();

            $table->integer('fail_count')->default(0);
            $table->dateTime('last_failed')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
