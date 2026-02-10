<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Booking;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // รายชื่อห้องตามที่กำหนด (ชั้น 2-5)
        $roomNumbers = [
            '19201', '19202', '19203', '19204', '19205', '19206',
            '19301', '19302', '19303', '19304', '19305', '19306',
            '19401', '19402', '19403', '19404', '19405', '19406',
            '19501', '19502', '19503', '19504', '19505', '19506',
        ];

        // หมายเหตุ: ห้ามลบ rooms ทั้งหมดตรง ๆ เพราะมี bookings ผูก FK อยู่
        // วิธีนี้จะ "รีเนม/อัปเดต" ห้องเดิมตามลำดับ id (เพื่อคง room_id เดิมของ booking)
        // และถ้าห้องไม่ครบ 24 จะสร้างเพิ่มให้ครบ

        $existingRooms = Room::orderBy('id')->get()->values();

        foreach ($roomNumbers as $idx => $number) {
            if (isset($existingRooms[$idx])) {
                $existingRooms[$idx]->update([
                    'name' => $number,
                    'capacity' => $existingRooms[$idx]->capacity ?: 40,
                    'type' => 'ห้องเรียน',
                    'description' => 'ห้อง ' . $number,
                    'status' => true,
                ]);
            } else {
                Room::create([
                    'name' => $number,
                    'capacity' => 40,
                    'type' => 'ห้องเรียน',
                    'description' => 'ห้อง ' . $number,
                    'status' => true,
                ]);
            }
        }

        // ลบห้องส่วนเกินที่ไม่อยู่ในรายการ *เฉพาะห้องที่ไม่มี booking ผูกอยู่*
        $bookedRoomIds = Booking::distinct()->pluck('room_id')->toArray();
        Room::whereNotIn('name', $roomNumbers)
            ->whereNotIn('id', $bookedRoomIds)
            ->delete();
    }
}

