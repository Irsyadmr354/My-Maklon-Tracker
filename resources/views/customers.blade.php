<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <title>Panel Admin — Madu Wild Bee</title>
  <link rel="stylesheet" href="{{ asset('/public/css/admin.css') }}">
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
  </header>

  <main>
    <section class="hero">
      <h1>Daftar Customer</h1>
      <p>Pilih customer untuk mengelola progres pesanannya.</p>
    </section>

    <div class="container">
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
