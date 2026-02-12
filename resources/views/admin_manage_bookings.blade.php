@extends('layouts.app')
@section('title', 'Manage Bookings')

@section('content')
    <div class="container">
        <h2>จัดการการจองห้องเรียน</h2>
        <table class="table table-bordered shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>รหัสผู้จอง</th>
                    <th>ห้อง</th>
                    <th>วันที่/เวลา</th>
                    <th>สถานะปัจจุบัน</th>
                    <th>จัดการ (อนุมัติ/ปฏิเสธ)</th>
                    <th>การเข้าใช้งาน (สำหรับที่อนุมัติแล้ว)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->user_id }}</td>
                        <td>{{ $booking->room->name }}</td>
                        <td>{{ $booking->booking_date }} ({{ $booking->start_time }}-{{ $booking->end_time }})</td>
                        <td>
                            <span
                                class="badge @if ($booking->status == 'pending') bg-warning @elseif($booking->status == 'approved') bg-success @else bg-danger @endif">
                                {{ $booking->status }}
                            </span>
                        </td>
                        <td>
                            @if ($booking->status == 'pending')
                                <form action="{{ route('admin_booking_update', $booking->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success">อนุมัติ</button>
                                </form>
                                <form action="{{ route('admin_booking_update', $booking->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($booking->status == 'approved')
                                <div class="d-flex gap-1">
                                    {{-- ปุ่มเข้าใช้งาน (1) --}}
                                    <form action="{{ route('admin_booking_update_usage', $booking->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_used" value="1">
                                        <button
                                            class="btn btn-sm {{ $booking->is_used == 1 ? 'btn-success' : 'btn-outline-success' }}"
                                            title="เข้าใช้งาน">
                                            <i class="bi bi-check-circle"></i> มาใช้งาน
                                        </button>
                                    </form>

                                    {{-- ปุ่มไม่เข้าใช้งาน (0) --}}
                                    <form action="{{ route('admin_booking_update_usage', $booking->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="is_used" value="0">
                                        <button
                                            class="btn btn-sm {{ $booking->is_used !== null && $booking->is_used == 0 ? 'btn-danger' : 'btn-outline-danger' }}"
                                            title="ไม่เข้าใช้งาน">
                                            <i class="bi bi-x-circle"></i> ไม่มาใช้งาน
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
