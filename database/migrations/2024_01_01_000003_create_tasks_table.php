<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');                      // مفتاح من WorkTypes::TASKS
            $table->string('stage')->default('فكرة');     // مرحلة سير العمل
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date');                    // منه يُشتق الشهر
            $table->boolean('is_late')->default(false);  // نصف النقاط عند التأخر
            $table->boolean('is_creative')->default(false); // نقاط إبداع/سرعة
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->index('due_date');
        });
    }

    public function down(): void { Schema::dropIfExists('tasks'); }
};
