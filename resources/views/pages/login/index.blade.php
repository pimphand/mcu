@extends('layouts.auth')

@section('title')
    Login Admin
@endsection

@section('css')
@endsection

@section('content')
    <!-- Login basic -->
    <div class="card mb-0">
        <div class="card-body">
            <a href="javascript:void(0)" class="brand-logo" style="display: flex; align-items: center; justify-content: center;">
                <img src="{{asset('logo.png')}}" width="40px" style="margin-right: 10px;">
                <h2 class="brand-text text-primary ms-1" style="margin: 0;">Klinik Dr Dini</h2>
            </a>


            {{--            <h4 class="card-title mb-1">Welcome to Vuexy! 👋</h4>--}}
{{--            <p class="card-text mb-2">Please sign-in to your account and start the adventure</p>--}}

            <form class="auth-login-form mt-2" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-1">
                    <label for="login-email" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="login-email" name="username"
                        placeholder="username or email" aria-describedby="login-email" tabindex="1" autofocus />
                </div>

                <div class="mb-1">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="login-password">Password</label>

                    </div>
                    <div class="input-group input-group-merge form-password-toggle">
                        <input type="password" class="form-control form-control-merge" id="login-password" name="password"
                            tabindex="2"
                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                            aria-describedby="login-password" />
                        <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                    </div>
                </div>
                <div class="mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember-me" tabindex="3" />
                        <label class="form-check-label" for="remember-me"> Ingat Saya </label>
                    </div>
                </div>
                @if (session('error'))
                    <p class="text-danger mb-1">{{ session('error') }}</p>
                @endif
                <button type="submit" class="btn btn-primary w-100" tabindex="4">Login</button>
            </form>
        </div>
    </div>
    <!-- /Login basic -->
@endsection

@section('js')
@endsection
