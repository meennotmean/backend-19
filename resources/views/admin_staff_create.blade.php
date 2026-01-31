@extends('layouts.app')
@section('title', 'Create Staff')

@section('content')
    <h1>StaffCreate</h1>
    <hr>
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('admin_staff_store') }}">
        @csrf
        <label for="userid">User ID:</label>
        <input type="text" id="userid" name="userid" required>
        <br><br>
        <label for="name">Staff Name:</label>
        <input type="text" id="name" name="name" required>
        <br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>

        <input type="submit" value="Create Staff">
    </form>
@endsection
