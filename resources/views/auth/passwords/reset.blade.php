<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Телезапчасти</title>
        <meta name="description" content="Телезапчасти" />
        <meta name="keywords" content="Телезапчасти" />

        <!-- Headbase -->

        @include('includes.head')
    </head>

    <body>
        <div class="full-height sd-12 col-12 pr-10 pl-10 flex-center-center">
            <div class="col-6 sd-12">
                <div>
                    <a class="logo flex-center-center sd-12" href="{{ route('main') }}">
                        <img class="sd-2" src="{{ asset('img/icon/logo.svg') }}" alt="Телезапчасти" />
                        <h3>
                            <span>tele</span>
                            Zapchasti
                        </h3>
                    </a>
                </div>
                <div>
                    <h4 class="text-center">
                        {{ __('Сброс пароля') }}
                    </h4>
                </div>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}" />

                    <div class="b5 bc sd-12 col-12 mt-em-1">
                        <div class="form-label-group sd-12">
                            <input
                                id="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ $email ?? old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                                placeholder="Email"
                            />
                            <label for="email" class="col-md-4 col-form-label text-md-right">
                                {{ __('Email') }}
                            </label>

                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="b5 bc sd-12 col-12 mt-em-1">
                        <div class="form-label-group sd-12">
                            <input
                                id="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Новый пароль"
                            />
                            <label for="password" class="col-md-4 col-form-label text-md-right">
                                {{ __('Новый пароль') }}
                            </label>

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="b5 bc sd-12 col-12 mt-em-1">
                        <div class="form-label-group sd-12">
                            <input
                                id="password-confirm"
                                type="password"
                                class="form-control"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Подтвердить новый пароль"
                            />
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">
                                {{ __('Подтвердить новый пароль') }}
                            </label>
                        </div>
                    </div>

                    <div class="flex-center-center">
                        <div class="col-6 sd-6">
                            <button type="submit" class="button__trigger">
                                {{ __('Сбросить пароль') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>