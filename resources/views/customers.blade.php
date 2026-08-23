<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <title>Panel Admin â€” Madu Wild Bee</title>
  <link rel="stylesheet" href="{{ asset('public/css/admin.css') }}">
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <span class="hex-mark"></span>
      <div class="brand-name">Madu Wild Bee
        <span class="brand-sub">Panel Admin</span>
      </div>
    </div>
    <span class="admin-chip">{{ auth()->user()->email }}</span>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout">Keluar</button>
    </form>
  </header>

  @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="no-access">{{ session('error') }}</div>
  @elseif($errors->any())
    <div class="no-access">
      <strong>Periksa kembali isian berikut:</strong>
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <main>
    <section class="hero">
      <h1>Daftar Customer</h1>
      <p>Pilih customer untuk mengelola progres pesanannya.</p>
    </section>

    <div class="container">
      <details class="details-add" @if($errors->any()) open @endif>
        <summary>Tambah Customer</summary>
        <form method="POST" action="{{ route('customers.store') }}" class="add-form">
          @csrf
          <div class="add-field">
            <label for="new-email">Email</label>
            <input id="new-email" type="email" name="email" value="{{ old('email') }}" required />
          </div>
          <div class="add-field">
            <label for="new-no_hp">No HP</label>
            <input id="new-no_hp" type="text" name="no_hp" value="{{ old('no_hp') }}"
                   maxlength="20" inputmode="numeric" required />
          </div>
          <div class="add-field">
            <label for="new-password">Kata Sandi</label>
            <input id="new-password" type="password" name="password" minlength="8" required autocomplete="new-password" />
          </div>
          <div class="add-field add-field--action">
            <button type="submit" class="btn-add">Tambah</button>
          </div>
        </form>
      </details>

      <table class="customer-table">
        <thead>
          <tr>
            <th>Email</th>
            <th>No HP</th>
            <th>Role</th>
            <th>Progres Selesai</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
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
            @endphp
            <tr>
              <td>{{ $c->email }}</td>
              <td>{{ $c->no_hp }}</td>
              <td><span class="role-badge {{ $c->role === 'admin' ? 'role-admin' : 'role-user' }}">{{ ucfirst($c->role) }}</span></td>
              <td>{{ $done }} / 8</td>
              <td><a class="btn-kelola" href="{{ route('customers.show', $c->id) }}">Kelola</a></td>
            </tr>
          @empty
            <tr>
              <td colspan="5">Belum ada customer terdaftar.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
