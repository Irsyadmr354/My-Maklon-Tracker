<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        <span class="hex-mark"></span>
        <div class="brand-name">Madu Wild Bee
          <span class="brand-sub">Maklon Tracker</span>
        </div>
      </div>
    </header>

    @if(session('success'))
      <div class="alert-success">{{ session('success') }}</div>
    @endif

    <main>
      <section class="hero">
        <p class="eyebrow">Progres Pesanan Maklon</p>
        <h1>Perjalanan madu Anda</h1>
        <div class="drip"><div class="drip-fill" style="width: {{ $percent }}%;"></div></div>
        <p class="drip-label">{{ $doneCount }} dari 8 tahap selesai</p>
      </section>

      <ol class="timeline">
        @foreach($stages as $stepName => $gambar)
          @php
            $step      = $loop->iteration;
            $bukti     = $buktiList->get($step);
            $rawStatus = optional($bukti)->status ?? 'hold';
            $status    = strtolower(str_replace(' ', '_', $rawStatus));
            $stateClass = match($status) {
              'done'        => 'step--done',
              'on_progress' => 'step--progress',
              default       => 'step--hold',
            };
            $pillClass  = match($status) {
              'done'        => 'pill--done',
              'on_progress' => 'pill--progress',
              default       => 'pill--hold',
            };
            $statusLabel = $bukti
              ? ucfirst(str_replace('_', ' ', $status))
              : 'Belum dimulai';
          @endphp

          <li class="step {{ $stateClass }}">
            <div class="step-hex">{{ $step }}</div>
            <div class="step-body">
              <div class="step-head">
                <h2>{{ Str::title($stepName) }}</h2>
                <span class="pill {{ $pillClass }}">{{ $statusLabel }}</span>
              </div>

              <div class="step-grid">
                <img class="step-img" src="{{ asset('/public/gambar/' . $gambar) }}" alt="{{ $stepName }}" />

                <div class="controls">
                  <input type="date" disabled value="{{ optional($bukti)->tanggal }}" />
                  @if($step === 8)
                    <textarea name="keterangan8" id="keterangan8" rows="5" disabled>{{ old('keterangan8', $buktiList->get(8)->keterangan ?? '') }}</textarea>
                  @else
                    <select disabled>
                      <option>{{ $statusLabel }}</option>
                    </select>
                  @endif
                </div>
              </div>

              {{-- Tombol Lihat Bukti khusus tahap 1–6 --}}
              @if($step >= 1 && $step <= 6 && $bukti && $bukti->path)
                <button type="button" class="extra-btn js-show-popup"
                  data-popup-src="{{ asset('/public/storage/'.$bukti->path) }}"
                  data-popup-desc="{{ $bukti->keterangan }}">
                  Lihat Bukti
                </button>
              @endif

              {{-- Tombol WhatsApp khusus tahap 7 --}}
              @if($step === 7 && $bukti && $status === 'done')
                <button type="button" class="extra-btn">Terkirim via WhatsApp</button>
              @endif
            </div>
          </li>
        @endforeach
      </ol>

      <div class="page-actions">
        <a href="{{ url('/') }}" class="back-button">Kembali</a>
      </div>
    </main>

    <div id="overlay" class="popup-overlay">
      <div class="popup-card">
        <h3>Bukti Tahapan</h3>
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
