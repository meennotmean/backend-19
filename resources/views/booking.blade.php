@extends('layouts.app')
@section('title', 'Booking Confirmation')
@section('content')
    <div class="container">
        <h2>จองห้องเรียน</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            <a href="{{ route('booking') }}" class="btn btn-primary">กลับไปหน้าจอง</a>
        @elseif ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="{{ route('rooms') }}" class="btn btn-secondary">กลับไปเลือกใหม่</a>
        @endif

        @if (isset($preSelectedRoomId) && $preSelectedRoomId)
            @php
                $room = $rooms->firstWhere('id', $preSelectedRoomId);
                // Map slots for display
                $slotLabels = [
                    'slot1' => '08:30-12:30',
                    'slot2' => '13:30-17:30',
                    'slot3' => '18:30-20:00',
                    'slot_w_1' => '08:20-09:10',
                    'slot_w_2' => '09:10-10:00',
                    'slot_w_3' => '10:00-10:50',
                    'slot_w_4' => '10:50-11:40',
                    'slot_w_5' => '11:40-12:30',
                    'slot_w_6' => '12:30-13:20',
                    'slot_w_7' => '13:20-14:10',
                    'slot_w_8' => '14:10-15:00',
                    'slot_w_9' => '15:00-15:50',
                    'slot_w_10' => '15:50-16:40',
                    'slot_w_11' => '16:40-17:30',
                    'slot_w_12' => '17:30-18:20',
                ];
                $selectedSlots = is_array($preSelectedSlots)
                    ? $preSelectedSlots
                    : ($preSelectedSlots
                        ? [$preSelectedSlots]
                        : []);
                $displaySlots = array_map(function ($s) use ($slotLabels) {
                    return $slotLabels[$s] ?? $s;
                }, $selectedSlots);

                // Prepare dates
                // Controller passes 'preSelectedDates' (plural)
                $selectedDates = isset($preSelectedDates)
                    ? (is_array($preSelectedDates)
                        ? $preSelectedDates
                        : [$preSelectedDates])
                    : [];

                $displayTextType = $preSelectedType === 'group' ? 'จองแบบกลุ่ม (หลายวัน)' : 'จองรายวัน';
            @endphp

            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    ยืนยันข้อมูลการจอง
                </div>
                <div class="card-body">
                    <form action="{{ route('booking_store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $preSelectedRoomId }}">

                        {{-- Booking Dates can be multiple --}}
                        @foreach ($selectedDates as $d)
                            <input type="hidden" name="booking_dates[]" value="{{ $d }}">
                        @endforeach

                        <input type="hidden" name="booking_type" value="{{ $preSelectedType ?? 'single' }}">

                        {{-- Slots --}}
                        @foreach ($selectedSlots as $slot)
                            <input type="hidden" name="time_slots[]" value="{{ $slot }}">
                        @endforeach

                        {{-- booking_dates[] and time_slots[] handle both single and group modes --}}

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>ประเภทการจอง:</strong> {{ $displayTextType }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>วันที่:</strong>
                                <ul>
                                    @foreach ($selectedDates as $d)
                                        <li>{{ \Illuminate\Support\Carbon::parse($d)->locale('th')->isoFormat('LL') }}
                                            ({{ $d }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>ห้อง:</strong> {{ $room ? $room->name : 'ไม่พบห้อง' }} <br>
                                <small class="text-muted">{{ $room ? $room->description : '' }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>เวลาที่เลือก:</strong>
                                <ul>
                                    @foreach ($displaySlots as $ds)
                                        <li>{{ $ds }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            กรุณาตรวจสอบข้อมูลก่อนกดปุ่ม "ยืนยันการจอง"
                        </div>

                        <button type="submit" class="btn btn-success btn-lg">ยืนยันการจอง</button>
                        <a href="{{ route('rooms') }}" class="btn btn-secondary btn-lg">ยกเลิก</a>
                    </form>
                </div>
            </div>
        @else
            @if (!session('success') && !$errors->any())
                <div class="alert alert-warning mt-4">
                    ไม่พบข้อมูลการจอง กรุณาเลือกห้องจากหน้า <a href="{{ route('rooms') }}">Rooms</a> ก่อนครับ
                </div>
            @endif
        @endif
    </div>
@endsection
