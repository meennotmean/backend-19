@extends('layouts.app')
@section('title', 'Manage Room Types')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>จัดการประเภทห้อง</h2>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">เพิ่มประเภทห้องใหม่</div>
                    <div class="card-body">
                        <form action="{{ route('room_types.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">ชื่อประเภทห้อง</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" required placeholder="เช่น ห้องประชุม">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">บันทึก</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">รายการประเภทห้อง</div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ชื่อประเภท</th>
                                    <th style="width: 150px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($types as $type)
                                    <tr>
                                        <td>{{ $type->name }}</td>
                                        <td>
                                            <form action="{{ route('room_types.destroy', $type->id) }}" method="POST"
                                                onsubmit="return confirm('ยืนยันการลบประเภทห้อง {{ $type->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($types->isEmpty())
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">ไม่พบข้อมูล</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
