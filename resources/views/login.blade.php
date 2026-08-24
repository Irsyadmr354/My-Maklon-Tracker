<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('public/css/login.css') }}">
  <title>Madu Wild Bee — Masuk</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <button class="btn-theme" id="themeToggle" type="button" aria-label="Toggle theme">
    <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
  </button>

  <main class="login-wrap">
    <div class="login-card">
      <div class="login-brand">
        <h1>Madu Wild Bee</h1>
        <span class="brand-tag">Maklon Tracker</span>
      </div>

      <h2 class="login-title">Masuk ke akun Anda</h2>

      @if ($errors->any())
        <div class="alert alert-error">
          @foreach ($errors->all() as $error)
            <span>{{ $error }}</span>
          @endforeach
        </div>
      @endif
      @if (session('error'))
        <div class="alert alert-error"><span>{{ session('error') }}</span></div>
      @endif
      @if (session('success'))
        <div class="alert alert-success"><span>{{ session('success') }}</span></div>
      @endif

      <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf
        <div class="field">
          <label for="no_hp">Nomor HP</label>
          <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required maxlength="20" inputmode="numeric" autocomplete="username">
        </div>
        <div class="field">
          <label for="password">Kata Sandi</label>
          <input type="password" id="password" name="password" placeholder="{{ old('no_hp') ? 'Masukkan kata sandi' : 'Masukkan kata sandi (aktivasi pertama)' }}" required minlength="8" autocomplete="current-password">
        </div>
        <button type="submit" class="btn-primary btn-full" id="submitBtn">Masuk</button>
      </form>

      <p class="login-footer">Hubungi admin jika belum punya akun.</p>
    </div>
  </main>

  <script>
    (function() {
      var saved = localStorage.getItem('theme') || 'dark';
      document.documentElement.setAttribute('data-theme', saved);
      document.getElementById('themeToggle').addEventListener('click', function() {
        var current = document.documentElement.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
      });
    })();

    setInterval(function() {
      fetch('/login', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.text(); })
        .then(function(html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var t = doc.querySelector('meta[name="csrf-token"]');
          if (t && t.content) {
            document.querySelector('meta[name="csrf-token"]').content = t.content;
            var inp = document.querySelector('input[name="_token"]');
            if (inp) inp.value = t.content;
          }
        }).catch(function() {});
    }, 5 * 60 * 1000);

    document.getElementById('loginForm').addEventListener('submit', function() {
      var b = document.getElementById('submitBtn');
      b.disabled = true;
      b.textContent = 'Masuk...';
      setTimeout(function() { b.disabled = false; b.textContent = 'Masuk'; }, 5000);
    });
  </script>
</body>
</html>
