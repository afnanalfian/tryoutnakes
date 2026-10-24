<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_no')->default(1);
            $table->string('status', 20)->default('in_progress'); // in_progress | submitted
            $table->timestamp('started_at');
            $table->timestamp('deadline_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('question_order');
            $table->json('option_order')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->unsignedSmallInteger('score_twk')->nullable();
            $table->unsignedSmallInteger('score_tiu')->nullable();
            $table->unsignedSmallInteger('score_tkp')->nullable();
            $table->json('breakdown')->nullable();
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->unsignedSmallInteger('wrong_count')->default(0);
            $table->unsignedSmallInteger('empty_count')->default(0);
            $table->boolean('is_passed')->nullable();
            $table->string('submit_reason', 20)->nullable(); // manual | timeout
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['tryout_id', 'attempt_no', 'status']);
            $table->index(['status', 'deadline_at']);
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->boolean('is_flagged')->default(false);
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 6, 2)->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('attempts');
    }
};
