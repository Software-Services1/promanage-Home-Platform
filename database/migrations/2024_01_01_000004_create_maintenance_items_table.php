<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');                       // مفتاح من WorkTypes::MAINTENANCE
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->string('status')->default('قيد التنفيذ');
            $table->timestamps();
            $table->index('work_date');
        });
    }

    public function down(): void { Schema::dropIfExists('maintenance_items'); }
};
