@extends('layouts.app')
@section('title', 'Edit Staff')

@section('content')
    <h1>StaffEdit</h1>
    <hr>
    <form method="POST" action="{{ route('admin_staff_update', $staff->id) }}">
        @csrf
        <label for="name">Staff Name:</label>
        <input type="text" id="name" name="name" value="{{ $staff->name }}" required>
        <br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="{{ $staff->email }}" required>
        <br><br>
        <input type="submit" value="Update">
    </form>
@endsection
