@extends('layouts.app')
@section('title', 'Booking History')

@section('content')
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">ประวัติการจองห้องเรียนของคุณ</h4>
            </div>
            <div class="card-body">
                @if ($bookings->isEmpty())
                    <p class="text-center">คุณยังไม่มีประวัติการจองในขณะนี้</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>วันที่จอง</th>
                                    <th>ชื่อห้อง</th>
                                    <th>เวลา</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    <tr>
                                        <td>{{ date('d/m/Y', strtotime($booking->booking_date)) }}</td>
                                        <td>{{ $booking->room->name ?? 'ไม่พบข้อมูลห้อง' }}</td>
                                        <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                        <td>
                                            @if ($booking->status == 'pending')
                                                <span class="badge bg-warning text-dark">รออนุมัติ</span>
                                            @elseif($booking->status == 'approved')
                                                <span class="badge bg-success">อนุมัติแล้ว</span>
                                            @elseif($booking->status == 'rejected')
                                                <span class="badge bg-danger">ปฏิเสธ</span>
                                            @elseif($booking->status == 'canceled')
                                                <span class="badge bg-secondary">ยกเลิกแล้ว</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($booking->status != 'canceled')
                                                <form action="{{ route('booking_cancel', $booking->id) }}" method="POST"
                                                    onsubmit="return confirm('ยืนยันการยกเลิกการจอง?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger">ยกเลิก</button>
                                                </form>
                                            @else
                                                <span class="text-muted">ยกเลิกแล้ว</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
