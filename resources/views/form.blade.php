@extends('layouts.app')
@section('title', 'Create Room')

@section('content')
    <h1>RoomsCreate</h1>
    <hr>
    <form method="POST" action="/insert">
        @csrf
        <label for="name">Room Name:</label>
        <input type="text" id="name" name="name" required>
        <br><br>
        <label for="description">Description:</label>
        <textarea type="text" id="description" name="description" required></textarea>
        <br><br>
        <label for="capacity">Capacity:</label>
        <input type="number" id="capacity" name="capacity" required>
        <br><br>
        <label for="room_type_id">Room Type:</label>
        <select id="room_type_id" name="room_type_id" required>
            <option value="">-- Select Type --</option>
            @foreach ($roomTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
        <br><br>

        <input type="submit" value="Create Room">
    </form>
@endsection
