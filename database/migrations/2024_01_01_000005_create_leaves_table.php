<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedInteger('days')->default(1);
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->string('reason')->nullable();
            $table->date('requested_at')->nullable();
            $table->timestamps();
            $table->index('from_date');
        });
    }

    public function down(): void { Schema::dropIfExists('leaves'); }
};
