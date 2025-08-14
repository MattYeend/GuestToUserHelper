<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (config('guest-upgrade.models', []) as $model) {
            $tableName = (new $model)->getTable();
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'guest_token')) {
                    $table->uuid('guest_token')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (config('guest-upgrade.models', []) as $model) {
            $tableName = (new $model)->getTable();
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'guest_token')) {
                    $table->dropColumn('guest_token');
                }
            });
        }
    }
};
