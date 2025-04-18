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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $defaultSettings = [
            // Company information
            ['key' => 'company_name', 'value' => 'Your Company', 'group' => 'company'],
            ['key' => 'company_email', 'value' => 'info@yourcompany.com', 'group' => 'company'],
            ['key' => 'company_phone', 'value' => '+1234567890', 'group' => 'company'],
            ['key' => 'company_address', 'value' => '123 Business Street, City', 'group' => 'company'],
            
            // Email settings
            ['key' => 'email_signature', 'value' => 'Best Regards,<br>Your Company Team', 'group' => 'email'],
            
            // Lead settings
            ['key' => 'lead_sources', 'value' => json_encode([
                'Website', 'Referral', 'Cold Call', 'Social Media', 'Trade Show', 'Email Campaign', 'Google Ads', 'Other'
            ]), 'group' => 'leads'],
            
            ['key' => 'lead_statuses', 'value' => json_encode([
                'New', 'Contacted', 'Qualified', 'Unqualified', 'Nurturing', 'Converted'
            ]), 'group' => 'leads'],
            
            // Deal settings
            ['key' => 'deal_lost_reasons', 'value' => json_encode([
                'Price', 'Competitor', 'No Budget', 'No Response', 'Needs Not Matched', 'Time Frame', 'Other'
            ]), 'group' => 'deals'],
            
            // System settings
            ['key' => 'items_per_page', 'value' => '10', 'group' => 'system'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'system'],
            ['key' => 'time_format', 'value' => 'H:i', 'group' => 'system'],
            ['key' => 'default_currency', 'value' => 'USD', 'group' => 'system'],
            ['key' => 'default_timezone', 'value' => 'UTC', 'group' => 'system'],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};