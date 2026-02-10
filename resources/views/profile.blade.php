@extends('layouts.app')
@section('title', 'Profile')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4 class="mb-3">ข้อมูลผู้ใช้</h4>
            <p class="mb-1"><strong>รหัสประจำตัว:</strong> {{ Auth::user()->userid }}</p>
            <p class="mb-0"><strong>บทบาท:</strong> {{ Auth::user()->role }}</p>
        </div>
    </div>
@endsection
