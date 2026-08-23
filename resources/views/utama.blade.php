<!DOCTYPE html>
<html lang="id" data-theme="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/css/utama.css') }}" />
    <title>Tracker Pesanan — Madu Wild Bee</title>
  </head>
  <body>
    @php
      $doneCount = $buktiList->filter(fn ($b) => strtolower((string) $b->status) === 'done')->count();
      $percent   = intdiv($doneCount * 100, 8);
    @endphp

    <header class="topbar">
      <div class="brand">
        <span class="brand-name">Madu Wild Bee</span>
        <span class="brand-tag">Tracker</span>
      </div>
      <div class="topbar-actions">
        <button class="btn-theme" id="themeToggle" type="button" aria-label="Toggle theme">
          <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn-ghost btn-sm">Keluar</button>
        </form>
      </div>
    </header>

    @if(session('success'))
      <div class="toast">{{ session('success') }}</div>
    @endif

    <main class="tracker-main">
      <section class="tracker-hero">
        <p class="eyebrow">Progres Pesanan</p>
        <h1>Perjalanan madu Anda</h1>
        <div class="progress-bar">
          <div class="progress-fill" style="width: {{ $percent }}%"></div>
        </div>
        <p class="progress-text">{{ $doneCount }} dari 8 tahap selesai</p>
      </section>

      @if($buktiList->isEmpty())
        <div class="empty-card">
          <h3>Pesanan belum dimulai</h3>
          <p>Tim kami akan memperbarui progres di sini. Hubungi CS untuk informasi lebih lanjut.</p>
        </div>
      @endif

      <!-- PIPELINE -->
      <div class="pipeline">
        @foreach($stages as $stepName => $gambar)
          @php
            $step = $loop->iteration;
            $bukti = $buktiList->get($step);
            $rawStatus = optional($bukti)->status ?? 'hold';
            $status = strtolower(str_replace(' ', '_', $rawStatus));
            $isLast = $loop->last;
          @endphp

          <div class="pipeline-step {{ $status === 'done' ? 'step-done' : ($status === 'on_progress' ? 'step-active' : 'step-pending') }}">
            <div class="pipeline-dot">
              @if($status === 'done')
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              @else
                <span class="dot-num">{{ $step }}</span>
              @endif
            </div>
            <div class="pipeline-content">
              <div class="pipeline-header">
                <h3>{{ Str::title($stepName) }}</h3>
                <span class="badge badge-{{ $status }}">{{ $status === 'done' ? 'Selesai' : ($status === 'on_progress' ? 'Dikerjakan' : 'Ditunda') }}</span>
              </div>
              @if($bukti && $bukti->tanggal)
                <p class="pipeline-date">{{ $bukti->tanggal }}</p>
              @endif
              @if($bukti && $bukti->keterangan && $step === 8)
                <p class="pipeline-note">{{ $bukti->keterangan }}</p>
              @endif
              @if($step >= 1 && $step <= 6 && $bukti && $bukti->path)
                @if(str_ends_with(strtolower($bukti->path), '.pdf'))
                  <a class="btn-proof" href="{{ route('bukti.show', $bukti->id) }}" target="_blank" rel="noopener">Lihat Bukti</a>
                @else
                  <button type="button" class="btn-proof js-show-popup"
                    data-popup-src="{{ route('bukti.show', $bukti->id) }}"
                    data-popup-desc="{{ $bukti->keterangan }}">Lihat Bukti</button>
                @endif
                @if($bukti->uploaded_by)
                  <span class="uploaded-by">oleh {{ $bukti->uploaded_by }}</span>
                @endif
              @endif
              @endif
              @if($step === 7 && $bukti && $status === 'done')
                <span class="badge badge-done">Terkirim via WhatsApp</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </main>

    <!-- Popup overlay -->
    <div id="overlay" class="popup-overlay">
      <div class="popup-card">
        <h3>Bukti Tahapan</h3>
        <img id="popup-img" src="" alt="Bukti tahapan">
        <div id="popup-desc"></div>
        <button type="button" class="btn-ghost" onclick="closePopup()">Tutup</button>
      </div>
    </div>

    <script>
      // Theme toggle
      (function() {
        const html = document.documentElement;
        const saved = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', saved);
        updateIcons(saved);

        document.getElementById('themeToggle').addEventListener('click', function() {
          const current = html.getAttribute('data-theme');
          const next = current === 'dark' ? 'light' : 'dark';
          html.setAttribute('data-theme', next);
          localStorage.setItem('theme', next);
          updateIcons(next);
        });

        function updateIcons(theme) {
          const sun = html.querySelector('.icon-sun');
          const moon = html.querySelector('.icon-moon');
          if (sun) sun.style.display = theme === 'dark' ? 'none' : 'block';
          if (moon) moon.style.display = theme === 'dark' ? 'block' : 'none';
        }
      })();

      // Popup
      const overlay = document.getElementById('overlay');
      const popupImg = document.getElementById('popup-img');
      const popupDesc = document.getElementById('popup-desc');
      function showPopup(src, desc) { popupImg.src = src; popupDesc.textContent = desc || ''; overlay.classList.add('active'); }
      function closePopup() { overlay.classList.remove('active'); }
      document.addEventListener('keydown', e => { if (e.key === 'Escape') closePopup(); });
      overlay.addEventListener('click', e => { if (e.target === overlay) closePopup(); });
      document.querySelectorAll('.js-show-popup').forEach(el => {
        el.addEventListener('click', () => showPopup(el.dataset.popupSrc, el.dataset.popupDesc));
      });
    </script>
  </body>
</html>
