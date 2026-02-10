@extends('layouts.app')
@section('title', 'Rooms')

@section('content')
    @php
        $floorRooms = [
            '2' => ['19201', '19202', '19203', '19204', '19205', '19206'],
            '3' => ['19301', '19302', '19303', '19304', '19305', '19306'],
            '4' => ['19401', '19402', '19403', '19404', '19405', '19406'],
            '5' => ['19501', '19502', '19503', '19504', '19505', '19506'],
        ];
        $allTargetRooms = collect($floorRooms)->flatten()->toArray();

        $roomsByName = collect($rooms)->keyBy('name');
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-0">ห้องเรียน</h2>
            <small class="text-muted d-block">แสดงตามชั้น และสถานะว่าง/ไม่ว่างตามวันที่ + ช่วงเวลา</small>
        </div>
        <div class="d-flex align-items-end gap-2 flex-wrap">
            <div style="min-width: 190px;">
                <label class="form-label mb-1">วันที่</label>
                <input type="text" id="rooms_overview_calendar" class="form-control form-control-sm" placeholder="เลือกวันที่">
            </div>
            <div>
                <label class="form-label mb-1 d-block">เวลา</label>
                <div class="btn-group btn-group-sm" role="group" aria-label="Time slots">
                    <button type="button" class="btn btn-outline-secondary overview-slot-btn" data-slot="slot1">08:30-12:30</button>
                    <button type="button" class="btn btn-outline-secondary overview-slot-btn" data-slot="slot2">13:30-17:30</button>
                    <button type="button" class="btn btn-outline-secondary overview-slot-btn" data-slot="slot3">18:30-20:00</button>
                </div>
            </div>
        </div>
    </div>
    <hr>

    @foreach ($floorRooms as $floor => $names)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">ชั้น {{ $floor }}</h5>
                    <small class="text-muted" id="overview-floor-{{ $floor }}">ทั้งหมด {{ count($names) }} ห้อง</small>
                </div>

                <div class="row g-2">
                    @foreach ($names as $roomName)
                        @php
                            $room = $roomsByName->get($roomName);
                        @endphp
                        <div class="col-6 col-md-4 col-lg-2">
                            @if ($room)
                                <button type="button"
                                    class="btn w-100 rooms-overview-card"
                                    data-room-id="{{ $room->id }}"
                                    data-floor="{{ $floor }}"
                                >
                                    <div class="fw-bold">{{ $roomName }}</div>
                                    <div class="small text-muted">ความจุ: {{ $room->capacity }}</div>
                                    <div class="small text-muted text-truncate" title="{{ $room->description }}">
                                        {{ $room->description }}
                                    </div>
                                    <div class="small mt-1 overview-status-text">
                                        {{-- จะอัปเดตด้วย JS ตามวันที่/เวลา --}}
                                    </div>
                                </button>
                            @else
                                <div class="card h-100">
                                    <div class="card-body p-2">
                                        <div class="fw-bold">{{ $roomName }}</div>
                                        <div class="small text-danger">ไม่พบข้อมูลห้องในระบบ</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        const overviewMaxDateForUser = "{{ now()->addDays(365)->toDateString() }}"; // แค่กันเลือกอนาคตไกลเกินไป

        let overviewDate = "";
        let overviewSlot = "";

        const overviewCalendar = flatpickr("#rooms_overview_calendar", {
            dateFormat: "Y-m-d",
            minDate: "today",
            maxDate: overviewMaxDateForUser,
            defaultDate: "{{ now()->toDateString() }}",
            onChange: function(selectedDates, dateStr) {
                overviewDate = dateStr;
                refreshRoomsOverview();
            }
        });

        document.querySelectorAll('.overview-slot-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                overviewSlot = this.getAttribute('data-slot');

                document.querySelectorAll('.overview-slot-btn').forEach(b => {
                    b.classList.remove('btn-secondary', 'text-white');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-secondary', 'text-white');

                refreshRoomsOverview();
            });
        });

        async function fetchOverviewUnavailable(date, slot) {
            const url = `{{ route('booking_availability') }}?date=${encodeURIComponent(date)}&slot=${encodeURIComponent(slot)}`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json();
            return (json.bookedRoomIds || []);
        }

        async function refreshRoomsOverview() {
            if (!overviewDate || !overviewSlot) {
                document.querySelectorAll('.rooms-overview-card').forEach(btn => {
                    btn.classList.remove('btn-success', 'btn-danger');
                    btn.classList.add('btn-outline-secondary');
                    const text = btn.querySelector('.overview-status-text');
                    if (text) text.textContent = 'เลือกวันที่และเวลาเพื่อดูสถานะ';
                });
                ['2','3','4','5'].forEach(f => {
                    const el = document.getElementById(`overview-floor-${f}`);
                    if (el) el.textContent = 'ทั้งหมด 6 ห้อง';
                });
                return;
            }

            const unavailableIds = new Set(
                (await fetchOverviewUnavailable(overviewDate, overviewSlot)).map(String)
            );

            const summary = { '2': {a:0,u:0}, '3': {a:0,u:0}, '4': {a:0,u:0}, '5': {a:0,u:0} };

            document.querySelectorAll('.rooms-overview-card').forEach(btn => {
                const id = String(btn.getAttribute('data-room-id'));
                const floor = btn.getAttribute('data-floor');
                const text = btn.querySelector('.overview-status-text');

                if (unavailableIds.has(id)) {
                    btn.classList.remove('btn-success', 'btn-outline-secondary');
                    btn.classList.add('btn-danger');
                    if (text) text.textContent = 'ไม่ว่าง';
                    if (summary[floor]) summary[floor].u += 1;
                } else {
                    btn.classList.remove('btn-danger', 'btn-outline-secondary');
                    btn.classList.add('btn-success');
                    if (text) text.textContent = 'ว่าง';
                    if (summary[floor]) summary[floor].a += 1;
                }
            });

            Object.keys(summary).forEach(f => {
                const el = document.getElementById(`overview-floor-${f}`);
                if (el) {
                    el.textContent = `ว่าง ${summary[f].a} ห้อง / ไม่ว่าง ${summary[f].u} ห้อง`;
                }
            });
        }

        // ค่าเริ่มต้น: วันนี้ + slot แรก
        (function initOverview() {
            overviewDate = "{{ now()->toDateString() }}";
            const firstSlotBtn = document.querySelector('.overview-slot-btn[data-slot=\"slot1\"]');
            if (firstSlotBtn) firstSlotBtn.click();
        })();
    </script>
@endsection
