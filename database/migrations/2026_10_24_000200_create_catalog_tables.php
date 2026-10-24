<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori tryout: 1 SKD + 8 SKB Nakes. Harga satuan ditentukan per kategori.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->index();          // skd | skb
            $table->string('code', 40)->unique();          // skd, fisioterapi, bidan, ...
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 40)->default('clipboard');
            $table->string('color', 20)->default('#1d4ed8');
            $table->string('image')->nullable();           // override gambar template
            $table->unsignedInteger('price')->default(0);  // harga satuan per tryout
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('question_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('subtest', 10);                 // twk | tiu | tkp | skb
            $table->string('name');
            $table->timestamps();
            $table->unique(['category_id', 'subtest', 'name']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('subtest', 10);
            $table->foreignId('topic_id')->nullable()->constrained('question_topics')->nullOnDelete();
            $table->longText('question_text');
            $table->string('image')->nullable();
            $table->longText('explanation')->nullable();
            $table->unsignedTinyInteger('difficulty')->nullable(); // 1 mudah, 2 sedang, 3 sulit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category_id', 'subtest']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->text('option_text')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedTinyInteger('weight')->default(0); // khusus TKP (1-5)
            $table->timestamps();
        });

        Schema::create('tryouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(100);
            $table->decimal('scale_max', 8, 2)->nullable();   // SKB: skala nilai (mis. 100 / 400)
            $table->json('passing_grade')->nullable();         // SKD: {twk,tiu,tkp}; SKB: {score}
            $table->boolean('is_free')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_explanation')->default(true);
            $table->unsignedTinyInteger('max_attempts')->default(3); // 0 = tanpa batas
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category_id', 'is_published']);
        });

        Schema::create('question_tryout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->unique(['tryout_id', 'question_id']);
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('price');
            $table->string('badge', 40)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('package_tryout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->unique(['package_id', 'tryout_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tryout');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('question_tryout');
        Schema::dropIfExists('tryouts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_topics');
        Schema::dropIfExists('categories');
    }
};
