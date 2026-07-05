<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_plans', function (Blueprint $table) {
            $table->id();
            $table->string('platform');                 // قائمة مرجعية
            $table->date('plan_date');                  // التاريخ — منه يُشتق الشهر
            $table->string('day_name')->nullable();     // اليوم
            $table->string('plan_time')->nullable();    // توقيت النشر
            $table->string('content_type');             // نوع المحتوى
            $table->string('post_type');                // نوع المنشور
            $table->text('design_content')->nullable();
            $table->string('design_text')->nullable();
            $table->text('caption')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();    // مرفقات (مسارات/أسماء)
            $table->string('status')->default('فكرة');
            $table->string('design_file')->nullable();  // تصميم المصمم النهائي
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_state')->default('pending'); // pending|approved|review|rejected
            $table->text('approval_note')->nullable();
            $table->timestamps();
            $table->index('plan_date');
        });
    }

    public function down(): void { Schema::dropIfExists('content_plans'); }
};
