<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Booking;
use App\Models\BookingSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusMail;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $userRole = auth()->user()->role ?? 'user';
        $setting = BookingSetting::first();
        $maxDays = $setting && $setting->max_advance_days !== null ? (int) $setting->max_advance_days : 14;
        if ($maxDays < 0) {
            $maxDays = 0;
        }
        $maxDate = Carbon::today()->addDays($maxDays)->toDateString();
        $limitRule = ($userRole === 'admin' || $userRole === 'staff') ? null : 'before_or_equal:' . $maxDate;

        $bookingType = $request->input('booking_type', 'single');

        // จองแบบกลุ่ม: ห้องเดียวกัน เวลาเดียวกัน แต่หลายวัน (สูงสุด 3 วัน)
        if ($bookingType === 'group') {
            $validated = $request->validate([
                'room_id' => 'required',
                'time_slot' => 'required|in:slot1,slot2,slot3',
                'booking_dates' => 'required|array|min:1|max:3',
                'booking_dates.*' => array_filter(['nullable', 'date', 'after_or_equal:today', $limitRule]),
            ]);

            // ถ้าห้องถูกตั้งเป็น No Avalible ห้ามจองทุกกรณี
            $room = Room::find($validated['room_id']);
            if (!$room || !$room->status) {
                return back()->withErrors([
                    'room_id' => 'ห้องนี้ถูกปิดไม่ให้จอง กรุณาเลือกห้องอื่น',
                ])->withInput();
            }

            [$startTime, $endTime] = $this->mapTimeSlotToRange($validated['time_slot']);

            $roomId = $validated['room_id'];
            $userId = auth()->user()->userid;

            $conflicts = [];

            $dates = array_unique(array_filter($validated['booking_dates']));

            if (count($dates) === 0) {
                return back()->withErrors([
                    'booking_dates' => 'กรุณาเลือกอย่างน้อย 1 วันที่ต้องการจอง',
                ])->withInput();
            }

            foreach ($dates as $date) {
                $exists = Booking::where('room_id', $roomId)
                    ->where('booking_date', $date)
                    ->where('start_time', $startTime)
                    ->where('end_time', $endTime)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($exists) {
                    $conflicts[] = $date;
                    continue;
                }

                Booking::create([
                    'user_id' => $userId,
                    'room_id' => $roomId,
                    'booking_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => 'pending',
                ]);
            }

            if (!empty($conflicts)) {
                return back()->withErrors([
                    'booking_dates' => 'ไม่สามารถจองวันที่ต่อไปนี้ได้ เนื่องจากมีผู้ใช้อื่นจองไปแล้ว: ' . implode(', ', $conflicts),
                ])->withInput();
            }

            return back()->with('success', 'สร้างการจองแบบกลุ่มเรียบร้อยแล้ว กำลังรอการอนุมัติ');
        }

        // จองแบบวันต่อวัน (เดี่ยว) ใช้ปุ่มช่วงเวลา 3 ปุ่ม
        $validated = $request->validate([
            'room_id' => 'required',
            'booking_date' => array_filter(['required', 'date', 'after_or_equal:today', $limitRule]),
            'time_slot' => 'required|in:slot1,slot2,slot3',
        ]);

        // ถ้าห้องถูกตั้งเป็น No Avalible ห้ามจองทุกกรณี
        $room = Room::find($validated['room_id']);
        if (!$room || !$room->status) {
            return back()->withErrors([
                'room_id' => 'ห้องนี้ถูกปิดไม่ให้จอง กรุณาเลือกห้องอื่น',
            ])->withInput();
        }

        [$startTime, $endTime] = $this->mapTimeSlotToRange($validated['time_slot']);

        $exists = Booking::where('room_id', $validated['room_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'booking_date' => 'ช่วงเวลานี้ของห้องที่เลือกถูกจองแล้ว ไม่สามารถจองซ้ำได้',
            ])->withInput();
        }

        Booking::create([
            'user_id' => auth()->user()->userid,
            'room_id' => $validated['room_id'],
            'booking_date' => $validated['booking_date'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
        ]);

        return back()->with('success', 'การจองห้องของคุณถูกส่งไปรอการอนุมัติแล้วครับ');
    }

    public function availability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'slot' => 'required|in:slot1,slot2,slot3',
        ]);

        [$startTime, $endTime] = $this->mapTimeSlotToRange($validated['slot']);

        $bookedRoomIds = Booking::where('booking_date', $validated['date'])
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('room_id')
            ->toArray();

        // ห้องที่ถูกตั้งเป็น No Avalible ให้ถือว่าไม่ว่าง
        $closedRoomIds = Room::where('status', false)->pluck('id')->toArray();

        $unavailable = array_values(array_unique(array_merge($bookedRoomIds, $closedRoomIds)));

        return response()->json([
            'bookedRoomIds' => $unavailable,
        ]);
    }

    public function index()
    {
        $rooms = Room::all();

        $today = Carbon::today()->toDateString();

        $setting = BookingSetting::first();
        $maxDays = $setting && $setting->max_advance_days !== null ? (int) $setting->max_advance_days : 14;
        if ($maxDays < 0) {
            $maxDays = 0;
        }
        $maxBookingDate = Carbon::today()->addDays($maxDays)->toDateString();

        $bookedRoomIdsToday = Booking::where('booking_date', $today)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('room_id')
            ->toArray();

        return view('booking', [
            'rooms' => $rooms,
            'today' => $today,
            'maxAdvanceDays' => $maxDays,
            'maxBookingDate' => $maxBookingDate,
            'bookedRoomIdsToday' => $bookedRoomIdsToday,
        ]);
    }

    public function settings()
    {
        $setting = BookingSetting::first();
        if (!$setting) {
            $setting = new BookingSetting(['max_advance_days' => 14]);
        }

        return view('booking_settings', compact('setting'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'max_advance_days' => 'required|integer|min:0|max:365',
        ]);

        $setting = BookingSetting::first();
        if (!$setting) {
            $setting = new BookingSetting();
        }
        $setting->max_advance_days = $validated['max_advance_days'];
        $setting->save();

        return back()->with('success', 'บันทึกจำนวนวันจองล่วงหน้าเรียบร้อยแล้ว');
    }

    public function history()
    {

        $bookings = Booking::where('user_id', auth()->user()->userid)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('history', compact('bookings'));
    }

    public function cancel($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->user()->userid)
            ->firstOrFail();

        $booking->update(['status' => 'canceled']);

        return back()->with('success', 'ยกเลิกการจองเรียบร้อยแล้ว');
    }

    public function manage()
    {
        $bookings = Booking::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin_manage_bookings', compact('bookings'));
    }

    public function updateStatus(Request $request, $id)
    {
        // 1. ค้นหาข้อมูลการจองพร้อมดึงข้อมูล User และ Room มาด้วย
        $booking = Booking::with(['user', 'room'])->findOrFail($id);

        // 2. อัปเดตสถานะในฐานข้อมูล (approved, rejected, canceled)
        $booking->update([
            'status' => $request->status
        ]);

        // 3. ส่งอีเมลแจ้งเตือนไปยังผู้จองทันที
        // ตรวจสอบว่าผู้จองมีอีเมลจริงก่อนส่ง เพื่อป้องกัน Error
        if ($booking->user && $booking->user->email) {
            Mail::to($booking->user->email)->send(new BookingStatusMail($booking));
        }

        return back()->with('success', 'อัปเดตสถานะและส่งอีเมลแจ้งเตือนเรียบร้อยแล้ว');
    }

    /**
     * แปลง time_slot จากปุ่มให้เป็นช่วงเวลาเริ่ม-จบ
     */
    private function mapTimeSlotToRange(string $timeSlot): array
    {
        switch ($timeSlot) {
            case 'slot1':
                // 08:30-12:30
                return ['08:30:00', '12:30:00'];
            case 'slot2':
                // 13:30-17:30
                return ['13:30:00', '17:30:00'];
            case 'slot3':
                // 18:30-20:00
                return ['18:30:00', '20:00:00'];
            default:
                // กันกรณีผิดพลาด
                return ['00:00:00', '00:00:00'];
        }
    }
}
