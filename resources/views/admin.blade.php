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
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <span class="hex-mark"></span>
      <div class="brand-name">Madu Wild Bee
        <span class="brand-sub">Panel Admin</span>
      </div>
    </div>
    <span class="admin-chip">{{ $user->email }}</span>
  </header>

  @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="no-access">{{ session('error') }}</div>
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
    <section class="hero">
      <h1>Kelola progres pesanan</h1>
      <p>Perbarui status tiap tahap, unggah bukti, lalu simpan.</p>
    </section>

    <div class="container">
      <div class="email-row">
        <label for="admin-email">Akun</label>
        <input class="form-group" id="admin-email" type="email" value="{{ $user->email }}" readonly />
      </div>

      <form action="{{ route('progress.update') }}" method="POST" enctype="multipart/form-data" id="adminForm">
        @csrf

        <ul class="stages">
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

            <li class="card">
              <div class="card-img">
                <img src="{{ asset('/public/gambar/' . $gambar) }}" alt="{{ $stepName }}">
              </div>

              <div class="card-main">
                <div class="card-head">
                  <h2>{{ $i }}. {{ Str::title($stepName) }}</h2>
                  <span class="step-desc">{{ $deskripsiTahapan[$i] }}</span>
                </div>

                <div class="controls">
                  <input type="date"
                    name="{{ $dateKey }}"
                    value="{{ old($dateKey, $progress->{$dateKey}) }}"
                    {{ $isAdmin ? '' : 'readonly' }}>

                  @if($i === 8 && $isAdmin) <!-- Tahap 8: Kesimpulan -->
                    <label for="keterangan8">Keterangan</label>
                    <textarea name="keterangan8" id="keterangan8" rows="5">{{ old('keterangan8', $buktiList[8]->keterangan ?? '') }}</textarea>
                  @else
                    <select name="{{ $statusKey }}"
                            id="status-select-{{ $i }}"
                            {{ $isAdmin ? '' : 'disabled' }}>
                      <option value="done"
                        {{ $current === 'done'        ? 'selected' : '' }}>Done</option>
                      <option value="on_progress"
                        {{ $current === 'on_progress' ? 'selected' : '' }}>On Progress</option>
                      <option value="hold"
                        {{ is_null($current) || $current==='hold' ? 'selected' : '' }}>
                        Hold
                      </option>
                    </select>
                  @endif
                </div>

                @if($isAdmin && $i !== 7 && $i !== 8)
                  <div class="file-upload" data-step="{{ $i }}" style="{{ $current === 'done' ? '' : 'display: none;' }}">
                    <label for="bukti{{ $i }}">Upload bukti</label>
                    <input type="file" id="bukti{{ $i }}" name="bukti{{ $i }}" />

                    <label for="keterangan{{ $i }}">Keterangan</label>
                    <input type="text"
                          id="keterangan{{ $i }}"
                          name="keterangan{{ $i }}"
                          value="{{ old("keterangan{$i}", $bukti?->keterangan) }}" />

                    @if($bukti)
                      <p>File sebelumnya:
                        <a href="{{ asset('public/storage/' . $bukti->path) }}" target="_blank">Lihat</a>
                      </p>
                    @endif
                  </div>
                @endif
              </div>
            </li>
          @endforeach
        </ul>

        @if($isAdmin)
          <div class="action-bar">
            <a href="{{ url('/') }}" class="back-button">Kembali</a>
            <button type="submit" class="btn-save" id="saveBtn">Simpan Semua</button>
          </div>
        @else
          <div class="no-access">Anda tidak memiliki akses untuk mengedit data!</div>
        @endif
      </form>
    </div>
  </main>

<script>
  // Untuk setiap select status, atur warna dan tampilkan/hide file-upload
  document.querySelectorAll("select[id^='status-select-']").forEach(select => {
    const step = select.id.replace('status-select-', '');
    const uploadSection = document.querySelector(`.file-upload[data-step="${step}"]`);

    const updateUI = () => {
      const value = select.value;
      // Warna background sesuai status
      if (value === "done") {
        select.style.backgroundColor = "#e2f1e3";
        if (uploadSection && step !== '7') uploadSection.style.display = "grid";
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

    // Inisialisasi saat halaman load
    updateUI();
    // Bind event change
    select.addEventListener("change", updateUI);
  });

  // Alert khusus untuk step 7
  document.addEventListener("DOMContentLoaded", () => {
    const step7 = document.querySelector('#status-select-7');
    if (step7) {
      step7.addEventListener('change', function () {
        if (this.value === 'done') {
          alert('Pastikan kirim buktinya di WhatsApp!');
        }
      });
    }

    // Prevent double submit
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
