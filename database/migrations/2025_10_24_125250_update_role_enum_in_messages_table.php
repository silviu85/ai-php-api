<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This is the raw SQL command to modify the ENUM column.
        // It's the standard way to handle this in MySQL/MariaDB.
        DB::statement("ALTER TABLE messages CHANGE COLUMN role role ENUM('user', 'assistant', 'system') NOT NULL");
    }

    public function down(): void
    {
        // This allows you to revert the migration if needed.
        DB::statement("ALTER TABLE messages CHANGE COLUMN role role ENUM('user', 'assistant') NOT NULL");
    }
};