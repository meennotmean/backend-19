<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffManagementController extends Controller
{


    public function index()
    {
        $staffs = User::where('role', 'staff')->paginate(5);
        return view('admin_staff_index', compact('staffs'));
    }


    public function create()
    {
        return view('admin_staff_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|unique:users,userid',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        User::create([
            'userid' => $request->userid,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
        ]);

        return redirect()->route('admin_staff_index')->with('success', 'เพิ่ม Staff เรียบร้อย!');
    }

    function update_staff(Request $request, $id)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
            ],
            [
                'name.required' => 'กรุณาใส่ชื่อบุคลากร',
                'name.max' => 'ชื่อบุคลากรต้องไม่เกิน 50 ตัวอักษร',
                'email.required' => 'กรุณาใส่อีเมล',
                'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
                'email.unique' => 'อีเมลนี้ถูกใช้แล้ว',
            ]
        );
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];
        User::find($id)->update($data);
        return redirect()->route('admin_staff_index')->with('success', 'อัปเดตข้อมูล Staff เรียบร้อยแล้ว');
    }


    public function delete_staff($id)
    {
        $staff = User::find($id);
        $staff->delete();
        return back()->with('success', 'ลบข้อมูล Staff สำเร็จ');
    }

    function edit_staff($id)
    {
        $staff = User::find($id);
        return view('admin_staff_edit', compact('staff'));
    }
}
