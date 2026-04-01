<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_options', function (Blueprint $table) {
            $table->boolean('is_opt_out')->default(false)->after('is_visible');
        });

        $dailyMenuIds = DB::table('menu_options')
            ->select('daily_menu_id')
            ->where('is_opt_out', false)
            ->groupBy('daily_menu_id')
            ->pluck('daily_menu_id');

        foreach ($dailyMenuIds as $dailyMenuId) {
            $hasOptOut = DB::table('menu_options')
                ->where('daily_menu_id', $dailyMenuId)
                ->where('is_opt_out', true)
                ->exists();

            if ($hasOptOut) {
                continue;
            }

            DB::table('menu_options')->insert([
                'daily_menu_id' => $dailyMenuId,
                'title' => 'No solicitaré menú',
                'description' => 'Opción automática del sistema para informar que no solicitarás almuerzo este día.',
                'image_path' => null,
                'quota' => null,
                'is_visible' => true,
                'is_opt_out' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('menu_options', function (Blueprint $table) {
            $table->dropColumn('is_opt_out');
        });
    }
};
