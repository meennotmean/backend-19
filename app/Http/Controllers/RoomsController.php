<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Room;

use App\Models\RoomType;

class RoomsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    function rooms()
    {
        $rooms = Room::with('roomType')->get();
        return view('rooms', compact('rooms'));
    }

    function create()
    {
        $roomTypes = RoomType::all();
        return view('form', compact('roomTypes'));
    }
    function booking()
    {
        return view('booking');
    }
    function profile()
    {
        return view('profile');
    }
    function manage_staff()
    {
        return view('manage_staff');
    }
    function manage_room()
    {
        $rooms = Room::with('roomType')->get();
        return view('manage_room', compact('rooms'));
    }

    function insert(Request $request)
    {

        $request->validate([
            'name'        => 'required|max:50',
            'description' => 'required',
            'capacity'    => 'required|integer',
            'room_type_id' => 'required|exists:room_types,id',
        ], [
            'name.required'        => 'กรุณากรอกชื่อห้องเรียน',
            'description.required' => 'กรุณากรอกคำอธิบายห้องเรียน',
            'capacity.integer'     => 'จำนวนความจุต้องเป็นตัวเลขเท่านั้น',
            'room_type_id.required' => 'กรุณาเลือกประเภทห้องเรียน',
        ]);


        $data = [
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
            'capacity'    => $request->input('capacity'),
            'room_type_id' => $request->input('room_type_id'),
        ];


        Room::create($data);

        return redirect('manage_room');
    }
    function change($id)
    {
        $room = Room::find($id);
        $data = [
            'status' => !$room->status
        ];
        Room::find($id)->update($data);
        return redirect('/manage_room');
    }
    function edit($id)
    {
        $room = Room::find($id);
        $roomTypes = RoomType::all();
        return view('edit', compact('room', 'roomTypes'));
    }
    function delete($id)
    {
        Room::find($id)->delete();
        return redirect()->back();
    }
    function update(Request $request, $id)
    {
        $request->validate(
            [
                'name' => 'required|max:50',
                'description' => 'required',
                'capacity' => 'required|integer',
                'room_type_id' => 'required|exists:room_types,id',
            ],
            [
                'name.required' => 'กรุณาใส่ชื่อห้องเรียน',
                'name.max' => 'ชื่อห้องเรียนต้องไม่เกิน 50 ตัวอักษร',
                'description.required' => 'กรุณาใส่คำอธิบายห้องเรียน',
                'capacity.required' => 'กรุณาใส่จำนวนความจุ',
                'capacity.integer' => 'จำนวนความจุต้องเป็นตัวเลขเท่านั้น',
                'room_type_id.required' => 'กรุณาเลือกประเภทห้องเรียน'
            ]
        );
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity,
            'room_type_id' => $request->room_type_id
        ];
        Room::find($id)->update($data);
        return redirect('rooms');
    }
}
