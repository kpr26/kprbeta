@extends('layouts.panel')

@section('title', 'Pengaturan Notifikasi WhatsApp Otomatis')
@section('page-title', 'Pengaturan Notifikasi WhatsApp')
@section('page-subtitle', 'Kelola gateway Fonnte, nomor tujuan, grup WhatsApp, waktu kirim, dan template pesan otomatis')

@section('panel')
<div class="whatsapp-settings-container" style="max-width: 1380px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    {{-- ── 1. Flash Alerts ── --}}
    @if(session('success'))
        <div class="alert alert--success" style="padding: 14px 18px; border-radius: var(--radius-md); background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.35); color: #4ade80; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert--warning" style="padding: 14px 18px; border-radius: var(--radius-md); background: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.35); color: #fde047; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0;"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert--error" style="padding: 14px 18px; border-radius: var(--radius-md); background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #f87171; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ── 2. Top Header & Gateway Status Banner ── --}}
    <div class="panel" style="padding: 24px; border-radius: var(--radius-lg); background: var(--bg-card); border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 18px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #16a34a, #22c55e); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 20px rgba(34, 197, 94, 0.35);">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px;"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.26-1.5-1.4-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.17-.48-.29"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span class="badge" style="background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); font-weight: 700; font-size: 0.72rem; padding: 3px 8px;">
                            🛡️ KHUSUS SUPER ADMIN
                        </span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Fonnte Gateway Integration</span>
                    </div>
                    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--text-primary); font-family: var(--font-serif, serif);">
                        Otomatisasi Notifikasi WhatsApp (Executive Pulse)
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 4px 0 0;">
                        Laporan otomatis harian jam <b>{{ $setting->scheduled_time }} WIB</b> (Senin–Minggu) berisikan executive summary &amp; reminder logsheet yang belum dicatat.
                    </p>
                </div>
            </div>

            {{-- Status Badges --}}
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="background: var(--bg-card-subtle); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 16px; text-align: right;">
                    <span style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Status Gateway Fonnte</span>
                    @if($isFonnteConfigured)
                        <span style="color: #22c55e; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 8px #22c55e;"></span>
                            Token Terhubung
                        </span>
                    @else
                        <span style="color: #ef4444; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#ef4444;"></span>
                            Token Belum Terhubung
                        </span>
                    @endif
                </div>

                <div style="background: var(--bg-card-subtle); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 16px; text-align: right;">
                    <span style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Status Otomatisasi</span>
                    @if($setting->is_active)
                        <span style="color: #38bdf8; font-weight: 700; font-size: 0.85rem;">⏰ Aktif ({{ $setting->scheduled_time }} WIB)</span>
                    @else
                        <span style="color: #94a3b8; font-weight: 700; font-size: 0.85rem;">⏸️ Dinonaktifkan</span>
                    @endif
                </div>

                <div style="background: var(--bg-card-subtle); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 16px; text-align: right;">
                    <span style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Cron Job Server</span>
                    @if($isSchedulerTicking)
                        <span style="color: #22c55e; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 8px #22c55e;"></span>
                            {{ $schedulerStatusText }}
                        </span>
                    @else
                        <span style="color: #f59e0b; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#f59e0b;"></span>
                            {{ $schedulerStatusText }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if(! $isSchedulerTicking)
            <div style="margin-top: 16px; padding: 12px 16px; border-radius: var(--radius-sm); background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); display: flex; align-items: flex-start; gap: 12px;">
                <span style="font-size: 1.2rem; line-height: 1;">💡</span>
                <div style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5;">
                    <b style="color: #f59e0b;">Cron Job Server Hostinger / cPanel belum aktif atau belum berdetak.</b><br>
                    Agar notifikasi dapat terkirim secara otomatis tepat pada jam yang ditentukan tanpa perlu klik manual, daftarkan baris Cron Job berikut di panel hosting Anda:
                    <div style="margin: 8px 0; background: #0f172a; padding: 8px 12px; border-radius: 6px; font-family: monospace; font-size: 0.8rem; color: #38bdf8; display: flex; justify-content: space-between; align-items: center;">
                        <span>/usr/bin/php /home/u849135675/domains/kproperation.com/public_html/artisan schedule:run</span>
                    </div>
                    <span style="font-size: 0.74rem; color: var(--text-muted);">
                        Pilih tipe <b>Kustom (Custom)</b> dan interval <b>Setiap menit (<code>* * * * *</code>)</b> di menu <i>Cron Jobs</i> Hostinger. Hindari penggunaan <code>cd</code> dan <code>&gt;&gt; /dev/null</code> agar Hostinger dapat mengeksekusi binary PHP secara langsung.
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- ── 3. Main Two-Column Layout ── --}}
    <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 24px; align-items: start;">

        {{-- ── KOLOM KIRI: FORM PENGATURAN ── --}}
        <form method="POST" action="{{ route('admin.whatsapp.update') }}" id="waSettingsForm" style="display: flex; flex-direction: column; gap: 20px; margin: 0;">
            @csrf
            @method('PUT')

            {{-- CARD 0: Kredensial API Gateway Fonnte (Bisa Diisi / Diedit Langsung) --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.1rem;">🔑</span>
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                                Token API Gateway Fonnte
                            </h3>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                Kredensial otorisasi akun Fonnte untuk mengirimkan pesan WhatsApp
                            </span>
                        </div>
                    </div>
                    @if($isFonnteConfigured)
                        <span class="badge" style="background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.35); font-weight: 700; font-size: 0.75rem; padding: 4px 10px;">
                            🟢 Token Terhubung
                        </span>
                    @else
                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.35); font-weight: 700; font-size: 0.75rem; padding: 4px 10px;">
                            🔴 Token Belum Diisi
                        </span>
                    @endif
                </div>

                <div>
                    <label for="fonnte_token" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 6px;">
                        Fonnte Account Token:
                    </label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" id="fonnte_token" name="fonnte_token" value="{{ old('fonnte_token', $setting->getFonnteToken()) }}" placeholder="Masukkan Token Fonnte (contoh: 7vPVyswvCqqpoUqiPMa2)" class="input" style="width: 100%; font-size: 0.9rem; font-family: monospace; padding-right: 44px;" required>
                        <button type="button" onclick="toggleTokenVisibility()" style="position: absolute; right: 8px; background: transparent; border: none; cursor: pointer; color: var(--text-muted); padding: 4px 8px; font-size: 0.85rem;" title="Lihat/Sembunyikan Token">
                            👁️
                        </button>
                    </div>
                    <small style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 6px;">
                        Token dapat disalin dari menu <b>Device &gt; Edit / Token</b> di akun <a href="https://fonnte.com" target="_blank" style="color: #38bdf8; text-decoration: underline;">fonnte.com</a>. Token ini tersimpan aman di database aplikasi.
                    </small>
                </div>
            </div>

            {{-- CARD 1: Jadwal & Waktu Kirim --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                    <span style="font-size: 1.1rem;">⏰</span>
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                        Jadwal &amp; Waktu Kirim Otomatis
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #22c55e;">
                        <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                            Aktifkan Pengiriman Otomatis Harian (Senin s/d Minggu)
                        </span>
                    </label>

                    <div style="display: grid; grid-template-columns: 180px 1fr; gap: 16px; align-items: center;">
                        <div>
                            <label for="scheduled_time" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 6px;">
                                Jam Kirim (WIB):
                            </label>
                            <input type="time" id="scheduled_time" name="scheduled_time" value="{{ old('scheduled_time', $setting->scheduled_time) }}" required class="input" style="font-size: 1.1rem; font-weight: 700; padding: 8px 12px; width: 100%;">
                        </div>
                        <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0; line-height: 1.45;">
                            Notifikasi akan dieksekusi tepat pada jam ini setiap hari oleh scheduler sistem untuk mengevaluasi kelengkapan logsheet hari berjalan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- CARD 2: Grup WhatsApp Tujuan (Bisa Lebih Dari 1 Grup + Opsi Select / Unselect) --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.1rem;">👥</span>
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                                Grup WhatsApp Tujuan
                            </h3>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                Anda dapat menambahkan lebih dari 1 grup dan memilih (centang) grup mana saja yang aktif dikirimkan notifikasi.
                            </span>
                        </div>
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="send_to_group" value="1" {{ $setting->send_to_group ? 'checked' : '' }} style="accent-color: #22c55e;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">Kirim ke Grup</span>
                    </label>
                </div>

                {{-- Action Bar: Tambah Grup, Select All, Unselect All --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Pilihan:</span>
                        <button type="button" onclick="toggleAllGroups(true)" class="btn btn-sm" style="font-size: 0.72rem; padding: 4px 10px; background: rgba(34,197,94,0.12); color: #22c55e; border: 1px solid rgba(34,197,94,0.3);">
                            ✓ Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllGroups(false)" class="btn btn-sm" style="font-size: 0.72rem; padding: 4px 10px; background: rgba(148,163,184,0.12); color: var(--text-secondary); border: 1px solid var(--border);">
                            ✕ Batal Pilih Semua
                        </button>
                    </div>
                    <button type="button" onclick="addGroupRow()" class="btn btn-sm" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.35); font-weight: 600; font-size: 0.78rem;">
                        + Tambah Grup
                    </button>
                </div>

                {{-- Table Header --}}
                <div style="display: grid; grid-template-columns: 85px 1.25fr 1fr 38px; gap: 10px; padding: 6px 10px; background: var(--bg-card-subtle); border-radius: var(--radius-sm); font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                    <div>Kirim?</div>
                    <div>ID Grup WhatsApp (Fonnte)</div>
                    <div>Nama / Label Grup</div>
                    <div>Aksi</div>
                </div>

                {{-- Repeater Container --}}
                <div id="groupsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    @php
                        $groupsList = old('groups', $setting->target_groups ?? []);
                        if (empty($groupsList) && ! empty($setting->target_group_id)) {
                            $groupsList = [
                                [
                                    'id'        => $setting->target_group_id,
                                    'name'      => $setting->target_group_name ?: 'Grup WhatsApp KPR',
                                    'is_active' => true,
                                ]
                            ];
                        }
                    @endphp

                    @forelse($groupsList as $idx => $group)
                        @php
                            $gId = is_array($group) ? ($group['id'] ?? '') : $group;
                            $gName = is_array($group) ? ($group['name'] ?? '') : '';
                            $gActive = is_array($group) ? ($group['is_active'] ?? true) : true;
                            $gActive = filter_var($gActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $gActive;
                        @endphp
                        <div class="group-row" style="display: grid; grid-template-columns: 85px 1.25fr 1fr 38px; gap: 10px; align-items: center; padding: 8px; border-radius: var(--radius-sm); border: 1px solid {{ $gActive ? 'rgba(34,197,94,0.25)' : 'var(--border)' }}; background: {{ $gActive ? 'rgba(34,197,94,0.03)' : 'transparent' }}; transition: all 0.2s ease;">
                            {{-- Checkbox Select / Unselect --}}
                            <div style="display: flex; align-items: center; gap: 6px; padding-left: 4px;">
                                <input type="hidden" name="groups[{{ $idx }}][is_active]" value="0">
                                <input type="checkbox" name="groups[{{ $idx }}][is_active]" value="1" {{ $gActive ? 'checked' : '' }} class="group-checkbox" onchange="updateGroupRowState(this)" style="width: 17px; height: 17px; accent-color: #22c55e; cursor: pointer;">
                                <span class="group-status-badge" style="font-size: 0.72rem; font-weight: 700; color: {{ $gActive ? '#22c55e' : '#94a3b8' }};">
                                    {{ $gActive ? 'Aktif' : 'Lewati' }}
                                </span>
                            </div>

                            {{-- Input ID Grup --}}
                            <div>
                                <input type="text" name="groups[{{ $idx }}][id]" value="{{ $gId }}" placeholder="Contoh: 120363024823812933@g.us" class="input" style="width: 100%; font-size: 0.82rem; font-family: monospace;" required>
                            </div>

                            {{-- Input Label Grup --}}
                            <div>
                                <input type="text" name="groups[{{ $idx }}][name]" value="{{ $gName }}" placeholder="Nama / Label Grup" class="input" style="width: 100%; font-size: 0.82rem;">
                            </div>

                            {{-- Delete Button --}}
                            <button type="button" onclick="removeGroupRow(this)" class="btn btn-sm btn-action--delete" style="padding: 7px; height: 36px; display: flex; align-items: center; justify-content: center;" title="Hapus grup ini">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Zm3.58 0a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    @empty
                        <div id="noGroupsNotice" style="text-align: center; padding: 18px; color: var(--text-muted); font-size: 0.85rem; border: 1px dashed var(--border); border-radius: var(--radius-sm);">
                            Belum ada grup WhatsApp tujuan yang ditambahkan. Klik tombol <b>+ Tambah Grup</b> untuk memasukkan ID grup WhatsApp Fonnte.
                        </div>
                    @endforelse
                </div>

                <div style="margin-top: 10px; padding: 8px 12px; background: var(--bg-card-subtle); border-radius: var(--radius-sm); border-left: 3px solid #38bdf8;">
                    <p style="margin: 0; font-size: 0.74rem; color: var(--text-muted); line-height: 1.4;">
                        💡 <b>Tips ID Grup Fonnte:</b> ID grup WhatsApp di Fonnte berakhiran <code>@g.us</code>. Anda dapat melihat ID seluruh grup yang tergabung pada akun bot di dashboard <b>fonnte.com</b> (menu <i>Device &gt; Groups</i>). Hanya grup yang dicentang (<b>Aktif</b>) yang akan dikirimi notifikasi.
                    </p>
                </div>
            </div>

            {{-- CARD 3: Nomor WhatsApp Terpilih (Tambah / Hapus Nomor Tujuan + Opsi Select / Unselect) --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.1rem;">📱</span>
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                                Nomor WhatsApp Terpilih (Personal)
                            </h3>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                Kirim notifikasi langsung secara personal ke nomor-nomor tertentu (GM, Supervisor, dll.) dengan kontrol centang aktif/lewati.
                            </span>
                        </div>
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="send_to_numbers" value="1" {{ $setting->send_to_numbers ? 'checked' : '' }} style="accent-color: #22c55e;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">Kirim ke Nomor Ini</span>
                    </label>
                </div>

                {{-- Action Bar: Tambah Nomor, Select All, Unselect All --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Pilihan:</span>
                        <button type="button" onclick="toggleAllPhones(true)" class="btn btn-sm" style="font-size: 0.72rem; padding: 4px 10px; background: rgba(34,197,94,0.12); color: #22c55e; border: 1px solid rgba(34,197,94,0.3);">
                            ✓ Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllPhones(false)" class="btn btn-sm" style="font-size: 0.72rem; padding: 4px 10px; background: rgba(148,163,184,0.12); color: var(--text-secondary); border: 1px solid var(--border);">
                            ✕ Batal Pilih Semua
                        </button>
                    </div>
                    <button type="button" onclick="addPhoneRow()" class="btn btn-sm" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.35); font-weight: 600; font-size: 0.78rem;">
                        + Tambah Nomor
                    </button>
                </div>

                {{-- Table Header --}}
                <div style="display: grid; grid-template-columns: 85px 1.25fr 1fr 38px; gap: 10px; padding: 6px 10px; background: var(--bg-card-subtle); border-radius: var(--radius-sm); font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                    <div>Kirim?</div>
                    <div>Nomor WhatsApp (08xx / 628xx)</div>
                    <div>Nama / Jabatan</div>
                    <div>Aksi</div>
                </div>

                {{-- Table / Repeater List --}}
                <div id="phoneNumbersContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    @php
                        $phoneNumbers = old('phone_numbers', $setting->target_phone_numbers ?? []);
                    @endphp

                    @forelse($phoneNumbers as $idx => $item)
                        @php
                            $num = is_array($item) ? ($item['number'] ?? '') : $item;
                            $name = is_array($item) ? ($item['name'] ?? '') : '';
                            $pActive = is_array($item) ? ($item['is_active'] ?? true) : true;
                            $pActive = filter_var($pActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $pActive;
                        @endphp
                        <div class="phone-row" style="display: grid; grid-template-columns: 85px 1.25fr 1fr 38px; gap: 10px; align-items: center; padding: 8px; border-radius: var(--radius-sm); border: 1px solid {{ $pActive ? 'rgba(34,197,94,0.25)' : 'var(--border)' }}; background: {{ $pActive ? 'rgba(34,197,94,0.03)' : 'transparent' }}; transition: all 0.2s ease;">
                            {{-- Checkbox Select / Unselect --}}
                            <div style="display: flex; align-items: center; gap: 6px; padding-left: 4px;">
                                <input type="hidden" name="phone_numbers[{{ $idx }}][is_active]" value="0">
                                <input type="checkbox" name="phone_numbers[{{ $idx }}][is_active]" value="1" {{ $pActive ? 'checked' : '' }} class="phone-checkbox" onchange="updatePhoneRowState(this)" style="width: 17px; height: 17px; accent-color: #22c55e; cursor: pointer;">
                                <span class="phone-status-badge" style="font-size: 0.72rem; font-weight: 700; color: {{ $pActive ? '#22c55e' : '#94a3b8' }};">
                                    {{ $pActive ? 'Aktif' : 'Lewati' }}
                                </span>
                            </div>

                            <div>
                                <input type="text" name="phone_numbers[{{ $idx }}][number]" value="{{ $num }}" placeholder="Nomor WA (contoh: 08123456789)" class="input" style="width: 100%; font-size: 0.82rem;" required>
                            </div>
                            <div>
                                <input type="text" name="phone_numbers[{{ $idx }}][name]" value="{{ $name }}" placeholder="Nama / Jabatan (contoh: GM / Spv)" class="input" style="width: 100%; font-size: 0.82rem;">
                            </div>
                            <button type="button" onclick="removePhoneRow(this)" class="btn btn-sm btn-action--delete" style="padding: 7px; height: 36px; display: flex; align-items: center; justify-content: center;" title="Hapus nomor ini">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Zm3.58 0a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    @empty
                        <div id="noNumbersNotice" style="text-align: center; padding: 18px; color: var(--text-muted); font-size: 0.85rem; border: 1px dashed var(--border); border-radius: var(--radius-sm);">
                            Belum ada nomor WhatsApp khusus yang ditambahkan. Klik tombol <b>+ Tambah Nomor</b> jika ingin mengirim ke nomor personal.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- CARD 4: Isi Format Pesan Otomatis (Bisa Diedit) --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.1rem;">📝</span>
                        <div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                                Format Teks Pesan Otomatis
                            </h3>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                Sesuaikan teks pembuka, peringatan reminder, dan catatan penutup pesan WhatsApp
                            </span>
                        </div>
                    </div>
                    <span class="badge" style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.72rem;">
                        ℹ️ Data Stand Otomatis dari Logsheet
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div>
                        <label for="header_template" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">
                            Header / Judul Pesan:
                        </label>
                        <textarea id="header_template" name="header_template" rows="2" class="input" style="width: 100%; font-size: 0.85rem; font-family: monospace; resize: vertical;">{{ old('header_template', $setting->header_template) }}</textarea>
                        <small style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 2px;">
                            Gunakan tag <code>{tanggal}</code> dan <code>{waktu}</code> untuk memasukkan tanggal &amp; jam kirim dinamis.
                        </small>
                    </div>

                    <div>
                        <label for="reminder_template" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">
                            Teks Himbauan Reminder (Jika Ada Logsheet Belum Dicatat):
                        </label>
                        <textarea id="reminder_template" name="reminder_template" rows="2" class="input" style="width: 100%; font-size: 0.85rem; font-family: monospace; resize: vertical;">{{ old('reminder_template', $setting->reminder_template) }}</textarea>
                        <small style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 2px;">
                            Teks peringatan yang ditujukan kepada teknisi/tim on-duty yang sedang bertugas.
                        </small>
                    </div>

                    <div>
                        <label for="success_template" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">
                            Teks Konfirmasi Sukses (Jika Seluruh Logsheet Sudah Lengkap):
                        </label>
                        <textarea id="success_template" name="success_template" rows="2" class="input" style="width: 100%; font-size: 0.85rem; font-family: monospace; resize: vertical;">{{ old('success_template', $setting->success_template) }}</textarea>
                    </div>

                    <div>
                        <label for="footer_template" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">
                            Footer / Catatan Kaki:
                        </label>
                        <textarea id="footer_template" name="footer_template" rows="2" class="input" style="width: 100%; font-size: 0.85rem; font-family: monospace; resize: vertical;">{{ old('footer_template', $setting->footer_template) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Submit Button Bar --}}
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 4px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 700; font-size: 0.92rem; background: linear-gradient(135deg, #16a34a, #22c55e); border: none; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.4);">
                    💾 Simpan Semua Pengaturan
                </button>
            </div>
        </form>

        {{-- ── KOLOM KANAN: LIVE PREVIEW & TEST SEND ── --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">

            {{-- Card Preview Chat WhatsApp --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem;">💬</span>
                        <h3 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                            Live WhatsApp Chat Preview
                        </h3>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" onclick="copyPreviewMessage()" id="btnCopyPreview" class="btn btn-sm" style="font-size: 0.75rem; padding: 4px 10px;">
                            📋 Salin
                        </button>
                        <button type="button" onclick="refreshLivePreview()" class="btn btn-sm" style="font-size: 0.75rem; padding: 4px 10px;">
                            🔄 Segarkan
                        </button>
                    </div>
                </div>

                {{-- Filter Tanggal Preview --}}
                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <label for="previewDateSelect" style="font-size: 0.78rem; font-weight: 600; color: var(--text-secondary);">
                        Simulasi Data Tanggal:
                    </label>
                    <input type="date" id="previewDateSelect" value="{{ $todayDate }}" max="{{ $todayDate }}" onchange="refreshLivePreview()" class="input" style="padding: 5px 10px; font-size: 0.8rem;">
                </div>

                {{-- Mock WhatsApp Bubble Container --}}
                <div style="background: #0b141a; border: 1px solid #1f2c34; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.4);">
                    {{-- Header Top Bar Hijau WhatsApp --}}
                    <div style="background: #1f2c34; padding: 10px 14px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #22c55e; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.8rem;">
                            KPR
                        </div>
                        <div>
                            <div style="color: #e9edef; font-weight: 600; font-size: 0.82rem;">
                                KPR Executive Bot
                            </div>
                            <div style="color: #8696a0; font-size: 0.7rem;">
                                Bot Otomatis Fonnte · online
                            </div>
                        </div>
                    </div>

                    {{-- Chat Content Area --}}
                    <div style="padding: 16px; min-height: 280px; max-height: 480px; overflow-y: auto; position: relative;">
                        <div id="previewLoading" style="position: absolute; inset: 0; background: rgba(11,20,26,0.85); display: none; align-items: center; justify-content: center; color: #22c55e; font-size: 0.85rem; z-index: 2;">
                            Memuat pratinjau pesan...
                        </div>
                        <pre id="previewTextContent" style="margin: 0; white-space: pre-wrap; word-break: break-word; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 0.8rem; line-height: 1.45; color: #e9edef;">{{ $messagePreview }}</pre>
                    </div>
                </div>
            </div>

            {{-- Card Uji Coba Kirim Manual --}}
            <div class="panel" style="padding: 22px; border-radius: var(--radius-md); background: var(--bg-card); border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                    <span style="font-size: 1.1rem;">🚀</span>
                    <h3 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-primary);">
                        Uji Coba Kirim Sekarang (Test Send)
                    </h3>
                </div>

                @php
                    $activeGroupsCount = count($setting->getActiveGroups());
                    $activePhonesCount = count($setting->getActivePhoneNumbers());
                @endphp

                <form method="POST" action="{{ route('admin.whatsapp.test-send') }}" onsubmit="return confirm('Kirimkan notifikasi WhatsApp uji coba ini sekarang?')">
                    @csrf
                    <input type="hidden" name="date" id="testSendDate" value="{{ $todayDate }}">

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <label for="send_mode" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">
                                Target Pengiriman:
                            </label>
                            <select name="send_mode" id="send_mode" class="input" style="width: 100%; font-size: 0.85rem;" onchange="toggleCustomTargetInput(this.value)">
                                <option value="all">Semua Target Aktif ({{ $activeGroupsCount }} Grup &amp; {{ $activePhonesCount }} Nomor Terpilih)</option>
                                <option value="group_only">Hanya Grup WhatsApp Terpilih ({{ $activeGroupsCount }} grup)</option>
                                <option value="numbers_only">Hanya Nomor Terpilih ({{ $activePhonesCount }} nomor)</option>
                                <option value="custom">Kirim ke Nomor Tes Khusus...</option>
                            </select>
                        </div>

                        <div id="customTargetWrap" style="display: none;">
                            <label for="custom_target" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 4px;">
                                Masukkan Nomor WhatsApp Uji Coba:
                            </label>
                            <input type="text" name="custom_target" id="custom_target" placeholder="Contoh: 081234567890" class="input" style="width: 100%; font-size: 0.85rem;">
                        </div>

                        <button type="submit" class="btn btn-sm" style="margin-top: 6px; padding: 10px; background: #22c55e; color: #fff; font-weight: 700; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.35);">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                            Kirim Uji Coba Sekarang
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
let groupRowCounter = {{ count($groupsList ?? []) }};
let phoneRowCounter = {{ count($phoneNumbers ?? []) }};

function toggleTokenVisibility() {
    const input = document.getElementById('fonnte_token');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}

/* ── MULTI-GROUP JAVASCRIPT HELPERS ── */
function addGroupRow() {
    const container = document.getElementById('groupsContainer');
    const notice = document.getElementById('noGroupsNotice');
    if (notice) notice.remove();

    const row = document.createElement('div');
    row.className = 'group-row';
    row.style.cssText = 'display: grid; grid-template-columns: 85px 1.25fr 1fr 38px; gap: 10px; align-items: center; padding: 8px; border-radius: var(--radius-sm); border: 1px solid rgba(34,197,94,0.25); background: rgba(34,197,94,0.03); transition: all 0.2s ease;';
    row.innerHTML = `
        <div style="display: flex; align-items: center; gap: 6px; padding-left: 4px;">
            <input type="hidden" name="groups[${groupRowCounter}][is_active]" value="0">
            <input type="checkbox" name="groups[${groupRowCounter}][is_active]" value="1" checked class="group-checkbox" onchange="updateGroupRowState(this)" style="width: 17px; height: 17px; accent-color: #22c55e; cursor: pointer;">
            <span class="group-status-badge" style="font-size: 0.72rem; font-weight: 700; color: #22c55e;">Aktif</span>
        </div>
        <div>
            <input type="text" name="groups[${groupRowCounter}][id]" placeholder="Contoh: 120363024823812933@g.us" class="input" style="width: 100%; font-size: 0.82rem; font-family: monospace;" required>
        </div>
        <div>
            <input type="text" name="groups[${groupRowCounter}][name]" placeholder="Nama / Label Grup" class="input" style="width: 100%; font-size: 0.82rem;">
        </div>
        <button type="button" onclick="removeGroupRow(this)" class="btn btn-sm btn-action--delete" style="padding: 7px; height: 36px; display: flex; align-items: center; justify-content: center;" title="Hapus grup ini">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Zm3.58 0a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
        </button>
    `;
    container.appendChild(row);
    groupRowCounter++;
}

function removeGroupRow(btn) {
    const row = btn.closest('.group-row');
    if (row) row.remove();
}

function updateGroupRowState(checkbox) {
    const row = checkbox.closest('.group-row');
    const badge = row.querySelector('.group-status-badge');
    if (checkbox.checked) {
        row.style.borderColor = 'rgba(34,197,94,0.25)';
        row.style.background = 'rgba(34,197,94,0.03)';
        if (badge) {
            badge.textContent = 'Aktif';
            badge.style.color = '#22c55e';
        }
    } else {
        row.style.borderColor = 'var(--border)';
        row.style.background = 'transparent';
        if (badge) {
            badge.textContent = 'Lewati';
            badge.style.color = '#94a3b8';
        }
    }
}

function toggleAllGroups(checked) {
    const checkboxes = document.querySelectorAll('.group-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checked;
        updateGroupRowState(cb);
    });
}

/* ── MULTI-PHONE NUMBER JAVASCRIPT HELPERS ── */
function addPhoneRow() {
    const container = document.getElementById('phoneNumbersContainer');
    const notice = document.getElementById('noNumbersNotice');
    if (notice) notice.remove();

    const row = document.createElement('div');
    row.className = 'phone-row';
    row.style.cssText = 'display: grid; grid-template-columns: 85px 1.25fr 1fr 38px; gap: 10px; align-items: center; padding: 8px; border-radius: var(--radius-sm); border: 1px solid rgba(34,197,94,0.25); background: rgba(34,197,94,0.03); transition: all 0.2s ease;';
    row.innerHTML = `
        <div style="display: flex; align-items: center; gap: 6px; padding-left: 4px;">
            <input type="hidden" name="phone_numbers[${phoneRowCounter}][is_active]" value="0">
            <input type="checkbox" name="phone_numbers[${phoneRowCounter}][is_active]" value="1" checked class="phone-checkbox" onchange="updatePhoneRowState(this)" style="width: 17px; height: 17px; accent-color: #22c55e; cursor: pointer;">
            <span class="phone-status-badge" style="font-size: 0.72rem; font-weight: 700; color: #22c55e;">Aktif</span>
        </div>
        <div>
            <input type="text" name="phone_numbers[${phoneRowCounter}][number]" placeholder="Nomor WA (contoh: 08123456789)" class="input" style="width: 100%; font-size: 0.82rem;" required>
        </div>
        <div>
            <input type="text" name="phone_numbers[${phoneRowCounter}][name]" placeholder="Nama / Jabatan (contoh: Spv / GM)" class="input" style="width: 100%; font-size: 0.82rem;">
        </div>
        <button type="button" onclick="removePhoneRow(this)" class="btn btn-sm btn-action--delete" style="padding: 7px; height: 36px; display: flex; align-items: center; justify-content: center;" title="Hapus nomor ini">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Zm3.58 0a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
        </button>
    `;
    container.appendChild(row);
    phoneRowCounter++;
}

function removePhoneRow(btn) {
    const row = btn.closest('.phone-row');
    if (row) row.remove();
}

function updatePhoneRowState(checkbox) {
    const row = checkbox.closest('.phone-row');
    const badge = row.querySelector('.phone-status-badge');
    if (checkbox.checked) {
        row.style.borderColor = 'rgba(34,197,94,0.25)';
        row.style.background = 'rgba(34,197,94,0.03)';
        if (badge) {
            badge.textContent = 'Aktif';
            badge.style.color = '#22c55e';
        }
    } else {
        row.style.borderColor = 'var(--border)';
        row.style.background = 'transparent';
        if (badge) {
            badge.textContent = 'Lewati';
            badge.style.color = '#94a3b8';
        }
    }
}

function toggleAllPhones(checked) {
    const checkboxes = document.querySelectorAll('.phone-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checked;
        updatePhoneRowState(cb);
    });
}

/* ── PREVIEW & TEST SEND UTILS ── */
function toggleCustomTargetInput(mode) {
    const wrap = document.getElementById('customTargetWrap');
    if (!wrap) return;
    wrap.style.display = (mode === 'custom') ? 'block' : 'none';
}

function refreshLivePreview() {
    const dateInput = document.getElementById('previewDateSelect');
    const loading = document.getElementById('previewLoading');
    const textEl = document.getElementById('previewTextContent');
    const testDate = document.getElementById('testSendDate');

    const selectedDate = dateInput ? dateInput.value : '';
    if (testDate && selectedDate) testDate.value = selectedDate;

    if (loading) loading.style.display = 'flex';

    fetch(`{{ route('admin.whatsapp.preview') }}?date=${encodeURIComponent(selectedDate || '')}`)
        .then(res => res.json())
        .then(data => {
            if (loading) loading.style.display = 'none';
            if (data.status && textEl) {
                textEl.textContent = data.message;
            }
        })
        .catch(err => {
            if (loading) loading.style.display = 'none';
            if (textEl) {
                textEl.textContent = 'Terjadi kesalahan saat memuat preview: ' + err.message;
            }
        });
}

function copyPreviewMessage() {
    const textEl = document.getElementById('previewTextContent');
    const btn = document.getElementById('btnCopyPreview');
    if (!textEl || !textEl.textContent) return;

    navigator.clipboard.writeText(textEl.textContent).then(() => {
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '✔ Tersalin!';
            btn.style.color = '#22c55e';
            setTimeout(() => {
                btn.innerHTML = original;
                btn.style.color = '';
            }, 2000);
        }
    }).catch(() => {
        alert('Gagal menyalin pesan.');
    });
}
</script>
@endpush
