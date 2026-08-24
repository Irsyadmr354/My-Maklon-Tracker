<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <title>Daftar Customer — Madu Wild Bee</title>
  <link rel="stylesheet" href="{{ asset('public/css/admin.css') }}">
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <span class="brand-name">Madu Wild Bee</span>
    </div>
    <div class="topbar-actions">
      <span class="chip">{{ auth()->user()->email }}</span>
      <button class="btn-theme" id="themeToggle" type="button" aria-label="Toggle theme">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
    </div>
  </header>

  @if(session('success'))
    <div class="toast">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="toast toast-error">{{ session('error') }}</div>
  @elseif($errors->any())
    <div class="toast toast-error">
      @foreach ($errors->all() as $error)
        {{ $error }}@if(!$loop->last)<br>@endif
      @endforeach
    </div>
  @endif

  <main>
    <section class="page-header">
      <h1>Daftar Customer</h1>
      <p>Pilih customer untuk mengelola progres pesanannya.</p>
    </section>

    <div class="customer-list">
      <details class="add-section" open>
        <summary class="add-toggle">Tambah Customer baru</summary>
        <form method="POST" action="{{ route('customers.store') }}" class="add-form">
          @csrf
          <div class="field">
            <label for="new-email">Email</label>
            <input id="new-email" type="email" name="email" value="{{ old('email') }}" required />
          </div>
          <div class="field">
            <label for="new-no_hp">No HP</label>
            <input id="new-no_hp" type="text" name="no_hp" value="{{ old('no_hp') }}"
                   maxlength="20" inputmode="numeric" required />
          </div>
          <div class="field">
            <label for="new-password">Kata Sandi</label>
            <input id="new-password" type="password" name="password" minlength="8" required autocomplete="new-password" />
          </div>
          <div class="field" style="align-self:end;">
            <button type="submit" class="btn-primary" style="width:100%;">Tambah</button>
          </div>
        </form>
      </details>

      @forelse($customers as $c)
        @php
          $p = $c->progress;
          $done = 0;
          if ($p) {
            foreach (range(1, 8) as $i) {
              if (($p->{"status$i"}) === 'done') {
                $done++;
              }
            }
          }
          $percent = intdiv($done * 100, 8);
        @endphp
        <div class="customer-card">
          <div class="customer-info">
            <div class="customer-email">{{ $c->email }}</div>
            <div class="customer-meta">
              {{ $c->no_hp }}
              <span class="badge {{ $c->role === 'admin' ? 'badge-done' : 'badge-hold' }}">
                {{ ucfirst($c->role) }}
              </span>
            </div>
          </div>
          <div class="customer-progress">
            <div class="mini-bar"><div class="mini-fill" style="width:{{ $percent }}%"></div></div>
            <span class="mini-label">{{ $done }}/8</span>
          </div>
          <a class="btn-manage" href="{{ route('customers.show', $c->id) }}">Kelola</a>
        </div>
      @empty
        <div class="empty-state">
          <h3>Belum ada customer</h3>
          <p>Tambahkan customer pertama dengan tombol di atas.</p>
        </div>
      @endforelse
    </div>
  </main>

  <div class="action-bar">
    <a href="{{ route('admin.index') }}" class="btn-ghost">Keluar</a>
  </div>

  <script>
    (function() {
      const saved = localStorage.getItem('theme') || 'dark';
      document.documentElement.setAttribute('data-theme', saved);
      document.getElementById('themeToggle').addEventListener('click', function() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
      });
    })();
  </script>
</body>
</html>
