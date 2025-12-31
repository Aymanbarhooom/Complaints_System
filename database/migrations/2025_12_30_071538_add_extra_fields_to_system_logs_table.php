<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToSystemLogsTable extends Migration
{
    public function up()
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->text('request_data')->nullable()->after('user_agent');
            $table->integer('response_code')->nullable()->after('request_data');
            $table->decimal('duration', 10, 2)->nullable()->after('response_code'); // ms
            
            // Indexes للأداء
            $table->index('user_id');
            $table->index('response_code');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropColumn(['user_agent', 'request_data', 'response_code', 'duration']);
        });
    }
}