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
        Schema::table('kampus', function (Blueprint $table) {
            $table->string('pmb_token', 100)->nullable()->unique()->after('settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kampus', function (Blueprint $table) {
            $table->dropUnique(['pmb_token']);
            $table->dropColumn('pmb_token');
        });
    }
};
