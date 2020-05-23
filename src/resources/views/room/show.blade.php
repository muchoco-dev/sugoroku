@extends('layouts.app')

@section('content')
  <div class="row">
    <div class="col-9">
      <sugoroku :board="{{ $room->board }}" :spaces="{{ json_encode($spaces) }}" ></sugoroku>
    </div>
    <div class="col-2">
        <!-- ここにメンバー一覧 -->
    </div>
  </div>
  <div class="row">
    <div class="col-9">
      <logs :room_id="{{ $room->id }}"></logs>
    </div>
    <div class="col-2">
      <action
        :room="{{ $room }}"
        :user_id="{{ Auth::id() }}"
        :room_status_open="{{ config('const.room_status_open') }}"
        token="{{ $pusher_token }}"></action>
    </div>
  </div>
@endsection
