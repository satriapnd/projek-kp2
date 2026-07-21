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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tamu_id')->constrained('tamu')->onDelete('cascade');
            $table->string('keperluan')->nullable();
            $table->timestamp('jam_masuk')->useCurrent();
            $table->timestamp('jam_keluar')->nullable();
            $table->enum('status', ['sedang berkunjung', 'selesai'])->default('sedang berkunjung');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
