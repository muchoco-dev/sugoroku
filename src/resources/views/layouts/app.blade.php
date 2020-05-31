<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Scripts -->
    <script src="https://unpkg.com/clipboard@2/dist/clipboard.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <script src="https://kit.fontawesome.com/c47c535577.js" crossorigin="anonymous"></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/room.css') }}" rel="stylesheet">
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    ログアウト
                                </a>

                                @if(Request::is('rooms') || Request::is('/'))
                                    <div id="updateUserModal" class="dropdown-item" data-toggle="modal" data-target="#updateUser"
                                         style="cursor: pointer">
                                        プロフィールの変更
                                    </div>
                                @endif

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>
</div>

{{-- プロフィール変更モーダル --}}
@auth
    <div class="modal fade" id="updateUser" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('user.update', auth()->user()->id) }}" method="post">
                    @csrf
                    @method('put')
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">プロフィールの変更</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-4 my-auto">
                                ニックネーム
                            </div>
                            <div class="col-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name', auth()->user()->name) }}" required
                                       autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 my-auto">
                                現在のパスワード
                            </div>
                            <div class="col-8">
                                <input id="old_password" type="password"
                                       class="form-control @error('old_password') is-invalid @enderror"
                                       name="old_password" value="{{ old('old_password') }}"
                                       autocomplete="old_password" autofocus>
                                @error('old_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 my-auto">
                                新しいパスワード
                            </div>
                            <div class="col-8">
                                <input id="new_password" type="password"
                                       class="form-control @error('new_password') is-invalid @enderror" name="new_password"
                                       value="{{ old('new_password') }}" autocomplete="new_password" autofocus>
                                @error('new_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-4 my-auto">
                                新しいパスワード<br>(確認用)
                            </div>
                            <div class="col-8">
                                <input id="new_password_confirmation" type="password"
                                       class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                       name="new_password_confirmation" value="{{ old('new_password_confirmation') }}"
                                       autocomplete="new_password_confirmation" autofocus>
                                @error('new_password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-primary" value="更新">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth
@if ($errors->any())
    <script>
        onload = () => document.getElementById('updateUserModal').click();
    </script>
@endif
</body>
<footer>
    <div class="row text-center">
        <div class="col-12">
            <p class="text-center">
                <small>
                    <a href="/privacy">プライバシーポリシー</a>
                    ・
                    <a href="/terms">利用規約</a>
                    ・
                    <a href="/developers">開発者一覧</a>
                </small>
            </p>
        </div>
        <div class="col-12">
            <small>
                <p><a href="https://commew.net/">©commew</a></p>
            </small>
        </div>
    </div>
</footer>
</html>
