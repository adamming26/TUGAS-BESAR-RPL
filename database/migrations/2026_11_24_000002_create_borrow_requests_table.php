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
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['barang', 'ruangan'])->default('barang');
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->date('borrow_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_hours')->default(1);
            $table->text('purpose');
            $table->integer('participant_count')->nullable()->default(1);
            $table->string('status')->default('menunggu'); // menunggu, disetujui, dipinjam, dikembalikan, ditolak, selesai
            $table->text('reject_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
};
