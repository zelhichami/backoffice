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
        Schema::create('sections', function (Blueprint $table) {
            $table->uuid('id')->default(DB::raw('(UUID())'))->unique();
            // Assuming you have a users table and standard auth
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->index();
            $table->string('screenshot_path')->nullable();
            $table->string('status')->default('draft'); // e.g., draft, published
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
