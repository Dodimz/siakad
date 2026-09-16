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
        Schema::create('pmb_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kampus_id')->constrained('kampus')->cascadeOnDelete();
            $table->string('no_pendaftar');
            $table->string('nama')->nullable();
            $table->string('aksi'); // created, duplicate
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['kampus_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pmb_import_logs');
    }
};
