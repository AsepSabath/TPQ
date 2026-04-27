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
        Schema::create('risk_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete()->unique();
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->enum('risk_level', ['rendah', 'sedang', 'tinggi'])->default('rendah');
            $table->decimal('attendance_rate', 5, 2)->nullable();
            $table->decimal('avg_grade', 5, 2)->nullable();
            $table->unsignedTinyInteger('unpaid_months')->default(0);
            $table->unsignedSmallInteger('violation_count')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();

            $table->index(['risk_level', 'is_resolved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_alerts');
    }
};
