<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_plans', function (Blueprint $table) {
            $table->text('post_text')->nullable()->after('caption');       // نص المنشور
            $table->string('reference_link')->nullable()->after('post_text'); // رابط مرجعي
            $table->string('reference_file')->nullable()->after('reference_link'); // ملف/صورة مرجعية
        });

        // توحيد حالات الاعتماد إلى أربع: draft|pending|approved|rejected
        \Illuminate\Support\Facades\DB::table('content_plans')->where('approval_state', 'review')->update(['approval_state' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('content_plans', function (Blueprint $table) {
            $table->dropColumn(['post_text', 'reference_link', 'reference_file']);
        });
    }
};
