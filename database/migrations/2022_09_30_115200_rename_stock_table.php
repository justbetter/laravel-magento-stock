<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('stocks', 'magento_stocks');
    }

    public function down(): void
    {
        Schema::rename('magento_stocks', 'stocks');
    }
};
