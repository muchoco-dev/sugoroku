@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>参加者募集中の部屋</h1>
            <div class="card-columns">
                @foreach ($rooms as $room)
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $room['name'] }}</h5>
                            <p class="card-text"><i class="fas fa-user mr-2"></i>{{ $room['member_count'].'/'.$room['max_member_count'] }}</p>
                            @if ($room['member_count'] < $room['max_member_count'])
                            <a href="/room/{{ $room['uname'] }}/join" class="card-link">いますぐ参加</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <button @click="showCreateForm = true" class="btn btn-primary">部屋を作成する</button>
            <create-room-modal v-if="showCreateForm" @close="showCreateForm = false" token="{{ $pusher_token }}"></create-room-modal>
        </div>
    </div>
</div>
@endsection
