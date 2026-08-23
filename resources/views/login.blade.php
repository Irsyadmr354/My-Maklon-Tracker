<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('/public/css/login.css') }}">
  <title>Masuk — Madu Wild Bee Maklon Tracker</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <div class="login-container">
    <div class="brand">
      <span class="hex-mark"></span>
      <div class="brand-name">Madu Wild Bee
        <span class="brand-sub">Maklon Tracker</span>
      </div>
    </div>

    <h2>Masuk ke akun Anda</h2>

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
        <label for="no_hp">Nomor HP</label>
        <input type="text" id="no_hp" name="no_hp" placeholder="081234567890"
               value="{{ old('no_hp') }}" required inputmode="numeric" />
      </div>
      <div class="form-group button-wrapper">
        <button type="submit" class="btn-login" id="submitBtn">Masuk</button>
      </div>
    </form>

    <p class="login-footer">Masuk dengan email dan nomor HP yang terdaftar.</p>
  </div>

  <script>
    // Auto refresh CSRF token jika halaman sudah lama dibuka
    document.addEventListener('DOMContentLoaded', function() {
      // Refresh CSRF token setiap 30 menit
      setInterval(function() {
        fetch('/login', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).then(response => response.text())
        .then(html => {
          // Extract CSRF token dari response
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newToken = doc.querySelector('meta[name="csrf-token"]')?.content;

          if (newToken) {
            document.querySelector('meta[name="csrf-token"]').content = newToken;
            document.querySelector('input[name="_token"]').value = newToken;
          }
        }).catch(() => {});
      }, 30 * 60 * 1000); // 30 menit

      // Prevent double submit
      document.getElementById('loginForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sedang Masuk...';

        // Re-enable setelah 5 detik sebagai backup
        setTimeout(function() {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Masuk';
        }, 5000);
      });
    });
  </script>
</body>
</html>
