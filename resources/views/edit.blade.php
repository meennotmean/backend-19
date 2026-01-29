@extends('layouts.app')
@section('title', 'Edit Room')

@section('content')
    <h1>RoomEdit</h1>
    <hr>
    <form method="POST" action="{{ route('update', $room->id) }}">
        @csrf
        <label for="name">Room Name:</label>
        <input type="text" id="name" name="name" value="{{ $room->name }}" required>
        <br><br>
        <label for="description">Description:</label>
        <textarea type="text" id="description" name="description" required>{{ $room->description }}</textarea>
        <br><br>
        <label for="capacity">Capacity:</label>
        <input type="number" id="capacity" name="capacity" required>
        <br><br>
        <label for="type">Room Type:</label>
        <input type="text" id="type" name="type" required>
        <br><br>

        <input type="submit" value="Update Room">
    </form>
@endsection
