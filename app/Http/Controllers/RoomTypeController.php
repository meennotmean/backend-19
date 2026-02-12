<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomType;

class RoomTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $types = RoomType::all();
        return view('manage_room_types', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:room_types,name|max:50',
        ], [
            'name.required' => 'กรุณากรอกชื่อประเภทห้อง',
            'name.unique' => 'ประเภทห้องนี้มีอยู่แล้ว',
        ]);

        RoomType::create($request->only('name'));
        return back()->with('success', 'เพิ่มประเภทห้องเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $type = RoomType::findOrFail($id);

        // Prevent deletion if in use
        if ($type->rooms()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบประเภทห้องนี้ได้เนื่องจากยังมีห้องที่ใช้งานอยู่');
        }

        $type->delete();
        return back()->with('success', 'ลบประเภทห้องเรียบร้อยแล้ว');
    }
}
