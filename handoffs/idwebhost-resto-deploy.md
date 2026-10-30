# Deploy IDwebhost — Resto (fresh) + poslive
Status: SIAP EKSEKUSI · Service: pos-platform + poslive · Diperbarui: 2026-10-31 21:45

## Sedang dikerjakan
Prepare deploy kedua aplikasi (pos-platform control plane + poslive tenant POS) ke IDwebhost fresh, tanpa tenant. Bos Robert akan upload & jalankan di cPanel sesuai `docs/IDWEBHOST_DEPLOY_SEQUENCE.md`. Pekerjaan saya = koordinasi, validasi, laporan; upload & cPanel execution = Bos Robert.

## Status terakhir (terbukti)
- **Reserved tenant code validation SELESAI & TERVERIFIKASI** (worksheet §4): `TenantDomainSuggestionService` + `TenantRegistrationService` (12 reserved word final: www, resto, mm, admin, api, app, mail, ftp, cpanel, webmail, support, status) + validasi karakter ilegal subdomain. Test `tests/Feature/PlatformReservedTenantCodeTest.php` 5 passed; full suite 168 passed / 3 skipped / 0 failed. Opsi A (field `tenant_code` opsional, auto dari nama maks 12 char) termasuk scope validasi reserved.
- Build frontend fresh: `poslive` (`app-BpqIVRQL.js`) + `pos-platform` (`app-DYUwHqdt.js`) — `npm run build` sukses, timestamp build 2026-10-31.
- Dokumen deploy lengkap: `docs/IDWEBHOST_DEPLOY_SEQUENCE.md` (Fase 0–7, fresh, terminal ada, DOKU sandbox).
- `DEPLOYMENT_PREFLIGHT_WORKSHEET.md` §1 + §3 sudah di-update sesuai keputusan Bos Robert 2026-10-31 (terminal ada → migration method selesai; fresh → tak ada import dump; DOKU sandbox; saran subdomain resto/mm). Dokumen 3 file (worksheet, deploy sequence, handoff ini) disesuaikan domain final + reserved list final (`mm`) 2026-10-31.

## Keputusan penting (Bos Robert, 2026-10-31)
- IDwebhost PUNYA TERMINAL → `php artisan migrate` langsung, tanpa trigger browser `update-v1.php` untuk fresh install (trigger tetap dipakai utk upgrade skema tenant NANTI, bukan fresh).
- IDwebhost FRESH, tanpa tenant → semua tenant dibuat via pendaftaran `resto.nuhafrandesa.com`; TIDAK ada import dump `jajf8926_*.sql` (itu data DapurHosting).
- DOKU = sandbox dulu (akun produksi ada, dipindah belakangan; credential production `BRN-0276-...` saat ini 4017300 = belum valid production, lihat `handoffs/qris-doku-online-tenant-54.md`).
- Domain FINAL (keputusan 2026-10-31): root=`nuhafrandesa.com` (+ www), resto platform+register=`resto.nuhafrandesa.com` (pos-platform/public), minimarket platform+register=`mm.nuhafrandesa.com` (pos-platform/public, reserved, process bangun), tenant=`*<kode>.nuhafrandesa.com`→poslive/public (wildcard).
- Opsi A FINAL: field `tenant_code` opsional di form register; bila kosong, auto dari nama tenant, dibatasi 12 char. Reserved list final (12 word): www, resto, mm, admin, api, app, mail, ftp, cpanel, webmail, support, status.
- Info ini menggeser release blocker lama "migration platform tanpa terminal" → SELESAI.

## Langkah berikutnya
- [x] Bos Robert: konfirmasi nama subdomain resto + minimarket — **FINAL: `resto.` + `mm.nuhafrandesa.com` (2026-10-31)**
- [x] Bos Robert: konfirmasi path cPanel IDwebhost — **SELESAI 2026-10-31**: home `/home/nuhafran` (pos-platform `/home/nuhafran/pos-platform`, poslive `/home/nuhafran/poslive`), PHP CLI `/usr/local/bin/php` (8.2.35), DB platform `nuhafran_pos_platform`, DB central `nuhafran_poslive_central`, user DB `nuhafran_pos`, IP publik `202.52.146.137`. Nilai sudah ditanam ke `docs/IDWEBHOST_DEPLOY_SEQUENCE.md`.
- [x] Bos Robert: verifikasi `diagnose_doku_qris.php` SUDAH terhapus dari hosting Rumahweb (v3.jajajuju.my.id) — **SELESAI, dikonfirmasi Bos Robert 2026-10-31**. (Rumahweb TIDAK ada terminal; IDwebhost ADA terminal.)
- [ ] Bos Robert: jalankan `docs/IDWEBHOST_DEPLOY_SEQUENCE.md` Fase 0→7 (SIAP EKSEKUSI — semua input sudah lengkap)
- [ ] Setelah Fase 6 (smoke test) sukses: update worksheet (centang), update `HOSTING_DEPLOYMENT_CHECKLISTS.md` B1–B5, tulis entri `CHANGELOG_FIXES.md`, lalu tandai handoff ini ✅ LIVE + hapus
- [ ] Bila upload belum dilakukan (ditunda Bos Robert) → Status: DITUNDA + catat alasan

## Jangan lakukan (jebakan)
- JANGAN import `DB/jajf8926_pos_platform.sql` / `jajf8926_tenant_dkriuk_3.sql` ke IDwebhost (data DapurHosting, salah lingkungan + risiko bocor kredensial lintas host)
- JANGAN upload `.env`, `vendor/` (pakai `composer install --no-dev` di terminal IDwebhost), `tests/`, folder `DB/`, atau `docs/updatev1.md`
- JANGAN jalankan `update-v1.php` untuk fresh install (terminal ada, `php artisan migrate` menggantikan)
- JANGAN publik wildcard `*.nuhafrandesa.com` sebelum reserved code test (Fase 6.2) lolos
- JANGAN campur DB/`.env`/Cron/Cron-job IDwebhost dengan DapurHosting
- JANGAN nyalakan Cron `schedule:run` sebelum migration platform (Fase 3.6) selesai
- Reserved code list (12 word) JANGAN di-overwrite/diedit dari `.env` (const di service, sengaja)
