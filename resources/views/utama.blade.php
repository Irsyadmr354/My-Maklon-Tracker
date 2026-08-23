<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/css/utama.css') }}" />
    <title>Tracker Pesanan — Madu Wild Bee</title>
  </head>
  <body class="tracker-page">
    @php
      $doneCount = $buktiList->filter(fn ($b) => strtolower((string) $b->status) === 'done')->count();
      $percent   = intdiv($doneCount * 100, 8);
    @endphp

    <header class="topbar">
      <div class="brand">
        <div class="brand-name">Madu Wild Bee
          <span class="brand-sub">Maklon Tracker</span>
        </div>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Keluar</button>
      </form>
    </header>

    @if(session('success'))
      <div class="toast">{{ session('success') }}</div>
    @endif

    <main>
      <section class="progress-hero">
        <p class="eyebrow">Progres Pesanan</p>
        <h1>Perjalanan madu Anda</h1>
        <div class="progress-bar">
          <div class="progress-fill" style="width: {{ $percent }}%"></div>
        </div>
        <p class="progress-meta">{{ $doneCount }} dari 8 tahap selesai</p>
      </section>

      @if($buktiList->isEmpty())
        <div class="empty-state">
          <h3>Pesanan kamu belum dimulai</h3>
          <p>Tim Madu Wild Bee akan mengisi progres di halaman ini setiap tahap berjalan. Ada pertanyaan soal pesanan? Hubungi CS kami.</p>
        </div>
      @endif

      <div class="steps">
        @foreach($stages as $stepName => $gambar)
          @php
            $step      = $loop->iteration;
            $bukti     = $buktiList->get($step);
            $rawStatus = optional($bukti)->status ?? 'hold';
            $status    = strtolower(str_replace(' ', '_', $rawStatus));
            $statusLabel = $bukti
              ? ucfirst(str_replace('_', ' ', $status))
              : 'Belum dimulai';
          @endphp

          <div class="step-card step--{{ $status }}">
            <div class="step-num">{{ $step }}</div>
            <div class="step-content">
              <div class="step-header">
                <h3>{{ Str::title($stepName) }}</h3>
                <span class="badge badge--{{ $status }}">{{ $statusLabel }}</span>
              </div>
              <div class="step-meta">
                <span class="step-date">{{ optional($bukti)->tanggal ?? '—' }}</span>
                @if($step === 8 && optional($bukti)->keterangan)
                  <p class="step-note">{{ $bukti->keterangan }}</p>
                @endif
              </div>

              @if($step >= 1 && $step <= 6 && $bukti && $bukti->path)
                @if(str_ends_with(strtolower($bukti->path), '.pdf'))
                  <a class="btn-proof" href="{{ route('bukti.show', $bukti->id) }}" target="_blank" rel="noopener">Lihat Bukti</a>
                @else
                  <button type="button" class="btn-proof js-show-popup"
                    data-popup-src="{{ route('bukti.show', $bukti->id) }}"
                    data-popup-desc="{{ $bukti->keterangan }}">
                    Lihat Bukti
                  </button>
                @endif
              @endif

              @if($step === 7 && $bukti && $status === 'done')
                <span class="badge badge--done">Terkirim via WhatsApp</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </main>

    <div id="overlay" class="popup-overlay">
      <div class="popup-card">
        <img id="popup-img" src="" alt="Bukti tahapan" />
        <div id="popup-desc"></div>
        <button type="button" class="popup-close" onclick="closePopup()">Tutup</button>
      </div>
    </div>

    <script>
      const overlay = document.getElementById('overlay');
      const popupImg = document.getElementById('popup-img');
      const popupDesc = document.getElementById('popup-desc');

      function showPopup(imageSrc, description) {
        popupImg.src = imageSrc;
        popupDesc.textContent = description || '';
        overlay.classList.add('active');
      }

      function closePopup() {
        overlay.classList.remove('active');
      }

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePopup();
      });
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePopup();
      });

      document.querySelectorAll('.js-show-popup').forEach(el => {
        el.addEventListener('click', () => {
          showPopup(el.dataset.popupSrc, el.dataset.popupDesc);
        });
      });
    </script>
  </body>
</html>
