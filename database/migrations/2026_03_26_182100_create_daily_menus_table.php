<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('weekly_menu_id')->constrained()->cascadeOnDelete();
            $table->date('menu_date');
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['weekly_menu_id', 'menu_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_menus');
    }
};
