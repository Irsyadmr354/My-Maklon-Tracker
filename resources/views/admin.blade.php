<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <title>Panel Admin — Madu Wild Bee</title>
  <link rel="stylesheet" href="{{ asset('public/css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}-20250830b">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <span class="brand-name">Madu Wild Bee</span>
      <span class="brand-tag">Admin</span>
    </div>
    <div class="topbar-actions">
      <span class="chip">{{ auth()->user()->no_hp }}</span>
      <button class="btn-theme" id="themeToggle" type="button" aria-label="Toggle theme">
        <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
    </div>
  </header>

  @if(session('success'))
    <div class="toast">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="toast toast-error">{{ session('error') }}</div>
  @endif

  <main class="admin-main">
    @php
      $doneCount = $buktiList->filter(fn($b) => strtolower((string) ($b->status ?? '')) === 'done')->count();
      $percent = intdiv($doneCount * 100, 8);
    @endphp
    <section class="page-header">
      <h1>Kelola Pesanan</h1>
      <p class="page-sub">{{ $user->no_hp }}</p>
      <div class="hero-progress" aria-label="Progress pesanan">
        <div class="progress-bar"><div class="progress-fill" style="width: {{ $percent }}%"></div></div>
        <p class="progress-text"><span class="progress-pill">{{ $doneCount }} dari 8 tahap selesai</span></p>
      </div>
    </section>
    <div class="customer-switcher-wrap">
      <div class="customer-switcher">
        <label for="customerSearch" class="switcher-label">Pindah Customer — ketik No HP / Email untuk cari</label>
        <div class="switcher-row">
          <input id="customerSearch" type="text" placeholder="Cari No HP atau Email..." autocomplete="off" list="customerList" />
          <datalist id="customerList">
            @foreach($customers as $c)<option value="{{ $c->no_hp }} — {{ $c->email }}" data-id="{{ $c->id }}">@endforeach
          </datalist>
          <button type="button" class="btn-ghost btn-sm" onclick="goCustomer()">Buka</button>
        </div>
        <p class="switcher-hint">Sedang melihat: <strong>{{ $user->no_hp }}</strong> — {{ $user->email }}</p>
      </div>
    </div>

    <form action="{{ route('progress.update') }}" method="POST" enctype="multipart/form-data" id="adminForm">
      @csrf
      <input type="hidden" name="user_id" value="{{ $user->id }}">

      @php
        $isAdmin = (auth()->user()->role === 'admin');
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

      <div class="pipeline-scroll">
        {{-- Desktop: centered horizontal stepper + detail stack --}}
        <div class="pipeline-stepper-wrap" aria-label="Stepper pipeline">
          <div class="pipeline-stepper" role="tablist">
            <div class="stepper-line" aria-hidden="true"></div>
            @foreach($stages as $stepName => $gambar)
              @php
                $i = $loop->iteration;
                $statusKey = "status{$i}";
                $bukti = $buktiList[$i] ?? null;
                $current = old($statusKey, $progress->{$statusKey});
                if ($current !== 'done' && $firstIncomplete === null) $firstIncomplete = $i;
              @endphp
              <button type="button"
                class="stepper-node {{ $current === 'done' ? 'node-done' : ($current === 'on_progress' ? 'node-active' : 'node-pending') }}"
                data-step="{{ $i }}"
                onclick="toggleStep({{ $i }})"
                role="tab"
                aria-controls="panel-{{ $i }}"
                aria-selected="false">
                <span class="stepper-dot">
                  @if($current === 'done')
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  @else
                    <span class="dot-num">{{ $i }}</span>
                  @endif
                </span>
                <span class="stepper-title">{{ Str::title($stepName) }}</span>
                <span class="badge badge-{{ $current === 'done' ? 'done' : ($current === 'on_progress' ? 'on_progress' : 'hold') }}">{{ $current === 'done' ? 'Selesai' : ($current === 'on_progress' ? 'Dikerjakan' : 'Ditunda') }}</span>
                @if($bukti && $bukti->tanggal)
                  <span class="stepper-date">{{ $bukti->tanggal }}</span>
                @endif
              </button>
            @endforeach
          </div>
          @if($isAdmin)
          <div class="detail-stack">
            @foreach($stages as $stepName => $gambar)
              @php
                $i = $loop->iteration;
                $statusKey = "status{$i}";
                $dateKey = "tanggal{$i}";
                $bukti = $buktiList[$i] ?? null;
                $current = old($statusKey, $progress->{$statusKey});
              @endphp
              <div class="edit-panel detail-panel" id="panel-{{ $i }}" role="tabpanel" data-step="{{ $i }}">
                <div class="detail-head">
                  <span class="detail-kicker">Tahap {{ $i }} / 8 — {{ Str::title($stepName) }}</span>
                  <button type="button" class="btn-ghost btn-sm" onclick="toggleStep({{ $i }})">Tutup ✕</button>
                </div>
                @if($isAdmin && $bukti && $bukti->assigned_to)
                  <span class="pipeline-assigned">{{ $bukti->assigned_to === 'digital_marketing' ? 'Digital Marketing' : ucfirst($bukti->assigned_to) }}</span>
                @endif
                @if($i !== 8)
                  <label class="field-label" style="margin-top:0.65rem">Ubah Status</label>
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
                  {{-- mirror badge id for JS sync --}}
                  <span id="badge-{{ $i }}" class="sr-only" aria-hidden="true"></span>
                @else
                  {{-- step 8 still needs placeholder so JS badge lookup never fails; inputs below handle keterangan --}}
                  <span id="badge-{{ $i }}" class="sr-only" aria-hidden="true"></span>
                @endif
                <div class="field">
                  <label>Tanggung Jawab</label>
                  <select name="assigned_to{{ $i }}">
                    <option value="">-- Pilih --</option>
                    <option value="admin" {{ ($bukti?->assigned_to ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="digital_marketing" {{ ($bukti?->assigned_to ?? '') === 'digital_marketing' ? 'selected' : '' }}>Digital Marketing</option>
                    <option value="produksi" {{ ($bukti?->assigned_to ?? '') === 'produksi' ? 'selected' : '' }}>Produksi</option>
                  </select>
                </div>
                <div class="field date-field" onclick="try{this.querySelector('input[type=date]').showPicker()}catch(e){}">
                  <label>Tanggal</label>
                  <input type="date" name="{{ $dateKey }}" value="{{ old($dateKey, $progress->{$dateKey}) }}">
                </div>
                @if($i === 8)
                  <div class="field">
                    <label>Keterangan Kesimpulan</label>
                    <textarea name="keterangan8" id="keterangan8" rows="3">{{ old('keterangan8', $buktiList[8]->keterangan ?? '') }}</textarea>
                  </div>
                @endif
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
            @endforeach
          </div>
          @endif
        </div>

        {{-- Mobile: vertical timeline (visible only <=768px) --}}
        <div class="pipeline pipeline-edit pipeline-vertical">
        @foreach($stages as $stepName => $gambar)
          @php
            $i = $loop->iteration;
            $statusKey = "status{$i}";
            $bukti = $buktiList[$i] ?? null;
            $current = old($statusKey, $progress->{$statusKey});
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
                <span class="badge badge-{{ $current === 'done' ? 'done' : ($current === 'on_progress' ? 'on_progress' : 'hold') }}">{{ $current === 'done' ? 'Selesai' : ($current === 'on_progress' ? 'Dikerjakan' : 'Ditunda') }}</span>
              </div>
              @if($bukti && $bukti->tanggal)
                <p class="pipeline-date">{{ $bukti->tanggal }}</p>
              @endif
              @if($bukti && $bukti->assigned_to)
                <span class="pipeline-assigned">{{ $bukti->assigned_to === 'digital_marketing' ? 'Digital Marketing' : ucfirst($bukti->assigned_to) }}</span>
              @endif
              @if($isAdmin)
                <button type="button" class="btn-expand" onclick="toggleStep({{ $i }})">
                  <span class="expand-text" id="m-expand-text-{{ $i }}">Lihat detail</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              @endif
            </div>
          </div>
        @endforeach
        </div>
      </div>
    </form>

      @if($isAdmin)
        <div class="action-bar">
          @if($user->id !== auth()->id())
            <a href="{{ route('customers.index') }}" class="btn-ghost">&larr; Customer</a>
          @else
            <a href="{{ route('customers.index') }}" class="btn-ghost">Kelola Customer</a>
          @endif
          <button type="button" class="btn-ghost" onclick="openGuide()">Panduan</button>
          <button type="submit" form="adminForm" class="btn-primary" id="saveBtn">Simpan Semua</button>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost">Keluar</button>
          </form>
        </div>
      @else
        <div class="alert alert-error" style="max-width:680px;margin:1rem auto;">Anda tidak memiliki akses untuk mengedit data.</div>
        <div class="action-bar">
          <a href="{{ route('customers.index') }}" class="btn-ghost">&larr; Customer</a>
          <button type="button" class="btn-ghost" onclick="openGuide()">Panduan</button>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost">Keluar</button>
          </form>
        </div>
      @endif
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

    // Status buttons → set hidden input + toggle upload zone — syncs stepper-node + vertical card
    function setStatus(step, value, btn) {
      const inp = document.getElementById('status-input-' + step);
      if (inp) inp.value = value;
      if (btn && btn.closest('.status-buttons')) {
        btn.closest('.status-buttons').querySelectorAll('.status-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
      }
      const labels = { done: 'Selesai', on_progress: 'Dikerjakan', hold: 'Ditunda' };
      const badgeClasses = { done: 'badge-done', on_progress: 'badge-on_progress', hold: 'badge-hold' };
      const nodeClasses = { done: 'node-done', on_progress: 'node-active', hold: 'node-pending' };
      const vertClasses = { done: 'step-done', on_progress: 'step-active', hold: 'step-pending' };
      // update stepper node
      const sn = document.querySelector('.stepper-node[data-step="' + step + '"]');
      if (sn) {
        sn.classList.remove('node-done','node-active','node-pending');
        sn.classList.add(nodeClasses[value] || 'node-pending');
        const badge = sn.querySelector('.badge');
        if (badge) { badge.textContent = labels[value]; badge.className = 'badge ' + badgeClasses[value]; }
        const dot = sn.querySelector('.stepper-dot');
        if (dot) {
          if (value === 'done') dot.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
          else dot.innerHTML = '<span class="dot-num">' + step + '</span>';
        }
      }
      // update vertical card (mobile)
      const vs = document.querySelector('.pipeline-vertical .pipeline-step[data-step="' + step + '"]');
      if (vs) {
        vs.classList.remove('step-done','step-active','step-pending');
        vs.classList.add(vertClasses[value] || 'step-pending');
        const vbadge = vs.querySelector('.badge');
        if (vbadge) { vbadge.textContent = labels[value]; vbadge.className = 'badge ' + badgeClasses[value]; }
        const vdot = vs.querySelector('.pipeline-dot');
        if (vdot) {
          if (value === 'done') vdot.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
          else vdot.innerHTML = '<span class="dot-num">' + step + '</span>';
        }
      }
      const upload = document.getElementById('upload-' + step);
      if (upload) upload.style.display = value === 'done' ? '' : 'none';
      if (step === 7 && value === 'done') alert('Pastikan kirim buktinya di WhatsApp!');
    }

    // Toggle — PC: detail-stack, HP: dropdown literally below the card
    function toggleStep(step) {
      const panel = document.getElementById('panel-' + step);
      if (!panel) return;
      const isDesktop = window.matchMedia('(min-width: 769px)').matches;
      const wasOpen = panel.classList.contains('open');

      // close others
      document.querySelectorAll('.detail-panel.open').forEach(function(p) {
        if (p !== panel) {
          p.classList.remove('open');
          const idx = p.id.replace('panel-','');
          const n = document.querySelector('.stepper-node[data-step="' + idx + '"]');
          if (n) n.setAttribute('aria-selected','false');
          const mt = document.getElementById('m-expand-text-' + idx);
          if (mt) mt.textContent = 'Lihat detail';
        }
      });
      document.querySelectorAll('.stepper-node').forEach(function(n){
        if (parseInt(n.getAttribute('data-step'),10) !== step) n.setAttribute('aria-selected','false');
      });

      if (isDesktop) {
        // ensure panel is in detail-stack for PC
        const ds = document.querySelector('.detail-stack');
        if (ds && panel.parentElement !== ds) ds.appendChild(panel);
        if (wasOpen) {
          panel.classList.remove('open');
          const sn = document.querySelector('.stepper-node[data-step="' + step + '"]');
          if (sn) sn.setAttribute('aria-selected','false');
        } else {
          panel.classList.add('open');
          const sn = document.querySelector('.stepper-node[data-step="' + step + '"]');
          if (sn) sn.setAttribute('aria-selected','true');
          try { panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch(e){}
        }
        const mt = document.getElementById('m-expand-text-' + step);
        if (mt) mt.textContent = wasOpen ? 'Lihat detail' : 'Lihat detail';
      } else {
        // HP: move panel to directly below the tapped vertical card (dropdown)
        const vCard = document.querySelector('.pipeline-vertical .pipeline-step[data-step="' + step + '"]');
        const mText = document.getElementById('m-expand-text-' + step);
        if (!vCard) {
          // fallback: toggle in place
          if (wasOpen) {
            panel.classList.remove('open');
            if (mText) mText.textContent = 'Lihat detail';
          } else {
            panel.classList.add('open');
            if (mText) mText.textContent = 'Tutup';
          }
          return;
        }
        if (wasOpen) {
          panel.classList.remove('open');
          if (mText) mText.textContent = 'Lihat detail';
          // keep it after card but hidden — no need to move back
        } else {
          // move panel to after this card so it drops down below it
          if (panel.parentElement !== vCard.parentElement || panel.previousElementSibling !== vCard) {
            vCard.parentNode.insertBefore(panel, vCard.nextSibling);
          }
          panel.classList.add('open');
          if (mText) mText.textContent = 'Tutup';
          try { panel.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(e){}
        }
        // reset other mobile texts
        document.querySelectorAll('[id^="m-expand-text-"]').forEach(function(t){
          const idx = parseInt(t.id.replace('m-expand-text-',''),10);
          if (idx !== step) t.textContent = 'Lihat detail';
        });
      }
      const bd = document.getElementById('editBackdrop');
      if (bd) bd.style.display = 'none';
      document.body.style.overflow = '';
    }
    function closeAllEditPanels() {
      document.querySelectorAll('.detail-panel.open').forEach(function(p){
        p.classList.remove('open');
        // move back to detail-stack for PC consistency
        const ds = document.querySelector('.detail-stack');
        if (ds && p.parentElement !== ds) {
          // only move back if currently after a vertical card and we are on desktop? Keep in place for mobile hidden is fine
          // move back to stack so next desktop open is in correct place
          if (window.matchMedia('(min-width: 769px)').matches) ds.appendChild(p);
        }
      });
      document.querySelectorAll('.stepper-node').forEach(function(n){ n.setAttribute('aria-selected','false'); });
      document.querySelectorAll('[id^="m-expand-text-"]').forEach(function(t){ t.textContent = 'Lihat detail'; });
      const bd = document.getElementById('editBackdrop');
      if (bd) bd.style.display = 'none';
      document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAllEditPanels(); });

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

    // Guide modal
    function openGuide() {
      const m = document.getElementById('guideModal');
      if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    }
    function closeGuide() {
      const m = document.getElementById('guideModal');
      if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
    }
    function filterGuide(q) {
      const needle = (q || '').toLowerCase().trim();
      document.querySelectorAll('.guide-steps li').forEach(function(li){
        const hay = ((li.getAttribute('data-keywords') || '') + ' ' + li.textContent).toLowerCase();
        li.style.display = !needle || hay.includes(needle) ? '' : 'none';
      });
    }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { const gm = document.getElementById('guideModal'); if (gm && gm.style.display !== 'none') closeGuide(); } });
    document.addEventListener('DOMContentLoaded', function() {
      const gm = document.getElementById('guideModal');
      if (gm) gm.addEventListener('click', function(e) { if (e.target === gm) closeGuide(); });
    });

    // Customer switcher
    function goCustomer() {
      const input = document.getElementById('customerSearch');
      const val = (input && input.value ? input.value.trim() : '');
      if (!val) { alert('Ketik No HP atau Email customer dulu.'); return; }
      const dl = document.getElementById('customerList');
      let id = null;
      if (dl) {
        for (const opt of dl.options) {
          if (opt.value === val) { id = opt.getAttribute('data-id'); break; }
        }
        if (!id) {
          const needle = val.split(' — ')[0].trim();
          for (const opt of dl.options) {
            const optNoHp = opt.value.split(' — ')[0].trim();
            if (opt.value.includes(needle) || optNoHp === needle) { id = opt.getAttribute('data-id'); break; }
          }
        }
      }
      if (!id && val.includes(' — ')) {
        const nohp = val.split(' — ')[0].trim();
        if (dl) for (const opt of dl.options) {
          if (opt.value.startsWith(nohp + ' —')) { id = opt.getAttribute('data-id'); break; }
        }
      }
      if (id) location.href = '/admin/customers/' + id;
      else alert('Customer tidak ditemukan. Pilih dari daftar.');
    }
    document.addEventListener('DOMContentLoaded', function() {
      const ci = document.getElementById('customerSearch');
      if (ci) ci.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); goCustomer(); } });
    });
  </script>

  <div id="editBackdrop" class="edit-backdrop" style="display:none" onclick="closeAllEditPanels()"></div>

<div id="guideModal" class="guide-overlay" style="display:none" role="dialog" aria-modal="true">
  <div class="guide-card">
    <button class="guide-close" type="button" onclick="closeGuide()" aria-label="Tutup panduan">✕</button>
    <div class="guide-head">
      <div class="guide-icon" aria-hidden="true">🐝</div>
      <div>
        <h3>Panduan Admin</h3>
        <p class="guide-sub">Madu Wild Bee — 5 langkah untuk staf awam. Klik langkah untuk loncat ke pipeline.</p>
      </div>
    </div>
    <div class="guide-search">
      <input id="guideSearch" type="text" placeholder="Cari: login, tambah customer, upload bukti..." oninput="filterGuide(this.value)" autocomplete="off">
    </div>
    <ol class="guide-steps">
      <li data-keywords="masuk login nomor hp kata sandi"><span class="step-num">1</span><div><strong>Masuk ke Akun</strong> — buka halaman <em>Masuk</em>, ketik <em>nomor HP</em> dan <em>kata sandi</em>. Nomor HP admin sudah disiapkan kantor. Jika akun baru pertama kali, cukup ketik nomor HP yang terdaftar lalu buat kata sandi baru.</div></li>
      <li data-keywords="daftar customer tambah cari"><span class="step-num">2</span><div><strong>Lihat Daftar Customer</strong> — di bawah layar tekan tombol <em>Kelola Customer</em>. Anda akan melihat daftar semua customer. Untuk menambah, isi formulir di atas (alamat email, nomor HP untuk login, dan kata sandi) lalu tekan <em>Tambah</em>. Untuk mencari, ketik nama atau nomor HP di kotak pencarian.</div></li>
      <li data-keywords="buka pesanan pindah customer"><span class="step-num">3</span><div><strong>Buka Pesanan</strong> — pada kartu customer tekan <em>Progres</em>. Atau pakai kotak <em>Pindah Customer</em> di atas (ketik nomor HP atau email lalu tekan <em>Buka</em>). Anda akan melihat 8 tahap pesanan yang terbentang dari kiri ke kanan.</div></li>
      <li data-keywords="ubah status tanggung jawab tanggal bukti foto"><span class="step-num">4</span><div><strong>Ubah Progress</strong> — klik lingkaran nomor tahap yang ingin diubah (misal Desain Label). Kotak pengaturan akan muncul persis di bawah (di HP menempel di bawah kartu, di komputer di bawah baris). Pilih <em>Status</em> (Selesai / Dikerjakan / Ditunda), pilih siapa yang mengerjakan, isi tanggal, dan unggah foto bukti jika sudah selesai. Tahap Foto &amp; Video akan mengingatkan kirim via WhatsApp. Setelah selesai tekan tombol kuning <em>Simpan Semua</em> di bawah layar.</div></li>
      <li data-keywords="selesai lihat ganti akun"><span class="step-num">5</span><div><strong>Selesai</strong> — customer cukup buka halaman tracker di HP mereka, masuk dengan nomor HP-nya, dan hanya bisa melihat (tidak bisa mengubah). Jika perlu mengganti nomor HP atau kata sandi customer, kembali ke Daftar Customer, tekan <em>Kelola</em> pada kartunya, ubah, lalu Simpan.</div></li>
    </ol>
    <div class="guide-tip"><strong>Tips:</strong> Pipeline di komputer terbentang penuh dari angka 1 sampai 8 tanpa perlu geser. Di HP, tombol <em>Lihat detail</em> membuka kotak tepat di bawah kartu — tidak perlu scroll ke atas.</div>
    <div class="guide-actions"><button class="btn-primary" type="button" onclick="closeGuide()">Mengerti, tutup</button><span class="guide-hint">Tekan Esc atau klik luar untuk tutup</span></div>
  </div>
</div>

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
