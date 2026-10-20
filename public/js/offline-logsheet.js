/**
 * KPR Beta - Offline-First Logsheet Handler for Mobile / HP Teknisi
 * Menggunakan HTML5 IndexedDB untuk menyimpan data stand & file foto meteran secara aman saat tanpa sinyal.
 * Data akan otomatis dikirim (auto-sync) begitu HP mendapatkan sinyal kembali.
 */

(function () {
    'use strict';

    const DB_NAME = 'kprbeta_offline_logsheet_db';
    const DB_VERSION = 1;
    const STORE_NAME = 'pending_logsheets';

    // Helper: Buka atau inisialisasi IndexedDB
    function openDB() {
        return new Promise((resolve, reject) => {
            if (!('indexedDB' in window)) {
                reject(new Error('IndexedDB tidak didukung pada browser ini.'));
                return;
            }
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    const store = db.createObjectStore(STORE_NAME, { keyPath: 'id' });
                    store.createIndex('type', 'type', { unique: false });
                    store.createIndex('createdAt', 'createdAt', { unique: false });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Helper: Simpan data logsheet ke IndexedDB
    async function savePendingLogsheet(item) {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const req = store.put(item);
            req.onsuccess = () => resolve(item);
            req.onerror = () => reject(req.error);
        });
    }

    // Helper: Ambil semua logsheet tertunda dari IndexedDB
    async function getPendingLogsheets() {
        try {
            const db = await openDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(STORE_NAME, 'readonly');
                const store = tx.objectStore(STORE_NAME);
                const req = store.getAll();
                req.onsuccess = () => resolve(req.result || []);
                req.onerror = () => reject(req.error);
            });
        } catch (e) {
            console.warn('Gagal membaca pending logsheets:', e);
            return [];
        }
    }

    // Helper: Hapus data logsheet dari IndexedDB berdasarkan ID
    async function deletePendingLogsheet(id) {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const req = store.delete(id);
            req.onsuccess = () => resolve(true);
            req.onerror = () => reject(req.error);
        });
    }

    // Helper: Ambil CSRF token saat ini
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Helper: Refresh CSRF token jika kadaluarsa (HTTP 419)
    async function refreshCsrfToken() {
        try {
            const res = await fetch('/logsheet/hub', { credentials: 'same-origin', cache: 'no-store' });
            if (res.ok) {
                const html = await res.text();
                const match = html.match(/<meta\s+name=["']csrf-token["']\s+content=["']([^"']+)["']/i);
                if (match && match[1]) {
                    const token = match[1];
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.setAttribute('content', token);
                    return token;
                }
            }
        } catch (e) {
            console.warn('Gagal me-refresh CSRF token:', e);
        }
        return getCsrfToken();
    }

    // Notifikasi Toast / Floating Banner
    function showToast(msg, type = 'info', duration = 5000) {
        let container = document.getElementById('offline-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'offline-toast-container';
            container.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;gap:8px;max-width:92%;width:420px;pointer-events:none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.style.cssText = `pointer-events:auto;background:#0f172a;color:#fff;padding:12px 16px;border-radius:12px;font-size:0.88rem;box-shadow:0 8px 24px rgba(0,0,0,0.5);display:flex;align-items:center;gap:12px;border:1px solid ${
            type === 'success' ? 'rgba(16,185,129,0.5)' : type === 'warn' ? 'rgba(245,158,11,0.5)' : 'rgba(56,189,248,0.5)'
        };animation:slideUp 0.3s ease;`;

        const icon = type === 'success' ? '✓' : type === 'warn' ? '⚠' : 'ℹ';
        const iconColor = type === 'success' ? '#34d399' : type === 'warn' ? '#fbbf24' : '#38bdf8';

        toast.innerHTML = `
            <div style="background:${iconColor}22;color:${iconColor};width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;flex-shrink:0;">${icon}</div>
            <div style="flex:1;line-height:1.4;">${msg}</div>
            <button type="button" style="background:none;border:none;color:#94a3b8;font-size:1.1rem;cursor:pointer;padding:0 4px;" onclick="this.parentElement.remove()">✕</button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    }

    // Modal sukses simpan offline
    function showOfflineSavedModal(type, targetUrl, customTitle) {
        let typeLabel = 'Logsheet Utilitas';
        if (type === 'listrik') typeLabel = 'Logsheet AMR PLN T33N';
        else if (type === 'air') typeLabel = 'Logsheet Air (Water Meter Gedung)';
        else if (type === 'public_area_water') typeLabel = customTitle || 'Logsheet Water Meter Public Area';
        else if (type === 'public_area_electricity') typeLabel = 'Logsheet KWh Meter Public Area';
        else if (type === 'public_area_lvsb') typeLabel = 'Logsheet LVSB (Low Voltage Switchboard)';

        const defaultBack = type.startsWith('public_area') ? '/logsheet-public-area' : '/logsheet/hub';
        const backUrl = targetUrl || defaultBack;
        const backText = type.startsWith('public_area') ? 'Kembali ke Menu Public Area' : 'Kembali ke Menu Logsheet';

        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:999999;display:flex;align-items:center;justify-content:center;padding:20px;animation:fadeIn 0.25s ease;';

        overlay.innerHTML = `
            <div style="background:#0f172a;border:1px solid rgba(245,158,11,0.4);border-radius:18px;max-width:440px;width:100%;padding:26px;color:#fff;box-shadow:0 20px 40px rgba(0,0,0,0.6);text-align:center;">
                <div style="width:60px;height:60px;border-radius:50%;background:rgba(245,158,11,0.15);border:2px solid #fbbf24;display:flex;align-items:center;justify-content:center;margin:0 auto 16px auto;font-size:1.8rem;color:#fbbf24;">
                    💾
                </div>
                <h3 style="font-size:1.25rem;font-weight:700;margin:0 0 8px;color:#fff;">Tersimpan di Memori HP</h3>
                <div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);border-radius:8px;padding:8px 12px;font-size:0.82rem;color:#fbbf24;margin-bottom:14px;font-weight:600;">
                    Mode Offline (Tanpa Sinyal)
                </div>
                <p style="font-size:0.88rem;color:#94a3b8;line-height:1.5;margin:0 0 20px;">
                    Data stand dan foto fisik meteran untuk <b>${typeLabel}</b> berhasil diamankan di penyimpanan lokal HP Anda.<br><br>
                    <span style="color:#e2e8f0;">Data akan <b>otomatis terkirim ke server</b> begitu HP Anda mendeteksi sinyal / koneksi internet kembali.</span>
                </p>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <a href="${backUrl}" style="display:block;background:linear-gradient(135deg,#c8aa6e,#b89248);color:#0a0e1a;padding:12px 18px;border-radius:10px;font-weight:700;text-decoration:none;font-size:0.92rem;">
                        ${backText}
                    </a>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
    }

    // Sinkronisasi data dari IndexedDB ke Server
    let isSyncing = false;
    async function syncPendingLogsheets() {
        if (isSyncing) return;
        if (!navigator.onLine) return;

        const pendingItems = await getPendingLogsheets();
        if (!pendingItems.length) {
            updateOfflineUI([]);
            return;
        }

        isSyncing = true;
        updateOfflineUI(pendingItems, true);

        let successCount = 0;
        let token = getCsrfToken();

        for (const item of pendingItems) {
            try {
                const formData = new FormData();
                formData.append('_token', token);

                // Tambahkan field teks
                if (item.fields) {
                    for (const [key, val] of Object.entries(item.fields)) {
                        if (val !== null && val !== undefined) {
                            formData.append(key, val);
                        }
                    }
                }

                // Tambahkan tanggal pencatatan asli saat disimpan offline
                if (item.tanggal) {
                    formData.append('tanggal_pencatatan', item.tanggal);
                }

                // Tambahkan file foto dari Blob
                if (item.photoBlob) {
                    const fileField = item.fileField || (item.type === 'listrik' ? 'foto_kwh' : (item.type === 'air' ? 'foto_air' : 'foto'));
                    const fileObj = new File([item.photoBlob], item.photoName || `${fileField}_${Date.now()}.jpg`, {
                        type: item.photoType || 'image/jpeg'
                    });
                    formData.append(fileField, fileObj);
                }

                // Kirim via fetch dengan request headers JSON
                let response = await fetch(item.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                // Jika CSRF expired (HTTP 419), refresh token dan coba sekali lagi
                if (response.status === 419) {
                    console.log('CSRF token expired, mencoba refresh...');
                    token = await refreshCsrfToken();
                    formData.set('_token', token);
                    response = await fetch(item.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                }

                if (response.ok) {
                    const resJson = await response.json();
                    await deletePendingLogsheet(item.id);
                    successCount++;
                    const typeName = item.meterName || (item.type === 'listrik' ? 'AMR PLN T33N' : (item.type === 'air' ? 'Air Gedung' : (item.type === 'public_area_water' ? 'Water Meter Public Area' : (item.type === 'public_area_lvsb' ? 'LVSB Public Area' : 'KWh Meter Public Area'))));
                    showToast(`✓ Logsheet ${typeName} tanggal ${item.tanggal} berhasil disinkronkan ke server!`, 'success');
                } else if (response.status === 403 || response.status === 422) {
                    // Cek pesan error dari server
                    const errJson = await response.json().catch(() => ({}));
                    const errMsg = errJson.message || 'Data tidak dapat diproses oleh server.';
                    console.warn(`Sync ditolak untuk item ${item.id}:`, errMsg);
                    // Jika memang sudah pernah diinput hari ini, hapus antrean agar tidak loop
                    if (response.status === 403 && errMsg.includes('hak akses')) {
                        await deletePendingLogsheet(item.id);
                        showToast(`ℹ ${errMsg}`, 'warn', 7000);
                    } else {
                        showToast(`⚠ Gagal sinkron: ${errMsg}`, 'warn', 6000);
                    }
                } else {
                    console.warn(`Sync error HTTP ${response.status} untuk item ${item.id}`);
                }
            } catch (err) {
                console.warn('Koneksi terputus saat sinkronisasi:', err);
                break; // Hentikan loop jika koneksi tiba-tiba putus kembali
            }
        }

        isSyncing = false;
        const remaining = await getPendingLogsheets();
        updateOfflineUI(remaining);

        if (successCount > 0 && window.location.pathname.includes('/logsheet/hub')) {
            // Segarkan data tampilan hub jika ada logsheet yang berhasil masuk
            setTimeout(() => window.location.reload(), 1500);
        }
    }

    // Perbarui UI Banner & Indikator Antrean
    function updateOfflineUI(pendingItems = [], isSyncActive = false) {
        // 1. Indikator Status Banner di Header/Main
        let banner = document.getElementById('offline-sync-banner');
        const container = document.getElementById('offline-sync-banner-container');
        const isOffline = !navigator.onLine;

        if (isOffline || pendingItems.length > 0) {
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'offline-sync-banner';
                banner.style.cssText = 'margin-bottom:16px;padding:12px 16px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:0.86rem;transition:all 0.3s;';
                if (container) {
                    container.appendChild(banner);
                } else {
                    const mainPanel = document.querySelector('main.main');
                    if (mainPanel) {
                        mainPanel.insertBefore(banner, mainPanel.firstChild);
                    }
                }
            }

            if (isOffline) {
                banner.style.background = 'rgba(245,158,11,0.15)';
                banner.style.border = '1px solid rgba(245,158,11,0.35)';
                banner.style.color = '#fbbf24';
                banner.innerHTML = `
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:1.15rem;display:inline-block;animation:pulse 1.5s infinite;">⚡</span>
                        <div>
                            <strong style="color:#fff;">Mode Offline Aktif (Tanpa Sinyal)</strong>
                            <div style="font-size:0.8rem;color:#e2e8f0;margin-top:2px;">
                                Anda tetap dapat menginput stand &amp; mengambil foto. Data tersimpan di memori HP dan otomatis terkirim saat sinyal pulih.
                            </div>
                        </div>
                    </div>
                    ${pendingItems.length > 0 ? `
                        <span style="background:rgba(245,158,11,0.25);padding:4px 10px;border-radius:6px;font-weight:600;font-size:0.8rem;">
                            ${pendingItems.length} data tersimpan di HP
                        </span>
                    ` : ''}
                `;
            } else if (pendingItems.length > 0) {
                banner.style.background = 'rgba(56,189,248,0.12)';
                banner.style.border = '1px solid rgba(56,189,248,0.3)';
                banner.style.color = '#38bdf8';
                banner.innerHTML = `
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:1.15rem;">📥</span>
                        <div>
                            <strong style="color:#fff;">${pendingItems.length} Data Logsheet Menunggu Sinkronisasi</strong>
                            <div style="font-size:0.8rem;color:#cbd5e1;margin-top:2px;">
                                Sinyal telah terdeteksi. ${isSyncActive ? 'Sedang mengirim data ke server...' : 'Siap dikirim ke server.'}
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" id="btn-manual-sync" style="background:#0284c7;color:#fff;border:none;padding:6px 14px;border-radius:8px;font-weight:600;font-size:0.82rem;cursor:pointer;" ${isSyncActive ? 'disabled' : ''}>
                            ${isSyncActive ? 'Mengirim...' : 'Sinkronkan Sekarang'}
                        </button>
                    </div>
                `;
                const syncBtn = banner.querySelector('#btn-manual-sync');
                if (syncBtn && !isSyncActive) {
                    syncBtn.addEventListener('click', () => syncPendingLogsheets());
                }
            }
        } else if (banner) {
            banner.remove();
        }

        // 2. Hub Card Badge Updates
        const elecCard = document.querySelector('.logsheet-choice-card--elec .choice-status-box');
        const waterCard = document.querySelector('.logsheet-choice-card--water .choice-status-box');

        const pendingElec = pendingItems.find(i => i.type === 'listrik');
        const pendingWater = pendingItems.find(i => i.type === 'air');

        if (pendingElec && elecCard) {
            let badge = elecCard.querySelector('.badge-offline-queue');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'badge badge-offline-queue';
                badge.style.cssText = 'font-size:0.8rem;padding:6px 12px;background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.4);border-radius:6px;display:inline-block;margin-top:6px;font-weight:600;';
                elecCard.appendChild(badge);
            }
            badge.innerHTML = '💾 Tersimpan di HP (Menunggu Sinyal)';
        }

        if (pendingWater && waterCard) {
            let badge = waterCard.querySelector('.badge-offline-queue');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'badge badge-offline-queue';
                badge.style.cssText = 'font-size:0.8rem;padding:6px 12px;background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.4);border-radius:6px;display:inline-block;margin-top:6px;font-weight:600;';
                waterCard.appendChild(badge);
            }
            badge.innerHTML = '💾 Tersimpan di HP (Menunggu Sinyal)';
        }
    }

    // Intersepsi Form Logsheet Listrik & Air & Public Area
    function setupFormInterception() {
        const elecForm = document.getElementById('logsheet-listrik-form');
        const waterForm = document.getElementById('logsheet-air-form');

        if (elecForm) {
            handleFormSubmit(elecForm, 'listrik');
        }
        if (waterForm) {
            handleFormSubmit(waterForm, 'air');
        }

        // Public Area Water Forms (Cooling Tower 12A, Toilet Sky Lobby 11 & Mushola 12, Pantry Office)
        document.querySelectorAll('.form-offline-pa-water, [data-offline-type="public_area_water"]').forEach(form => {
            handleFormSubmit(form, 'public_area_water');
        });

        // Public Area Electricity Forms (KWh Meter Public Area)
        document.querySelectorAll('.form-offline-pa-electricity, [data-offline-type="public_area_electricity"]').forEach(form => {
            handleFormSubmit(form, 'public_area_electricity');
        });

        // Public Area LVSB Forms (Low Voltage Switchboard)
        document.querySelectorAll('.form-offline-pa-lvsb, [data-offline-type="public_area_lvsb"]').forEach(form => {
            handleFormSubmit(form, 'public_area_lvsb');
        });
    }

    function handleFormSubmit(form, type) {
        form.addEventListener('submit', async function (e) {
            // Jika perangkat offline, kita WAJIB simpan ke IndexedDB
            const isOffline = !navigator.onLine;

            if (isOffline) {
                e.preventDefault();
                await processOfflineSubmission(form, type);
                return;
            }

            // Jika online, coba kirim via fetch dengan timeout pendek (12s).
            // Jika gagal karena koneksi tiba-tiba putus / no internet, fallback ke IndexedDB!
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg style="animation:spin 1s linear infinite;width:18px;height:18px;display:inline-block;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                    </svg> Mengirim...
                `;
            }

            try {
                const formData = new FormData(form);
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 12000);

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData,
                    signal: controller.signal
                });
                clearTimeout(timeoutId);

                if (response.ok) {
                    const res = await response.json();
                    window.location.href = res.target || '/logsheet/hub';
                    return;
                } else if (response.status === 422) {
                    // Validasi form gagal dari server
                    const err = await response.json();
                    alert(err.message || 'Mohon periksa isian angka stand dan foto.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    return;
                } else if (response.status === 403) {
                    const err = await response.json();
                    alert(err.message || 'Hak akses ditolak.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    return;
                } else {
                    throw new Error('Server returned ' + response.status);
                }
            } catch (err) {
                console.warn('Gagal submit online, fallback ke penyimpanan offline:', err);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                // Fallback simpan ke IndexedDB
                await processOfflineSubmission(form, type);
            }
        });
    }

    // Proses penyimpanan data form ke IndexedDB
    async function processOfflineSubmission(form, type) {
        // Validasi input di sisi klien
        if (type === 'listrik') {
            const bp = form.querySelector('#stand_bp');
            const lbp1 = form.querySelector('#stand_lbp1');
            const lbp2 = form.querySelector('#stand_lbp2');
            const total = form.querySelector('#stand_total');
            const file = form.querySelector('input[name="foto_kwh"]');

            if (bp) {
                if (!bp.value || !lbp1?.value || !lbp2?.value || !total?.value) {
                    alert('Semua angka stand AMR PLN (BP, LBP 1, LBP 2, dan Total) wajib diisi.');
                    return;
                }
            } else {
                const lwbp = form.querySelector('#stand_lwbp');
                const wbp = form.querySelector('#stand_wbp');
                if (!lwbp || !lwbp.value || !wbp || !wbp.value) {
                    alert('Stand kWh wajib diisi.');
                    return;
                }
            }

            if (file && file.hasAttribute('required') && (!file.files || !file.files[0])) {
                alert('Foto fisik meteran AMR PLN T33N wajib dilampirkan.');
                return;
            }
        } else if (type === 'air') {
            const air = form.querySelector('#stand_air');
            const file = form.querySelector('input[name="foto_air"]');

            if (!air || !air.value) {
                alert('Stand Water Meter wajib diisi.');
                return;
            }
            if (file && file.hasAttribute('required') && (!file.files || !file.files[0])) {
                alert('Foto water meter wajib dilampirkan.');
                return;
            }
        } else if (type === 'public_area_water') {
            const stand = form.querySelector('[name="stand_meter"]');
            const file = form.querySelector('[name="foto"]');
            const meterLabel = form.dataset.meterLabel || 'Water Meter';

            if (!stand || !stand.value) {
                alert(`Stand ${meterLabel} wajib diisi.`);
                return;
            }
            if (file && file.hasAttribute('required') && (!file.files || !file.files[0])) {
                alert(`Foto fisik meteran ${meterLabel} wajib dilampirkan.`);
                return;
            }
        } else if (type === 'public_area_electricity') {
            const stand = form.querySelector('[name="stand_meter"]');
            const file = form.querySelector('[name="foto"]');

            if (!stand || !stand.value) {
                alert('Stand KWh Meter Public Area wajib diisi.');
                return;
            }
            if (file && file.hasAttribute('required') && (!file.files || !file.files[0])) {
                alert('Foto fisik KWh meter public area wajib dilampirkan.');
                return;
            }
        }

        // Ambil data form
        const formData = new FormData(form);
        const fields = {};
        let photoBlob = null;
        let photoName = '';
        let photoType = '';

        for (const [key, val] of formData.entries()) {
            if (key === '_token') continue;
            if (val instanceof File) {
                if (val.size > 0) {
                    photoBlob = val;
                    photoName = val.name;
                    photoType = val.type;
                }
            } else {
                fields[key] = val;
            }
        }

        const todayStr = new Date().toISOString().split('T')[0];
        const recordId = `${type}_${fields.meter_key || ''}_${todayStr}_${Date.now()}`;

        let fileField = 'foto';
        if (type === 'listrik') fileField = 'foto_kwh';
        else if (type === 'air') fileField = 'foto_air';
        else if (form.dataset.fileField) fileField = form.dataset.fileField;

        const offlineItem = {
            id: recordId,
            type: type,
            fileField: fileField,
            meterName: form.dataset.meterLabel || '',
            url: form.action,
            createdAt: new Date().toISOString(),
            tanggal: todayStr,
            fields: fields,
            photoBlob: photoBlob,
            photoName: photoName,
            photoType: photoType,
            status: 'pending'
        };

        try {
            await savePendingLogsheet(offlineItem);
            const customTitle = form.dataset.meterLabel || '';
            const redirectUrl = form.dataset.targetRedirect || (type.startsWith('public_area') ? '/logsheet-public-area' : '/logsheet/hub');
            showOfflineSavedModal(type, redirectUrl, customTitle);
        } catch (err) {
            console.error('Gagal menyimpan ke IndexedDB:', err);
            alert('Terjadi kesalahan saat menyimpan data di HP Anda: ' + err.message);
        }
    }

    // Pre-cache halaman form logsheet di background saat online di hub
    function prefetchLogsheetForms() {
        if (!navigator.onLine) return;
        const path = window.location.pathname;
        if (!path.includes('/logsheet/hub') && !path.includes('/logsheet-public-area')) return;

        const urls = [
            '/logsheet/input/listrik',
            '/logsheet/input/air',
            '/logsheet-public-area/water',
            '/logsheet-public-area/lvsb',
            '/logsheet-public-area/lvsb/input',
            '/logsheet-public-area/electricity',
            '/logsheet-public-area/water/input/cooling_tower',
            '/logsheet-public-area/water/input/toilet_sky_lobby',
            '/logsheet-public-area/water/input/pantry_office',
            '/logsheet-public-area/electricity/input'
        ];
        urls.forEach(url => {
            fetch(url, { credentials: 'same-origin', cache: 'force-cache' })
                .catch(() => {});
        });
    }

    // Inisialisasi saat DOM siap
    document.addEventListener('DOMContentLoaded', async () => {
        setupFormInterception();

        // Cek antrean tersimpan saat halaman dibuka
        const pending = await getPendingLogsheets();
        updateOfflineUI(pending);

        // Prefetch halaman input agar tersimpan di cache service worker
        prefetchLogsheetForms();

        // Event listener saat HP mendapatkan sinyal kembali
        window.addEventListener('online', () => {
            showToast('Sinyal terdeteksi! Mengirim data tersimpan ke server...', 'info');
            syncPendingLogsheets();
        });

        // Event listener saat HP kehilangan sinyal
        window.addEventListener('offline', async () => {
            showToast('⚡ Koneksi internet terputus (Mode Offline). Data akan disimpan di HP.', 'warn');
            const items = await getPendingLogsheets();
            updateOfflineUI(items);
        });

        // Coba sinkronisasi jika online dan ada antrean
        if (navigator.onLine && pending.length > 0) {
            syncPendingLogsheets();
        }
    });

    // Ekspos method ke global window untuk debugging & testing manual
    window.OfflineLogsheet = {
        getPending: getPendingLogsheets,
        sync: syncPendingLogsheets,
        delete: deletePendingLogsheet,
        save: savePendingLogsheet
    };
})();
