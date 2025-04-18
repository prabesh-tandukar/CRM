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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type'); // call, email, meeting, note, system, etc.
            $table->string('subject');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->integer('duration')->nullable(); // in minutes
            $table->string('status')->default('Planned'); // Planned, Completed, Cancelled
            $table->string('priority')->nullable()->default('Medium'); // Low, Medium, High
            $table->timestamp('completed_at')->nullable();
            $table->morphs('activityable'); // Polymorphic relationship (Contact, Deal, Lead, etc.)
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};