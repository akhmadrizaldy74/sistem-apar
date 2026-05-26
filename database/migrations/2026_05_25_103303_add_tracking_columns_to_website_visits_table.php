<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->string('event_type', 50)->nullable()->after('user_agent');
            $table->foreignId('product_id')->nullable()->unsigned()->after('event_type');
            $table->string('page_title', 255)->nullable()->after('product_id');
            $table->string('session_id', 64)->nullable()->index()->after('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['event_type', 'product_id', 'page_title', 'session_id']);
        });
    }
};