@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>部屋一覧ページ</h1>
            <div class="card-columns">
                @foreach ($rooms as $room)
                    <div class="card">
                        <p>部屋名：{{ $room['name'] }}</p>
                        <p>人数：{{ $room['member_count'].'/'.$room['max_member_count'] }}</p>
                    </div>
                @endforeach
            </div>
            <button>部屋を作成する</button>
        </div>
    </div>
</div>
@endsection
