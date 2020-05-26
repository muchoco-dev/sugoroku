@extends('layouts.app')

@section('content')
  <sugoroku
    :board="{{ $room->board }}"
    :room="{{ $room }}"
    :members="{{ $room->users }}"
    :spaces="{{ json_encode($spaces) }}"
    :auth_id="{{ Auth::id() }}"
    :room_status_open="{{ config('const.room_status_open') }}"
    token="{{ $pusher_token }}"></sugoroku>
  </div>
@endsection
