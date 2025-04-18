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
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('probability')->nullable(); // default probability percentage for deals in this stage
            $table->integer('display_order')->default(0);
            $table->boolean('is_won')->default(false); // if this stage represents a won deal
            $table->boolean('is_lost')->default(false); // if this stage represents a lost deal
            $table->timestamps();
        });

        // Add default pipeline stages
        $stages = [
            ['name' => 'Qualification', 'probability' => 10, 'display_order' => 1],
            ['name' => 'Needs Analysis', 'probability' => 25, 'display_order' => 2],
            ['name' => 'Proposal', 'probability' => 50, 'display_order' => 3],
            ['name' => 'Negotiation', 'probability' => 75, 'display_order' => 4],
            ['name' => 'Closed Won', 'probability' => 100, 'display_order' => 5, 'is_won' => true],
            ['name' => 'Closed Lost', 'probability' => 0, 'display_order' => 6, 'is_lost' => true],
        ];

        $now = now();
        foreach ($stages as $stage) {
            DB::table('pipeline_stages')->insert(array_merge($stage, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};