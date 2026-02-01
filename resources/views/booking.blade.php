@extends('layouts.app')
@section('title', 'Booking Room')
@section('content')
    <div class="container">
        <h2>จองห้องเรียน</h2>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('booking_store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>เลือกห้อง</label>
                <select name="room_id" id="room_select" class="form-control">
                    <option value="" required>-- กรุณาเลือกห้อง --</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>รายละเอียดห้อง</label>
                <div id="room_description" class="alert alert-info" style="display:none;">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>เวลาเริ่ม</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>เวลาสิ้นสุด</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label>วันที่จอง</label>
                <input type="date" name="booking_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">จองห้อง</button>

            <script>
                document.getElementById('room_select').addEventListener('change', function() {
                    const roomId = this.value;
                    const descriptionBox = document.getElementById('room_description');

                    if (roomId) {
                        // ดึงข้อมูลจาก Route ที่เราสร้างไว้
                        fetch(`/room-details/${roomId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.description) {
                                    descriptionBox.innerText = data.description;
                                    descriptionBox.style.display = 'block';
                                } else {
                                    descriptionBox.innerText = 'ไม่มีข้อมูลรายละเอียดสำหรับห้องนี้';
                                    descriptionBox.style.display = 'block';
                                }
                            });
                    } else {
                        descriptionBox.style.display = 'none';
                    }
                });
            </script>
        @endsection
