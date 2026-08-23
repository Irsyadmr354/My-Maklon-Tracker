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
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="admin-page">
  <header class="topbar">
    <div class="brand">
      <div class="brand-name">Madu Wild Bee
        <span class="brand-sub">Panel Admin</span>
      </div>
    </div>
    <div class="topbar-right">
      @if($user->id !== auth()->id())
        <a class="breadcrumb" href="{{ route('customers.index') }}">&larr; Customer</a>
      @endif
      <span class="admin-chip">{{ $user->email }}</span>
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
  @endif

  @php
    $isAdmin = ($user->role === 'admin');

    $stages = [
      'konsultasi'     => 'gambar1.png',
      'pembayaran'     => 'gambar4.png',
      'desain label'   => 'gambar5.png',
      'produksi'       => 'gambar7.png',
      'pengemasan'     => 'gambar9.png',
      'pengiriman'     => 'gambar10.png',
      'foto dan video' => 'gambar8.png',
      'kesimpulan'     => 'gambar2.png',
    ];
  @endphp

  <main>
    <section class="page-header">
      <h1>Kelola progres pesanan</h1>
      <p>{{ $user->email }} — perbarui status, unggah bukti, lalu simpan.</p>
    </section>

    <form action="{{ route('progress.update') }}" method="POST" enctype="multipart/form-data" id="adminForm">
      @csrf
      <input type="hidden" name="user_id" value="{{ $user->id }}">

      <div class="stages-grid">
        @foreach($stages as $stepName => $gambar)
          @php
            $i          = $loop->iteration;
            $statusKey  = "status{$i}";
            $dateKey    = "tanggal{$i}";
            $bukti      = $buktiList[$i] ?? null;
            $current    = old($statusKey, $progress->{$statusKey});

            $deskripsiTahapan = [
              1 => 'Digital Marketing',
              2 => 'Digital Marketing',
              3 => 'Digital Marketing',
              4 => 'Produksi',
              5 => 'Produksi',
              6 => 'Produksi',
              7 => 'Digital Marketing',
              8 => 'Digital Marketing',
            ];
          @endphp

          <div class="stage-card">
            <div class="stage-head">
              <span class="stage-num">{{ $i }}</span>
              <div>
                <h3>{{ Str::title($stepName) }}</h3>
                <span class="stage-dept">{{ $deskripsiTahapan[$i] }}</span>
              </div>
            </div>

            <div class="stage-controls">
              <div class="field">
                <label>Tanggal</label>
                <input type="date" name="{{ $dateKey }}" value="{{ old($dateKey, $progress->{$dateKey}) }}">
              </div>

              @if($i === 8 && $isAdmin)
                <div class="field">
                  <label>Keterangan</label>
                  <textarea name="keterangan8" id="keterangan8" rows="4">{{ old('keterangan8', $buktiList[8]->keterangan ?? '') }}</textarea>
                </div>
              @else
                <div class="field">
                  <label>Status</label>
                  <select name="{{ $statusKey }}" id="status-select-{{ $i }}">
                    <option value="done" {{ $current === 'done' ? 'selected' : '' }}>Selesai</option>
                    <option value="on_progress" {{ $current === 'on_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="hold" {{ is_null($current) || $current === 'hold' ? 'selected' : '' }}>Ditunda</option>
                  </select>
                </div>
              @endif
            </div>

            @if($isAdmin && $i !== 7 && $i !== 8)
              <div class="upload-zone" data-step="{{ $i }}" style="{{ $current === 'done' ? '' : 'display:none' }}">
                <div class="upload-row">
                  <label for="bukti{{ $i }}" class="upload-label">Bukti</label>
                  <input type="file" id="bukti{{ $i }}" name="bukti{{ $i }}" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="upload-row">
                  <label for="keterangan{{ $i }}">Keterangan</label>
                  <input type="text" id="keterangan{{ $i }}" name="keterangan{{ $i }}"
                         value="{{ old("keterangan{$i}", $bukti?->keterangan) }}" placeholder="Opsional">
                </div>
                @if($bukti)
                  <a class="link-existing" href="{{ route('bukti.show', $bukti->id) }}" target="_blank" rel="noopener">Lihat bukti &rarr;</a>
                @endif
              </div>
            @endif
          </div>
        @endforeach
      </div>

      @if($isAdmin)
        <div class="action-bar">
          <a href="{{ url('/') }}" class="btn-ghost">Kembali</a>
          <button type="submit" class="btn-primary" id="saveBtn">Simpan Semua</button>
        </div>
      @else
        <div class="notice notice--error">Anda tidak memiliki akses untuk mengedit data.</div>
      @endif
    </form>
  </main>

<script>
  document.querySelectorAll("select[id^='status-select-']").forEach(select => {
    const step = select.id.replace('status-select-', '');
    const uploadSection = document.querySelector(`.upload-zone[data-step="${step}"]`);

    const updateUI = () => {
      const value = select.value;
      if (value === "done") {
        select.style.backgroundColor = "#e8f5e9";
        if (uploadSection && step !== '7') uploadSection.style.display = "block";
        if (uploadSection && step === '7') uploadSection.style.display = "none";
      } else if (value === "on_progress") {
        select.style.backgroundColor = "#fdf0d3";
        if (uploadSection) uploadSection.style.display = "none";
      } else if (value === "hold") {
        select.style.backgroundColor = "#fbe9e4";
        if (uploadSection) uploadSection.style.display = "none";
      } else {
        select.style.backgroundColor = "#ffffff";
        if (uploadSection) uploadSection.style.display = "none";
      }
    };

    updateUI();
    select.addEventListener("change", updateUI);
  });

  document.addEventListener("DOMContentLoaded", () => {
    const step7 = document.querySelector('#status-select-7');
    if (step7) {
      step7.addEventListener('change', function () {
        if (this.value === 'done') {
          alert('Pastikan kirim buktinya di WhatsApp!');
        }
      });
    }

    document.getElementById('adminForm').addEventListener('submit', function() {
      const saveBtn = document.getElementById('saveBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan...';
        setTimeout(function() {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Simpan Semua';
        }, 3000);
      }
    });
  });
</script>
</body>
</html>
