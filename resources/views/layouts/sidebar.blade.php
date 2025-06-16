<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AtlasBulletinBoard</title>

    <!-- Fonts & Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300&family=Oswald:wght@200&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
</head>
<body class="all_content">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <p>
                <a href="{{ route('top.show') }}">
                    <img src="{{ asset('image/home.png') }}" alt="マイページ" width="16" class="mr-2">マイページ
                </a>
            </p>
            <p>
                <a href="/logout">
                    <img src="{{ asset('image/logout.png') }}" alt="ログアウト" width="16" class="mr-2">ログアウト
                </a>
            </p>
            <p>
                <a href="{{ route('calendar.general.show', ['user_id' => Auth::id()]) }}">
                    <img src="{{ asset('image/yoyaku.png') }}" alt="スクール予約" width="16" class="mr-2">スクール予約
                </a>
            </p>
            @if(in_array(Auth::user()->role, [1, 2, 3]))
                <p>
                    <a href="{{ route('calendar.admin.show', ['user_id' => Auth::id()]) }}">
                        <img src="{{ asset('image/kakunin.png') }}" alt="予約確認" width="16" class="mr-2">スクール予約確認
                    </a>
                </p>
                <p>
                    <a href="{{ route('calendar.admin.setting', ['user_id' => Auth::id()]) }}">
                        <img src="{{ asset('image/touroku.png') }}" alt="枠登録" width="16" class="mr-2">スクール枠登録
                    </a>
                </p>
            @endif
            <p>
                <a href="{{ route('post.show') }}">
                    <img src="{{ asset('image/koment.png') }}" alt="掲示板" width="16" class="mr-2">掲示板
                </a>
            </p>
            <p>
                <a href="{{ route('user.show') }}">
                    <img src="{{ asset('image/user.png') }}" alt="ユーザー検索" width="16" class="mr-2">ユーザー検索
                </a>
            </p>
        </div>

        <!-- Main Content -->
        <div class="main-container">
            {{ $slot }}
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/bulletin.js') }}"></script>
    <script src="{{ asset('js/user_search.js') }}"></script>
    <script src="{{ asset('js/calendar.js') }}"></script>
</body>
</html>
