@extends('layouts.app')
@section('title', 'Booking Status')

@section('content')
    <h1>แจ้งผลการจองห้องเรียน</h1>
    <p>เรียน คุณ {{ $booking->user->name }},</p>
    <p>การจองห้อง <strong>{{ $booking->room->name }}</strong> ของคุณในวันที่ {{ $booking->booking_date }}</p>
    <p>สถานะปัจจุบัน:
        <strong>
            @if ($booking->status == 'approved')
                อนุมัติแล้ว
            @elseif($booking->status == 'rejected')
                ปฏิเสธ
            @endif
        </strong>
    </p>
    <p>ขอบคุณที่ใช้บริการครับ</p>
@endsection
