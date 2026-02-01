@extends('layouts.app')
@section('title', 'Rooms')

@section('content')
    <h1>Rooms</h1>
    <hr>
    @if (count($rooms) > 0)
        <h2 class="text text-center py-2">All Rooms</h2>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th scope="col">Rooms</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Type</th>
                    <th scope="col">Description</th>
                    <th scope="col">Status</th>
                    <th scope="col">Edit</th>
                    <th scope="col">Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rooms as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->capacity }}</td>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->description }}</td>
                        <td>
                            @if ($item->status == true)
                                <a href="{{ route('change', $item->id) }}" class="btn btn-success">Avalible</a>
                            @else
                                <a href="{{ route('change', $item->id) }}" class="btn btn-secondary">No
                                    Avalible</a>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('edit', $item->id) }}" class="btn btn-warning">Edit</a>
                        </td>
                        <td>
                            <a href="{{ route('delete', $item->id) }}" class="btn btn-danger"
                                onclick="return confirm('Delete {{ $item->name }} or No?')">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('create') }}" class="btn btn-primary">สร้างห้องเพิ่มเติม</a>
        {{ $rooms->links() }}
    @else
        <h2 class="text text-center py-2">No Rooms</h2>
        <a href="{{ route('create') }}" class="btn btn-primary">สร้างห้องเพิ่มเติม</a>
    @endif
@endsection
