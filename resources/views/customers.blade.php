<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <title>Panel Admin — Madu Wild Bee</title>
  <link rel="stylesheet" href="{{ asset('public/css/admin.css') }}">
</head>
<body class="admin-page">
  <header class="topbar">
    <div class="brand">
      <span class="brand-name">Madu Wild Bee
        <span class="brand-sub">Panel Admin</span>
      </span>
    </div>
    <div class="topbar-right">
      <span class="admin-chip">{{ auth()->user()->email }}</span>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Keluar</button>
      </form>
    </div>
  </header>

  @if(session('success'))
    <div class="toast">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="toast toast--error">{{ session('error') }}</div>
  @elseif($errors->any())
    <div class="toast toast--error">
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

    <div style="max-width:860px;margin:0 auto;padding:0 1.25rem;">
      <details class="add-section" @if($errors->any()) open @endif>
        <summary class="add-toggle">+ Tambah Customer</summary>
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
    </div>

    <div class="customer-list">
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
              <span class="role-badge {{ $c->role === 'admin' ? 'role-admin' : 'role-user' }}">
                {{ ucfirst($c->role) }}
              </span>
            </div>
          </div>
          <div class="customer-progress">
            <div class="mini-bar"><div class="mini-fill" style="width:{{ $percent }}%"></div></div>
            <span class="mini-label">{{ $done }}/8</span>
          </div>
          <div class="customer-actions">
            <a class="btn-manage" href="{{ route('customers.show', $c->id) }}">Kelola</a>
          </div>
        </div>
      @empty
        <div class="empty-state">
          <h3>Belum ada customer</h3>
          <p>Tambahkan customer pertama dengan tombol di atas.</p>
        </div>
      @endforelse
    </div>
  </main>
</body>
</html>
