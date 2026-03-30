<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_selections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_option_id')->constrained()->cascadeOnDelete();
            $table->timestamp('selected_at');
            $table->timestamps();

            $table->unique(['user_id', 'daily_menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_selections');
    }
};
