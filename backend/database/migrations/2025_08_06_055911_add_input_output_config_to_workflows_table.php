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
        Schema::table('workflows', function (Blueprint $table) {
            $table->enum('input_type', ['text', 'pdf'])->default('text')->after('name');
            $table->enum('output_type', ['text', 'pdf'])->default('text')->after('input_type');
            $table->text('input_data')->nullable()->after('output_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['input_type', 'output_type', 'input_data']);
        });
    }
};
