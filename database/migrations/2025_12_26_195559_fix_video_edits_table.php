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
        Schema::table('video_edits', function (Blueprint $table) {
        $table->dropForeign(['video_id']);

            // 🔴 then drop the column
            $table->dropColumn('video_id');

            // optional: remove old json column if exists
            if (Schema::hasColumn('video_edits', 'edit_data')) {
                $table->dropColumn('edit_data');
            }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_edits', function (Blueprint $table) {
            $table->unsignedBigInteger('video_id')->nullable();
        });
    }
};
