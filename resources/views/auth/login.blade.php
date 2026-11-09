<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ env('APP_NAME') }}</title>
    <link rel="icon" href="{{ asset('template') }}/assets/img/kaiadmin/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="{{ asset('template') }}/assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="{{ asset('template') }}/assets/css/kaiadmin.min.css"/>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a2035 0%, #2f3e6b 100%);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }
        .login-card .card-body { padding: 2rem; }
        .login-logo { height: 36px; }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="{{ asset('template') }}/assets/img/kaiadmin/logo_light.svg" alt="logo" class="login-logo mb-3">
                <h4 class="fw-bold mb-1">SIBENGKEL</h4>
                <p class="text-muted mb-0">Silakan login untuk melanjutkan</p>
            </div>

            @if ($errors->has('akun'))
                <div class="alert alert-warning">
                    <strong>Akun Nonaktif!</strong> {{ $errors->first('akun') }}
                </div>
            @elseif ($errors->has('email'))
                <div class="alert alert-danger">
                    <strong>Login Gagal!</strong> Email atau password yang kamu masukkan salah.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="nama@email.com" required autofocus autocomplete="email">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Masukkan password" required autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
