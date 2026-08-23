<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('public/css/login.css') }}">
  <title>Masuk — Madu Wild Bee Maklon Tracker</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="login-page">
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-brand">
        <h1>Madu Wild Bee</h1>
        <span class="brand-sub">Maklon Tracker</span>
      </div>

      <h2 class="login-title">Masuk ke akun Anda</h2>

      @if ($errors->any())
          <div class="msg msg--error">
              <strong>Periksa kembali isian berikut:</strong>
              <ul>
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif

      @if (session('error'))
          <div class="msg msg--error"><strong>Error:</strong> {{ session('error') }}</div>
      @endif

      @if (session('success'))
          <div class="msg msg--success"><strong>Sukses:</strong> {{ session('success') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="nama@email.com"
                 value="{{ old('email') }}" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                 required minlength="8" autocomplete="current-password" />
        </div>
        <details class="activation-section">
          <summary>Aktivasi pertama?</summary>
          <div class="form-group">
            <label for="no_hp">No HP</label>
            <input type="text" id="no_hp" name="no_hp" placeholder="081234567890"
                   value="{{ old('no_hp') }}" inputmode="numeric" maxlength="20" autocomplete="tel" />
          </div>
        </details>
        <div class="form-group button-wrapper">
          <button type="submit" class="btn-login" id="submitBtn">Masuk</button>
        </div>
      </form>

      <p class="login-hint">Hubungi admin jika belum punya akun.</p>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      setInterval(function() {
        fetch('/login', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).then(response => response.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newToken = doc.querySelector('meta[name="csrf-token"]')?.content;

          if (newToken) {
            document.querySelector('meta[name="csrf-token"]').content = newToken;
            document.querySelector('input[name="_token"]').value = newToken;
          }
        }).catch(() => {});
      }, 30 * 60 * 1000);

      document.getElementById('loginForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sedang Masuk...';

        setTimeout(function() {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Masuk';
        }, 5000);
      });
    });
  </script>
</body>
</html>
