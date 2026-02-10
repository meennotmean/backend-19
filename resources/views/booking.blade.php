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

        {{-- หน้า booking แบบการ์ด แบ่งตามชั้น --}}
        @php
            $floorRooms = [
                '2' => ['19201', '19202', '19203', '19204', '19205', '19206'],
                '3' => ['19301', '19302', '19303', '19304', '19305', '19306'],
                '4' => ['19401', '19402', '19403', '19404', '19405', '19406'],
                '5' => ['19501', '19502', '19503', '19504', '19505', '19506'],
            ];
            $allTargetRooms = collect($floorRooms)->flatten()->toArray();
        @endphp

        <form action="{{ route('booking_store') }}" method="POST">
            @csrf

            {{-- เลือกประเภทการจอง --}}
            <div class="mb-3">
                <label class="form-label d-block">ประเภทการจอง</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="booking_type" id="booking_type_single" value="single"
                        {{ old('booking_type', 'single') === 'single' ? 'checked' : '' }}>
                    <label class="form-check-label" for="booking_type_single">จองแบบวันต่อวัน</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="booking_type" id="booking_type_group" value="group"
                        {{ old('booking_type') === 'group' ? 'checked' : '' }}>
                    <label class="form-check-label" for="booking_type_group">จองแบบกลุ่ม (ห้องเดียวกัน เวลาเดียวกัน หลายวัน สูงสุด 3 วัน)</label>
                </div>
            </div>

            {{-- room_id จากปุ่มในตาราง --}}
            <input type="hidden" name="room_id" id="room_id" value="{{ old('room_id') }}">
            <input type="hidden" name="time_slot" id="time_slot" value="{{ old('time_slot') }}">

            {{-- แถบเลือก วัน + เวลา ให้อยู่ใกล้กัน --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-bold">เลือกวันและเวลา</div>
                            <small class="text-muted">
                                @if (auth()->user()->role === 'admin' || auth()->user()->role === 'staff')
                                    admin/staff เลือกวันได้ไม่จำกัด (ห้ามย้อนหลัง)
                                @else
                                    จองล่วงหน้าได้ไม่เกิน {{ $maxAdvanceDays ?? 14 }} วัน
                                @endif
                            </small>
                        </div>
                        <div class="d-flex align-items-end gap-2 flex-wrap justify-content-end">
                            <div style="min-width: 190px;">
                                <label class="form-label mb-1">วันที่</label>
                                <input type="text" id="booking_calendar" class="form-control form-control-sm" placeholder="เลือกวันที่">
                                <input type="hidden" name="booking_date" id="booking_date" class="single-date-input" value="{{ old('booking_date') }}">
                            </div>
                            <div>
                                <label class="form-label mb-1 d-block">เวลา</label>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Time slots">
                                    <button type="button" class="btn btn-outline-secondary time-slot-btn" data-slot="slot1">08:30-12:30</button>
                                    <button type="button" class="btn btn-outline-secondary time-slot-btn" data-slot="slot2">13:30-17:30</button>
                                    <button type="button" class="btn btn-outline-secondary time-slot-btn" data-slot="slot3">18:30-20:00</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="fw-bold">สรุปที่เลือก</div>
                        <div class="text-muted small" id="selection_summary">ยังไม่ได้เลือกวันที่/เวลา</div>
                    </div>
                </div>
            </div>

            {{-- จองแบบกลุ่ม: เลือกหลายวัน (สูงสุด 3 วัน) --}}
            <div class="mb-3 group-dates-wrapper" style="display: none;">
                <label>วันที่ต้องการจอง (จองแบบกลุ่ม สูงสุด 3 วัน)</label>
                <small class="d-block text-muted mb-2">เลือกหลายวันแต่ต้องเป็นห้องเดียวกันและช่วงเวลาเดียวกัน</small>
                <input type="text" id="group_dates_picker" class="form-control mb-2" placeholder="คลิกเพื่อเลือกหลายวัน (สูงสุด 3 วัน)" readonly>
                {{-- ซ่อน input จริง 3 ช่องไว้ส่งไป backend --}}
                @for ($i = 0; $i < 3; $i++)
                    <input type="hidden" name="booking_dates[]" class="group-date-hidden" value="{{ old('booking_dates.' . $i) }}">
                @endfor
            </div>

            {{-- แสดงการ์ดห้องแยกตามชั้น + นับว่าง/ไม่ว่าง --}}
            <div class="mb-3">
                <div class="btn-group" role="group" aria-label="Floor filter">
                    <button type="button" class="btn btn-outline-primary floor-filter active" data-floor="2">ชั้น 2</button>
                    <button type="button" class="btn btn-outline-primary floor-filter" data-floor="3">ชั้น 3</button>
                    <button type="button" class="btn btn-outline-primary floor-filter" data-floor="4">ชั้น 4</button>
                    <button type="button" class="btn btn-outline-primary floor-filter" data-floor="5">ชั้น 5</button>
                </div>
            </div>

            @foreach ($floorRooms as $floor => $roomNames)
                <div class="card mb-3 floor-section" data-floor="{{ $floor }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold">ชั้น {{ $floor }}</div>
                                <small class="text-muted" id="floor-summary-{{ $floor }}">ว่าง - ห้อง / ไม่ว่าง - ห้อง</small>
                            </div>
                        </div>
                        <div class="row g-2 mt-1">
                            @foreach ($rooms as $room)
                                @if (in_array($room->name, $roomNames))
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <button type="button"
                                            class="btn w-100 room-book-card"
                                            data-room-id="{{ $room->id }}"
                                            data-room-name="{{ $room->name }}"
                                            data-floor="{{ $floor }}"
                                            disabled
                                        >
                                            {{ $room->name }}
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- ห้องที่เลือก + description --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="fw-bold">ห้องที่เลือก</div>
                    <div class="text-muted" id="room_selected_display">ยังไม่ได้เลือกห้อง</div>

                    <div class="mt-3 fw-bold">รายละเอียดห้อง (Description)</div>
                    <div class="alert alert-info mb-0" id="room_description_box" style="display:none;"></div>
                    <div class="text-muted" id="room_description_empty">ยังไม่มีรายละเอียด (admin/staff สามารถเพิ่มได้ในหน้าจัดการห้อง)</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">จองห้อง</button>

            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

            <script>
                // สคริปต์สำหรับปุ่มเลือกชั้น (ซ่อน/โชว์ section)
                document.querySelectorAll('.floor-filter').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const floor = this.getAttribute('data-floor');

                        document.querySelectorAll('.floor-filter').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');

                        document.querySelectorAll('.floor-section').forEach(sec => {
                            sec.style.display = (sec.getAttribute('data-floor') === floor) ? '' : 'none';
                        })
                    });
                });

                // เรียกครั้งแรกให้แสดงเฉพาะชั้น 2
                (function() {
                    const defaultFloorBtn = document.querySelector('.floor-filter[data-floor="2"]');
                    if (defaultFloorBtn) {
                        defaultFloorBtn.click();
                    }
                })();

                // สคริปต์สำหรับปุ่มช่วงเวลา
                document.querySelectorAll('.time-slot-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const slot = this.getAttribute('data-slot');
                        document.getElementById('time_slot').value = slot;

                        document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('btn-secondary', 'text-white'));
                        document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.add('btn-outline-secondary'));

                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-secondary', 'text-white');

                        updateSelectionSummary();
                        refreshAvailability();
                    });
                });

                // สคริปต์สำหรับสลับระหว่างจองแบบวันต่อวัน กับแบบกลุ่ม
                function toggleBookingType() {
                    const isGroup = document.getElementById('booking_type_group').checked;
                    const groupWrapper = document.querySelector('.group-dates-wrapper');
                    const singleDateInput = document.querySelector('.single-date-input');

                    if (isGroup) {
                        groupWrapper.style.display = 'block';
                        // ซ่อนการเลือกวันเดี่ยวในแถบ
                        document.getElementById('booking_calendar').closest('div').style.display = 'none';
                    } else {
                        groupWrapper.style.display = 'none';
                        document.getElementById('booking_calendar').closest('div').style.display = 'block';
                    }

                    // reset room selection when switching type (avoid confusion)
                    clearSelectedRoom();
                    updateSelectionSummary();
                    refreshAvailability();
                }

                document.getElementById('booking_type_single').addEventListener('change', toggleBookingType);
                document.getElementById('booking_type_group').addEventListener('change', toggleBookingType);

                // เรียกครั้งแรกตามค่าเก่า (old input)
                toggleBookingType();

                // -----------------------
                // ปฏิทิน (flatpickr)
                // -----------------------
                const userRole = "{{ auth()->user()->role }}";
                const maxDateForUser = "{{ $maxBookingDate ?? now()->addDays(14)->toDateString() }}";
                const limitMaxDate = !(userRole === 'admin' || userRole === 'staff');

                const calendar = flatpickr("#booking_calendar", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    maxDate: limitMaxDate ? maxDateForUser : null,
                    defaultDate: "{{ old('booking_date') ?: '' }}",
                    onChange: function(selectedDates, dateStr) {
                        // sync to single booking_date input
                        document.getElementById('booking_date').value = dateStr;
                        updateSelectionSummary();
                        refreshAvailability();
                    }
                });

                const groupPicker = flatpickr("#group_dates_picker", {
                    mode: "multiple",
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    maxDate: limitMaxDate ? maxDateForUser : null,
                    onChange: function(selectedDates) {
                        // enforce max 3 dates
                        if (selectedDates.length > 3) {
                            selectedDates = selectedDates.slice(0, 3);
                            groupPicker.setDate(selectedDates, true);
                        }

                        const dates = selectedDates.map(d => groupPicker.formatDate(d, "Y-m-d"));
                        // write into hidden inputs (3 ช่อง)
                        const hiddenInputs = document.querySelectorAll('.group-date-hidden');
                        hiddenInputs.forEach((el, idx) => {
                            el.value = dates[idx] || '';
                        });
                        updateSelectionSummary();
                        refreshAvailability();
                    }
                });

                // โหลดค่า group_dates เดิม (ถ้ามี)
                (function hydrateOldGroupDates() {
                    const hiddenInputs = Array.from(document.querySelectorAll('.group-date-hidden'));
                    const oldDates = hiddenInputs.map(i => i.value).filter(Boolean);
                    if (oldDates.length) {
                        groupPicker.setDate(oldDates, true);
                    }
                })();

                // -----------------------
                // เลือกห้องด้วยปุ่ม
                // -----------------------
                function clearSelectedRoom() {
                    document.getElementById('room_id').value = '';
                    document.getElementById('room_selected_display').textContent = 'ยังไม่ได้เลือกห้อง';

                    document.getElementById('room_description_box').style.display = 'none';
                    document.getElementById('room_description_box').textContent = '';
                    document.getElementById('room_description_empty').style.display = 'block';

                    document.querySelectorAll('.room-book-card').forEach(b => {
                        b.classList.remove('btn-primary', 'text-white');
                    });
                }

                document.querySelectorAll('.room-book-card').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (this.disabled) return;

                        const roomId = this.getAttribute('data-room-id');
                        const roomName = this.getAttribute('data-room-name');

                        clearSelectedRoom();
                        document.getElementById('room_id').value = roomId;
                        document.getElementById('room_selected_display').textContent = roomName;

                        this.classList.remove('btn-success', 'btn-outline-secondary', 'btn-danger');
                        this.classList.add('btn-primary', 'text-white');

                        // ดึง description แล้วแสดงด้านล่าง
                        fetch(`/room-details/${roomId}`)
                            .then(r => r.json())
                            .then(data => {
                                const box = document.getElementById('room_description_box');
                                const empty = document.getElementById('room_description_empty');
                                const desc = (data && data.description) ? String(data.description).trim() : '';

                                if (desc) {
                                    box.textContent = desc;
                                    box.style.display = 'block';
                                    empty.style.display = 'none';
                                } else {
                                    box.style.display = 'none';
                                    box.textContent = '';
                                    empty.style.display = 'block';
                                }
                            })
                    });
                });

                // -----------------------
                // Availability (สีแดง disabled ถ้าไม่ว่าง)
                // -----------------------
                function getSelectedSlot() {
                    return document.getElementById('time_slot').value;
                }

                function isGroupType() {
                    return document.getElementById('booking_type_group').checked;
                }

                function getSelectedDates() {
                    if (isGroupType()) {
                        return Array.from(document.querySelectorAll('.group-date-hidden'))
                            .map(i => i.value)
                            .filter(Boolean);
                    }
                    const d = document.getElementById('booking_date').value;
                    return d ? [d] : [];
                }

                function updateSelectionSummary() {
                    const slot = getSelectedSlot();
                    const dates = getSelectedDates();
                    const summaryEl = document.getElementById('selection_summary');

                    const slotLabelMap = {
                        'slot1': '08:30-12:30',
                        'slot2': '13:30-17:30',
                        'slot3': '18:30-20:00',
                    };

                    if (!slot && dates.length === 0) {
                        summaryEl.textContent = 'ยังไม่ได้เลือกวันที่/เวลา';
                        return;
                    }

                    if (!slot || dates.length === 0) {
                        summaryEl.textContent = 'กรุณาเลือกให้ครบทั้ง “วันที่” และ “เวลา”';
                        return;
                    }

                    if (isGroupType()) {
                        summaryEl.textContent = `วัน: ${dates.join(', ')} | เวลา: ${slotLabelMap[slot] || slot}`;
                    } else {
                        summaryEl.textContent = `วัน: ${dates[0]} | เวลา: ${slotLabelMap[slot] || slot}`;
                    }
                }

                async function fetchBookedRoomIds(date, slot) {
                    const url = `{{ route('booking_availability') }}?date=${encodeURIComponent(date)}&slot=${encodeURIComponent(slot)}`;
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const json = await res.json();
                    return (json.bookedRoomIds || []);
                }

                async function refreshAvailability() {
                    const slot = getSelectedSlot();
                    const dates = getSelectedDates();

                    // ต้องเลือก slot และ date อย่างน้อย 1 วันก่อน ถึงจะเปิดให้กดเลือกห้อง
                    if (!slot || dates.length === 0) {
                        document.querySelectorAll('.room-book-card').forEach(btn => {
                            btn.disabled = true;
                            btn.classList.remove('btn-success', 'btn-danger', 'btn-primary', 'text-white');
                            btn.classList.add('btn-outline-secondary');
                            btn.textContent = btn.getAttribute('data-room-name');
                        });
                        // reset floor summary
                        ['2','3','4','5'].forEach(f => {
                            const el = document.getElementById(`floor-summary-${f}`);
                            if (el) el.textContent = 'ว่าง - ห้อง / ไม่ว่าง - ห้อง';
                        });
                        return;
                    }

                    // union bookedRoomIds ของทุกวัน (สำหรับจองแบบกลุ่ม: ถ้าวันไหนไม่ว่าง = ไม่ว่าง)
                    const bookedSet = new Set();
                    await Promise.all(dates.map(async (date) => {
                        const bookedIds = await fetchBookedRoomIds(date, slot);
                        bookedIds.forEach(id => bookedSet.add(String(id)));
                    }));

                    // สรุปต่อชั้น
                    const summary = { '2': {a:0,u:0}, '3': {a:0,u:0}, '4': {a:0,u:0}, '5': {a:0,u:0} };

                    document.querySelectorAll('.room-book-card').forEach(btn => {
                        const roomId = btn.getAttribute('data-room-id');
                        const floor = btn.getAttribute('data-floor');

                        // ถ้าห้องถูกจองแล้ว -> แดง disabled
                        if (bookedSet.has(String(roomId))) {
                            btn.disabled = true;
                            btn.classList.remove('btn-outline-secondary', 'btn-success', 'btn-primary', 'text-white');
                            btn.classList.add('btn-danger');
                            btn.textContent = btn.getAttribute('data-room-name');
                            if (summary[floor]) summary[floor].u += 1;

                            // ถ้า user เคยเลือกห้องนี้ไว้ ให้เคลียร์
                            if (document.getElementById('room_id').value === String(roomId)) {
                                clearSelectedRoom();
                            }
                            return;
                        }

                        // ว่าง -> เขียว กดได้
                        btn.disabled = false;
                        btn.classList.remove('btn-outline-secondary', 'btn-danger');
                        // อย่าทับกรณีถูกเลือกอยู่ (primary)
                        if (document.getElementById('room_id').value === String(roomId)) {
                            btn.classList.add('btn-primary', 'text-white');
                            btn.textContent = btn.getAttribute('data-room-name');
                        } else {
                            btn.classList.remove('btn-primary', 'text-white');
                            btn.classList.add('btn-success');
                            btn.textContent = btn.getAttribute('data-room-name');
                        }
                        if (summary[floor]) summary[floor].a += 1;
                    });

                    Object.keys(summary).forEach(f => {
                        const el = document.getElementById(`floor-summary-${f}`);
                        if (!el) return;
                        el.textContent = `ว่าง ${summary[f].a} ห้อง / ไม่ว่าง ${summary[f].u} ห้อง`;
                    });
                }

                // trigger initial availability if old inputs exist
                (function initialRefresh() {
                    // sync old booking_date into calendar + input
                    const oldSingle = "{{ old('booking_date') ?: '' }}";
                    if (oldSingle) {
                        document.getElementById('booking_date').value = oldSingle;
                        calendar.setDate(oldSingle, true);
                    }
                    updateSelectionSummary();
                    refreshAvailability();
                })();
            </script>
        @endsection
