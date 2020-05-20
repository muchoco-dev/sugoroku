@extends('layouts.app')

@section('content')
    <sugoroku :board="{{ $room->board }}" spaces_str="{{ json_encode($spaces) }}" ></sugoroku>
@endsection
