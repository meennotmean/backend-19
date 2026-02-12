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

    <style>
        .slot-active {
            background-color: #2d940d !important;
            color: white !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-0">ห้องเรียน</h2>
            <small class="text-muted d-block">แสดงตามชั้น และสถานะว่าง/ไม่ว่างตามวันที่ + ช่วงเวลา</small>
        </div>
        <div class="d-flex align-items-end gap-2 flex-wrap">
            <div style="min-width: 150px;">
                <label class="form-label mb-1">ประเภท</label>
                <select id="overview_booking_type" class="form-select form-select-sm">
                    <option value="single">จองรายวัน (เลือกหลายช่วงเวลาได้)</option>
                    <option value="group">จองแบบกลุ่ม (หลายวัน)</option>
                </select>
            </div>
            <div style="min-width: 190px;">
                <label class="form-label mb-1">วันที่</label>
                <input type="text" id="rooms_overview_calendar" class="form-control form-control-sm"
                    placeholder="เลือกวันที่">
            </div>
            <div>
                <label class="form-label mb-1 d-block">เวลา</label>
                <div class="btn-group btn-group-sm" role="group" aria-label="Time slots">
                    <button type="button" class="btn btn-outline-secondary overview-slot-btn"
                        data-slot="slot1">08:30-12:30</button>
                    <button type="button" class="btn btn-outline-secondary overview-slot-btn"
                        data-slot="slot2">13:30-17:30</button>
                    <button type="button" class="btn btn-outline-secondary overview-slot-btn"
                        data-slot="slot3">18:30-20:00</button>
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
                    <small class="text-muted" id="overview-floor-{{ $floor }}">ทั้งหมด {{ count($names) }}
                        ห้อง</small>
                </div>

                <div class="row g-2">
                    @foreach ($names as $roomName)
                        @php
                            $room = $roomsByName->get($roomName);
                        @endphp
                        <div class="col-6 col-md-4 col-lg-2">
                            @if ($room)
                                <button type="button" class="btn w-100 rooms-overview-card"
                                    data-room-id="{{ $room->id }}" data-floor="{{ $floor }}">
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
        const overviewMaxDateForUser = "{{ now()->addDays(365)->toDateString() }}";

        let overviewDates = ["{{ now()->toDateString() }}"]; // One date by default
        let overviewSlots = new Set(); // store multiple slots
        let overviewType = "single";

        // Type selector
        const typeSelector = document.getElementById('overview_booking_type');

        // Determine initial mode
        const initialMode = typeSelector.value === 'group' ? 'multiple' : 'single';

        const overviewCalendar = flatpickr("#rooms_overview_calendar", {
            mode: initialMode,
            dateFormat: "Y-m-d",
            minDate: "today",
            maxDate: overviewMaxDateForUser,
            defaultDate: ["{{ now()->toDateString() }}"],
            onChange: function(selectedDates, dateStr) {
                if (overviewType === 'single') {
                    // Force single date if in single mode (though we might switch picker mode)
                    if (selectedDates.length > 1) {
                        // Keep only the last selected
                        const lastDate = selectedDates[selectedDates.length - 1];
                        overviewCalendar.setDate([lastDate]);
                        overviewDates = [overviewCalendar.formatDate(lastDate, "Y-m-d")];
                    } else {
                        overviewDates = selectedDates.map(d => overviewCalendar.formatDate(d, "Y-m-d"));
                    }
                } else {
                    // Group: Max 3 days
                    if (selectedDates.length > 3) {
                        alert("จองแบบกลุ่มเลือกได้ไม่เกิน 3 วันครับ");
                        // Revert to first 3
                        const keepDates = selectedDates.slice(0, 3);
                        overviewCalendar.setDate(keepDates);
                        overviewDates = keepDates.map(d => overviewCalendar.formatDate(d, "Y-m-d"));
                    } else {
                        overviewDates = selectedDates.map(d => overviewCalendar.formatDate(d, "Y-m-d"));
                    }
                }
                refreshRoomsOverview();
            }
        });

        typeSelector.addEventListener('change', function() {
            overviewType = this.value;
            // Clear current selection to avoid conflicts logic
            overviewCalendar.clear();
            overviewDates = [];

            if (overviewType === 'single') {
                overviewCalendar.set('mode', 'single');
                // Reset to today
                overviewCalendar.setDate("{{ now()->toDateString() }}", true);
                overviewDates = ["{{ now()->toDateString() }}"];
            } else {
                overviewCalendar.set('mode', 'multiple');
                overviewCalendar.setDate("{{ now()->toDateString() }}", true);
                overviewDates = ["{{ now()->toDateString() }}"];
            }
            refreshRoomsOverview();
        });

        document.querySelectorAll('.overview-slot-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const slot = this.getAttribute('data-slot');

                if (overviewSlots.has(slot)) {
                    // Prevent deselecting if it's the last one
                    if (overviewSlots.size === 1) {
                        alert("ต้องเลือกช่วงเวลาอย่างน้อย 1 ช่วงครับ");
                        return;
                    }
                    overviewSlots.delete(slot);
                    this.classList.remove('slot-active');
                } else {
                    overviewSlots.add(slot);
                    this.classList.add('slot-active');
                }

                refreshRoomsOverview();
            });
        });

        async function fetchOverviewUnavailable(datesArray, slotsArray) {
            const params = new URLSearchParams();
            if (datesArray.length > 0) {
                datesArray.forEach(d => params.append('dates[]', d));
            } else {
                return [];
            }
            slotsArray.forEach(s => params.append('slots[]', s));

            const url = `{{ route('booking_availability') }}?${params.toString()}`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await res.json();
            return (json.bookedRoomIds || []);
        }

        async function refreshRoomsOverview() {
            if (overviewDates.length === 0 || overviewSlots.size === 0) {
                document.querySelectorAll('.rooms-overview-card').forEach(btn => {
                    btn.classList.remove('btn-success', 'btn-danger');
                    btn.classList.add('btn-outline-secondary');

                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);

                    const text = newBtn.querySelector('.overview-status-text');
                    if (text) text.textContent = 'เลือกวันที่และเวลา';
                });
                ['2', '3', '4', '5'].forEach(f => {
                    const el = document.getElementById(`overview-floor-${f}`);
                    if (el) el.textContent = 'ทั้งหมด 6 ห้อง';
                });
                return;
            }

            const slotsArray = Array.from(overviewSlots);
            const unavailableIds = new Set(
                (await fetchOverviewUnavailable(overviewDates, slotsArray)).map(String)
            );

            const summary = {
                '2': {
                    a: 0,
                    u: 0
                },
                '3': {
                    a: 0,
                    u: 0
                },
                '4': {
                    a: 0,
                    u: 0
                },
                '5': {
                    a: 0,
                    u: 0
                }
            };

            document.querySelectorAll('.rooms-overview-card').forEach(btn => {
                const id = String(btn.getAttribute('data-room-id'));
                const floor = btn.getAttribute('data-floor');
                const text = btn.querySelector('.overview-status-text');

                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);

                if (unavailableIds.has(id)) {
                    newBtn.classList.remove('btn-success', 'btn-outline-secondary');
                    newBtn.classList.add('btn-danger');
                    newBtn.disabled = true;
                    if (text) text.textContent = 'ไม่ว่าง';
                    if (summary[floor]) summary[floor].u += 1;
                } else {
                    newBtn.classList.remove('btn-danger', 'btn-outline-secondary');
                    newBtn.classList.add('btn-success');
                    newBtn.disabled = false;
                    if (text) text.textContent = 'ว่าง (คลิกเพื่อจอง)';
                    if (summary[floor]) summary[floor].a += 1;

                    newBtn.addEventListener('click', function() {
                        const params = new URLSearchParams();
                        params.append('room_id', id);
                        overviewDates.forEach(d => params.append('dates[]', d));
                        params.append('booking_type', overviewType);
                        slotsArray.forEach(s => params.append('slots[]', s));

                        window.location.href = `{{ route('booking') }}?${params.toString()}`;
                    });
                }
            });

            Object.keys(summary).forEach(f => {
                const el = document.getElementById(`overview-floor-${f}`);
                if (el) {
                    el.textContent = `ว่าง ${summary[f].a} ห้อง / ไม่ว่าง ${summary[f].u} ห้อง`;
                }
            });
        }

        (function initOverview() {
            // Force slot 1 on load
            const firstSlotBtn = document.querySelector('.overview-slot-btn[data-slot="slot1"]');
            if (firstSlotBtn) {
                overviewSlots.add('slot1');
                firstSlotBtn.classList.add('slot-active');
                // No need to click(), just set state
            }
            refreshRoomsOverview();
        })();
    </script>
@endsection
