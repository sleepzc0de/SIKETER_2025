<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing constraint jika ada
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }

        // Tambahkan constraint baru dengan PPK role
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'pimpinan', 'staff', 'tu', 'ppk'))");
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'pimpinan', 'staff', 'tu'))");
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }
    }
};
