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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('lead_status')->default('New');
            $table->string('industry')->nullable();
            $table->string('rating')->nullable();
            $table->decimal('estimated_budget', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('converted_to_deal_id')->nullable()->constrained('deals')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};