<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_positions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['opening', 'closing']);

            $table->decimal('kcb', 12, 2)->default(0);
            $table->decimal('equity', 12, 2)->default(0);
            $table->decimal('absa', 12, 2)->default(0);
            $table->decimal('mpesa', 12, 2)->default(0);
            $table->decimal('cash', 12, 2)->default(0);
            $table->decimal('other', 12, 2)->default(0);
            $table->string('other_label')->nullable();

            $table->decimal('total', 12, 2)->default(0);
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->constrained();

            $table->timestamps();

            // One opening and one closing per date
            $table->unique(['date', 'type']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_positions');
    }
};