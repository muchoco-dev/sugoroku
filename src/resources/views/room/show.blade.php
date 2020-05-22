@extends('layouts.app')

@section('content')
    <sugoroku :board="{{ $room->board }}" :spaces="{{ json_encode($spaces) }}" ></sugoroku>
    <action
        :room="{{ $room }}"
        :user_id="{{ Auth::id() }}"
        :room_status_open="{{ config('const.room_status_open') }}"
        token="{{ $pusher_token }}"></action>
    <logs :room_id="{{ $room->id }}"></logs>
@endsection
