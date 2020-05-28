@extends('layouts.app')

@section('content')
  <sugoroku
    :board="{{ $room->board }}"
    :room="{{ $room }}"
    :members="{{ $room->users }}"
    :spaces="{{ json_encode($spaces) }}"
    :auth_id="{{ Auth::id() }}"
    :const="{{ json_encode(config('const')) }}"
    token="{{ $pusher_token }}"></sugoroku>
  </div>
@endsection
