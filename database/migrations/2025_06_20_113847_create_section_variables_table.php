<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('section_variables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['text', 'image'])->default('text');
            $table->text('prompt');
            $table->text('default_text_value')->nullable();
            $table->string('default_image_path')->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'name']); // Each variable name must be unique per section
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_variables');
    }
};
