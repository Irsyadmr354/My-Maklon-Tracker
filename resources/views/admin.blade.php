<!DOCTYPE html>
<html lang="id" data-theme="dark">
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
<body>
  <header class="topbar">
    <div class="brand">
      <span class="brand-name">Madu Wild Bee</span>
      <span class="brand-tag">Admin</span>
    </div>
    <div class="topbar-actions">
      @if($user->id !== auth()->id())
        <a class="breadcrumb" href="{{ route('customers.index') }}">&larr; Customer</a>
      @endif
      <span class="chip">{{ $user->email }}</span>
      <button class="btn-theme" id="themeToggle" type="button" aria-label="Toggle theme">
        <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn-ghost btn-sm" type="submit">Keluar</button>
      </form>
    </div>
  </header>

  @if(session('success'))
    <div class="toast">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="toast toast-error">{{ session('error') }}</div>
  @endif

  <main class="admin-main">
    <section class="page-header">
      <h1>Kelola Pesanan</h1>
      <p class="page-sub">{{ $user->email }}</p>
    </section>

    <form action="{{ route('progress.update') }}" method="POST" enctype="multipart/form-data" id="adminForm">
      @csrf
      <input type="hidden" name="user_id" value="{{ $user->id }}">

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
        $firstIncomplete = null;
      @endphp

      <div class="pipeline pipeline-edit">
        @foreach($stages as $stepName => $gambar)
          @php
            $i = $loop->iteration;
            $statusKey = "status{$i}";
            $dateKey = "tanggal{$i}";
            $bukti = $buktiList[$i] ?? null;
            $current = old($statusKey, $progress->{$statusKey});
            if ($current !== 'done' && $firstIncomplete === null) $firstIncomplete = $i;
          @endphp

          <div class="pipeline-step {{ $current === 'done' ? 'step-done' : ($current === 'on_progress' ? 'step-active' : 'step-pending') }}" data-step="{{ $i }}">
            <div class="pipeline-dot">
              @if($current === 'done')
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              @else
                <span class="dot-num">{{ $i }}</span>
              @endif
            </div>
            <div class="pipeline-content">
              <div class="pipeline-header">
                <h3>{{ Str::title($stepName) }}</h3>
                <span class="badge badge-{{ $current === 'done' ? 'done' : ($current === 'on_progress' ? 'on_progress' : 'hold') }}">
                  {{ $current === 'done' ? 'Selesai' : ($current === 'on_progress' ? 'Dikerjakan' : 'Ditunda') }}
                </span>
              </div>

              @if($bukti && $bukti->tanggal)
                <p class="pipeline-date">{{ $bukti->tanggal }}</p>
              @endif
              @if($bukti && $bukti->assigned_to)
                <span class="pipeline-assigned">{{ $bukti->assigned_to === 'digital_marketing' ? 'Digital Marketing' : ucfirst($bukti->assigned_to) }}</span>
              @endif

              @if($isAdmin)
                <button type="button" class="btn-expand" onclick="toggleStep({{ $i }})">
                  <span class="expand-text" id="expand-text-{{ $i }}">Edit</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              @endif

              @if($isAdmin)
                <div class="edit-panel" id="panel-{{ $i }}">
                  {{-- Status buttons --}}
                  @if($i !== 8)
                    <label class="field-label">Ubah Status</label>
                    <div class="status-buttons">
                      <button type="button" class="status-btn status-btn-done {{ $current === 'done' ? 'active' : '' }}" onclick="setStatus({{ $i }}, 'done', this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Selesai
                      </button>
                      <button type="button" class="status-btn status-btn-progress {{ $current === 'on_progress' ? 'active' : '' }}" onclick="setStatus({{ $i }}, 'on_progress', this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Dikerjakan
                      </button>
                      <button type="button" class="status-btn status-btn-hold {{ is_null($current) || $current === 'hold' ? 'active' : '' }}" onclick="setStatus({{ $i }}, 'hold', this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        Ditunda
                      </button>
                    </div>
                    <input type="hidden" name="{{ $statusKey }}" id="status-input-{{ $i }}" value="{{ $current ?? 'hold' }}">
                  @endif

                  {{-- Assigned to --}}
                  <div class="field">
                    <label>Tanggung Jawab</label>
                    <select name="assigned_to{{ $i }}">
                      <option value="">-- Pilih --</option>
                      <option value="admin" {{ ($bukti?->assigned_to ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                      <option value="digital_marketing" {{ ($bukti?->assigned_to ?? '') === 'digital_marketing' ? 'selected' : '' }}>Digital Marketing</option>
                      <option value="produksi" {{ ($bukti?->assigned_to ?? '') === 'produksi' ? 'selected' : '' }}>Produksi</option>
                    </select>
                  </div>

                  {{-- Date --}}
                  <div class="field date-field" onclick="try{this.querySelector('input[type=date]').showPicker()}catch(e){}">
                    <label>Tanggal</label>
                    <input type="date" name="{{ $dateKey }}" value="{{ old($dateKey, $progress->{$dateKey}) }}">
                  </div>

                  {{-- Step 8: keterangan --}}
                  @if($i === 8)
                    <div class="field">
                      <label>Keterangan Kesimpulan</label>
                      <textarea name="keterangan8" id="keterangan8" rows="3">{{ old('keterangan8', $buktiList[8]->keterangan ?? '') }}</textarea>
                    </div>
                  @endif

                  {{-- Upload bukti (hanya muncul jika status = done) --}}
                  @if($i !== 7 && $i !== 8)
                    <div class="upload-zone" id="upload-{{ $i }}" data-step="{{ $i }}" style="{{ $current === 'done' ? '' : 'display:none' }}">
                      <input type="hidden" name="uploaded_by{{ $i }}" id="uploaded-by-{{ $i }}" value="{{ $bukti?->uploaded_by ?? '' }}">
                      @if($bukti && $bukti->uploaded_by)
                        <p class="uploader-info">Uploaded by: {{ $bukti->uploaded_by }}</p>
                      @endif
                      <div class="field">
                        <label>Upload Bukti</label>
                        <input type="file" name="bukti{{ $i }}" accept=".jpg,.jpeg,.png,.pdf" onchange="requestUploader({{ $i }})">
                      </div>
                      <div class="field">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan{{ $i }}" value="{{ old("keterangan{$i}", $bukti?->keterangan) }}" placeholder="Opsional">
                      </div>
                      @if($bukti && $bukti->path)
                        <a class="link-existing" href="{{ route('bukti.show', $bukti->id) }}" target="_blank" rel="noopener">Lihat bukti saat ini &rarr;</a>
                      @endif
                    </div>
                  @endif
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      @if($isAdmin)
        <div class="action-bar">
          <a href="{{ route('admin.index') }}" class="btn-ghost">&larr; Kembali</a>
          <a href="{{ route('customers.index') }}" class="btn-ghost">Kelola Customer</a>
          <button type="submit" class="btn-primary" id="saveBtn">Simpan Semua</button>
        </div>
      @else
        <div class="alert alert-error" style="max-width:680px;margin:1rem auto;">Anda tidak memiliki akses untuk mengedit data.</div>
      @endif
    </form>
  </main>

  <script>
    // Theme toggle
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

    // Status buttons → set hidden input + toggle upload zone
    function setStatus(step, value, btn) {
      document.getElementById('status-input-' + step).value = value;

      // Update active state
      btn.closest('.status-buttons').querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      // Update badge
      const badge = btn.closest('.pipeline-content').querySelector('.badge');
      const labels = { done: 'Selesai', on_progress: 'Dikerjakan', hold: 'Ditunda' };
      const classes = { done: 'badge-done', on_progress: 'badge-on_progress', hold: 'badge-hold' };
      badge.textContent = labels[value];
      badge.className = 'badge ' + classes[value];

      // Update pipeline step class
      const stepEl = btn.closest('.pipeline-step');
      stepEl.classList.remove('step-done', 'step-active', 'step-pending');
      const dotClasses = { done: 'step-done', on_progress: 'step-active', hold: 'step-pending' };
      stepEl.classList.add(dotClasses[value]);

      // Update dot icon
      const dot = stepEl.querySelector('.pipeline-dot');
      if (value === 'done') {
        dot.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
      } else {
        dot.innerHTML = '<span class="dot-num">' + step + '</span>';
      }

      // Toggle upload zone
      const upload = document.getElementById('upload-' + step);
      if (upload) upload.style.display = value === 'done' ? '' : 'none';

      // Step 7 WhatsApp alert
      if (step === 7 && value === 'done') {
        alert('Pastikan kirim buktinya di WhatsApp!');
      }
    }

    // Toggle expand/collapse
    function toggleStep(step) {
      const panel = document.getElementById('panel-' + step);
      const text = document.getElementById('expand-text-' + step);
      const isOpen = panel.classList.toggle('open');
      text.textContent = isOpen ? 'Tutup' : 'Edit';
    }

    // Auto-expand first incomplete step
    document.addEventListener('DOMContentLoaded', function() {
      @if($firstIncomplete)
        toggleStep({{ $firstIncomplete }});
      @endif
    });

    // Double submit prevention
    document.getElementById('adminForm').addEventListener('submit', function() {
      const b = document.getElementById('saveBtn');
      if (b) { b.disabled = true; b.textContent = 'Menyimpan...'; setTimeout(() => { b.disabled = false; b.textContent = 'Simpan Semua'; }, 3000); }
    });

    // Uploader name modal
    let pendingUploaderStep = null;

    function requestUploader(step) {
      const input = document.getElementById('uploaded-by-' + step);
      if (input && input.value) return; // already has name
      pendingUploaderStep = step;
      document.getElementById('uploaderModal').style.display = 'flex';
      document.getElementById('uploaderName').value = '';
      document.getElementById('uploaderName').focus();
    }

    function confirmUploader() {
      const name = document.getElementById('uploaderName').value.trim();
      if (!name) { document.getElementById('uploaderName').focus(); return; }
      const input = document.getElementById('uploaded-by-' + pendingUploaderStep);
      if (input) input.value = name;
      document.getElementById('uploaderModal').style.display = 'none';
      pendingUploaderStep = null;
    }

    function cancelUploader() {
      document.getElementById('uploaderModal').style.display = 'none';
      pendingUploaderStep = null;
    }

    document.getElementById('uploaderName').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') { e.preventDefault(); confirmUploader(); }
      if (e.key === 'Escape') cancelUploader();
    });
  </script>

  <!-- Uploader Name Modal -->
  <div id="uploaderModal" class="uploader-overlay" style="display:none">
    <div class="uploader-card">
      <h3>Siapa yang upload?</h3>
      <p class="uploader-hint">Masukkan nama lengkap</p>
      <input type="text" id="uploaderName" placeholder="Nama lengkap" maxlength="100">
      <div class="uploader-actions">
        <button type="button" class="btn-ghost" onclick="cancelUploader()">Batal</button>
        <button type="button" class="btn-primary" onclick="confirmUploader()">Simpan</button>
      </div>
    </div>
  </div>

</body>
</html>
