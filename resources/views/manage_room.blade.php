@extends('layouts.app')
@section('title', 'Manage Rooms')

@section('content')
    @php
        $floorRooms = [
            '2' => ['19201', '19202', '19203', '19204', '19205', '19206'],
            '3' => ['19301', '19302', '19303', '19304', '19305', '19306'],
            '4' => ['19401', '19402', '19403', '19404', '19405', '19406'],
            '5' => ['19501', '19502', '19503', '19504', '19505', '19506'],
        ];
        $roomsByName = collect($rooms)->keyBy('name');
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="mb-0">จัดการห้องเรียน</h2>
            <small class="text-muted">
                ปุ่มสถานะ Avalible / No Avalible ใช้ปิด/เปิดห้องให้จอง  
                ถ้า No Avalible จะไม่สามารถจองห้องนั้นได้เลยจนกว่าจะเปิดใหม่
            </small>
        </div>
        <a href="{{ route('create') }}" class="btn btn-primary">สร้างห้องเพิ่มเติม</a>
    </div>

    @foreach ($floorRooms as $floor => $names)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">ชั้น {{ $floor }}</h5>
                    <small class="text-muted">ทั้งหมด {{ count($names) }} ห้อง</small>
                </div>

                <div class="row g-2">
                    @foreach ($names as $roomName)
                        @php
                            $room = $roomsByName->get($roomName);
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold">ห้อง {{ $roomName }}</div>
                                            @if ($room)
                                                <div class="small text-muted">ความจุ: {{ $room->capacity }}</div>
                                                <div class="small text-muted">ประเภท: {{ $room->type }}</div>
                                            @else
                                                <div class="small text-danger">ยังไม่มีข้อมูลห้องในระบบ</div>
                                            @endif
                                        </div>
                                        @if ($room)
                                            <div>
                                                @if ($room->status)
                                                    <a href="{{ route('change', $room->id) }}"
                                                        class="btn btn-sm btn-success">Avalible</a>
                                                @else
                                                    <a href="{{ route('change', $room->id) }}"
                                                        class="btn btn-sm btn-secondary">No Avalible</a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if ($room)
                                        <div class="mt-2 small text-muted">Description:</div>
                                        <div class="small">{{ $room->description }}</div>

                                        <div class="mt-3 d-flex gap-2">
                                            <a href="{{ route('edit', $room->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="{{ route('delete', $room->id) }}" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete {{ $room->name }} or No?')">Delete</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@endsection
