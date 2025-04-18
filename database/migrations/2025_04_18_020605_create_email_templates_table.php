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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->string('category')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Add a few default templates
        $defaultTemplates = [
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{company_name}}',
                'body' => '<p>Dear {{contact_name}},</p>
                           <p>Thank you for your interest in {{company_name}}. We are excited to work with you.</p>
                           <p>If you have any questions, please don\'t hesitate to reach out.</p>
                           <p>{{email_signature}}</p>',
                'category' => 'general',
            ],
            [
                'name' => 'Meeting Follow-up',
                'subject' => 'Follow-up: Our Meeting on {{meeting_date}}',
                'body' => '<p>Dear {{contact_name}},</p>
                           <p>Thank you for taking the time to meet with me today to discuss {{meeting_topic}}.</p>
                           <p>As promised, I\'m sending you the information we discussed. Please let me know if you have any questions.</p>
                           <p>{{email_signature}}</p>',
                'category' => 'follow-up',
            ],
            [
                'name' => 'Proposal',
                'subject' => 'Proposal for {{company_name}}',
                'body' => '<p>Dear {{contact_name}},</p>
                           <p>Please find attached our proposal for {{deal_name}}.</p>
                           <p>I\'d be happy to schedule a call to discuss the details further.</p>
                           <p>{{email_signature}}</p>',
                'category' => 'sales',
            ],
        ];

        $adminUser = DB::table('users')->where('email', 'admin@example.com')->first();
        if ($adminUser) {
            foreach ($defaultTemplates as $template) {
                DB::table('email_templates')->insert(array_merge($template, [
                    'created_by' => $adminUser->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};