@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>部屋一覧ページ</h1>
            <button id ="showCreateForm" @click="showCreateForm = true">部屋を作成する</button>
            <createRoomModal-component v-if="showCreateForm" @close="showCreateForm = false"></createRoomModal-component>
        </div>
    </div>
</div>
@endsection
