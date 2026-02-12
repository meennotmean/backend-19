<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create room_types table
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Add room_type_id to rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('capacity')->constrained('room_types')->nullOnDelete();
        });

        // 3. Seed default types
        $defaultTypes = ['ห้องเรียน', 'ห้องประชุม', 'ห้องปฏิบัติการคอมพิวเตอร์'];
        foreach ($defaultTypes as $type) {
            DB::table('room_types')->insertOrIgnore([
                'name' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Migrate existing data
        // Iterate through rooms, find matching type or create new one
        $rooms = DB::table('rooms')->get();
        foreach ($rooms as $room) {
            if (!empty($room->type)) {
                // Check if exists
                $typeId = DB::table('room_types')->where('name', $room->type)->value('id');
                if (!$typeId) {
                    // Create new type from existing string
                    $typeId = DB::table('room_types')->insertGetId([
                        'name' => $room->type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                // Update room
                DB::table('rooms')->where('id', $room->id)->update(['room_type_id' => $typeId]);
            }
        }

        // 5. Drop old column
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('type')->nullable();
        });

        // Restore data (best effort)
        $rooms = DB::table('rooms')->get();
        foreach ($rooms as $room) {
            if ($room->room_type_id) {
                $typeName = DB::table('room_types')->where('id', $room->room_type_id)->value('name');
                DB::table('rooms')->where('id', $room->id)->update(['type' => $typeName]);
            }
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropColumn('room_type_id');
        });

        Schema::dropIfExists('room_types');
    }
};
