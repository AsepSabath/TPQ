<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spp_invoices', function (Blueprint $table) {
            $table->enum('period_status', ['belum_berjalan', 'berjalan'])
                ->default('berjalan')
                ->after('due_date');
        });

        DB::table('spp_invoices')
            ->orderBy('id')
            ->chunkById(100, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $periodDate = Carbon::create((int) $invoice->year, (int) $invoice->month, 1)->startOfMonth();
                    $periodStatus = $periodDate->lte(now()->startOfMonth())
                        ? 'berjalan'
                        : 'belum_berjalan';

                    DB::table('spp_invoices')
                        ->where('id', $invoice->id)
                        ->update(['period_status' => $periodStatus]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spp_invoices', function (Blueprint $table) {
            $table->dropColumn('period_status');
        });
    }
};