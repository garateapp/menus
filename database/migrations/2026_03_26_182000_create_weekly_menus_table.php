<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'week_start_date', 'week_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_menus');
    }
};
