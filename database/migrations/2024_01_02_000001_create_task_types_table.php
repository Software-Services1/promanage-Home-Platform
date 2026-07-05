<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // مفتاح ثابت يُستخدم في المهام
            $table->string('label');
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('bonus')->default(0); // نقاط إضافية (إبداع/سرعة)
            $table->string('category')->default('general'); // design|video|idea|sup|general
            $table->boolean('counts_when_published')->default(false); // مثل فكرة الريلز
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('task_types'); }
};
