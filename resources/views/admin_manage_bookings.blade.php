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
                    <th>จัดการ</th>
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
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
