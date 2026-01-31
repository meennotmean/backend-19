@extends('layouts.app')
@section('title', 'Profile')
@section('content')
    <h1>Profile</h1>
    <hr>
    <h3>ชื่อ-สกุล: {{ Auth::user()->name }}</h3>
    <h3>อีเมล: {{ Auth::user()->email }}</h3>
    <h3>บทบาท: {{ Auth::user()->role }}</h3>

@endsection
