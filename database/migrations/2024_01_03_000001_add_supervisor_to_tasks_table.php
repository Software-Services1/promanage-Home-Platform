<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->index('supervisor_id', 'tasks_supervisor_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropIndex('tasks_supervisor_idx');
            $table->dropColumn('supervisor_id');
        });
    }
};
