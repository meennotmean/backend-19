@extends('layouts.app')
@section('title', 'Staffs')

@section('content')
    <h1>Staffs</h1>
    <hr>
    @if (count($staffs) > 0)
        <h2 class="text text-center py-2">All Staffs</h2>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th scope="col">Staffs</th>
                    <th scope="col">Email</th>
                    <th scope="col">Userid</th>
                    <th scope="col">Edit</th>
                    <th scope="col">Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($staffs as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->userid }}</td>
                        <td>
                            <a href="{{ route('admin_staff_edit', $item->id) }}" class="btn btn-warning">Edit</a>
                        </td>
                        <td>
                            <a href="{{ route('admin_staff_delete', $item->id) }}" class="btn btn-danger"
                                onclick="return confirm('Delete {{ $item->name }} or No?')">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('admin_staff_create') }}" class="btn btn-primary">เพิ่มบุคลากรใหม่</a>
        {{ $staffs->links() }}
    @else
        <h2 class="text text-center py-2">No Staffs Yet</h2>
        <hr>
        <a href="{{ route('admin_staff_create') }}" class="btn btn-primary">เพิ่มบุคลากรใหม่</a>
    @endif
@endsection
