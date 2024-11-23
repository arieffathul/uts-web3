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
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipe_pesanan');
            $table->boolean('nasi')->default(0);  // Kolom baru dengan tipe boolean, default 0
            $table->integer('total_harga');
            $table->text('alamat_pesanan');
            $table->text('catatan')->nullable();
            $table->string('metode_pembayaran');
            $table->string('balasan')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
