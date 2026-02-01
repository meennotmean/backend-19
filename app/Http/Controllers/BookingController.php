<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingStatusMail;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        Booking::create([
            'user_id' => auth()->user()->userid,
            'room_id' => $request->room_id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending',
        ]);

        return back()->with('success', 'การจองห้องของคุณถูกส่งไปรอการอนุมัติแล้วครับ');
    }

    public function index()
    {
        $rooms = Room::all();
        return view('booking', compact('rooms'));
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

    public function updateStatus(Request $request, $id) //TODO: Auto sending email
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
}
