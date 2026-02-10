@extends('layouts.app')
@section('title', 'ตั้งค่าการจองล่วงหน้า')

@section('content')
    <div class="container">
        <h2>ตั้งค่าจำนวนวันจองล่วงหน้า</h2>
        <p class="text-muted">สำหรับ admin / staff เท่านั้น ใช้กำหนดว่าผู้ใช้ทั่วไปสามารถจองล่วงหน้าได้สูงสุดกี่วัน</p>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('booking_settings_update') }}" method="POST" class="mt-3" style="max-width: 400px;">
            @csrf

            <div class="mb-3">
                <label for="max_advance_days" class="form-label">จำนวนวันจองล่วงหน้าสูงสุด (หน่วย: วัน)</label>
                <input
                    type="number"
                    min="0"
                    max="365"
                    class="form-control"
                    id="max_advance_days"
                    name="max_advance_days"
                    value="{{ old('max_advance_days', $setting->max_advance_days ?? 14) }}"
                    required
                >
                <small class="text-muted">
                    0 = จองได้เฉพาะวันนี้, 7 = จองล่วงหน้าได้ 7 วัน, 14 = จองล่วงหน้าได้ 14 วัน เป็นต้น
                </small>
            </div>

            <button type="submit" class="btn btn-primary">บันทึกการตั้งค่า</button>
        </form>
    </div>
@endsection

