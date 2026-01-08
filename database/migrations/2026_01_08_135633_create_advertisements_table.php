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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_to_all')->default(true); // Show to all users
            $table->json('target_roles')->nullable(); // Specific roles if show_to_all is false
            $table->boolean('require_acknowledgment')->default(true);
            $table->boolean('allow_redisplay')->default(false); // Allow re-display if updated
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('start_date');
            $table->index('expiry_date');
        });
        
        Schema::create('advertisement_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('acknowledged_at');
            $table->text('notes')->nullable();
            $table->timestamps(); // Add created_at and updated_at columns
            
            $table->unique(['advertisement_id', 'user_id']); // Prevent duplicate acknowledgments
            $table->index('advertisement_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_acknowledgments');
        Schema::dropIfExists('advertisements');
    }
};
