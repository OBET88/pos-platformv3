# Catatan Lanjutan Proyek — POS Platform, Dua Hosting, dan QRIS

> **Instruksi untuk sesi AI berikutnya:** Bos Robert dapat berkata: **"Baca `docs/PROJECT_CONTINUATION_NOTE.md` lalu lanjutkan proyek dari titik terakhir."** Baca file ini terlebih dahulu, kemudian baca `AGENTS.md` pada repository aktif sebelum menyarankan atau mengubah code. Jangan upload, push, menjalankan migration production, mengubah DNS, atau membuat Cron tanpa persetujuan eksplisit Bos Robert.

**Terakhir diperbarui:** 23 Oktober 2026  
**Status proyek:** Planning / deployment ditunda menunggu fitur QRIS compliance dan requirement Bos Robert berikutnya.

## 1. Ringkasan keputusan penting

1. Ada dua hosting production yang **terpisah total**: DapurHosting dan IDwebhost.
2. Bos Robert memilih URL pendaftaran calon tenant secara **manual**. Tidak ada routing/redirect registration otomatis antar-host.
3. DapurHosting tetap menjalankan dKriuk existing dan akan di-upgrade ke code aplikasi terbaru dengan hati-hati.
4. IDwebhost akan menjadi install fresh untuk produk Resto/Cafe SaaS.
5. Tenant IDwebhost Resto ditargetkan memakai hostname pendek root wildcard, contoh `ayamku.nuhafrandesa.com`, bukan `ayamku.resto.nuhafrandesa.com`.
6. Platform minimarket belum dibuat. Saat minimarket nanti juga membutuhkan root-short-domain tenant, perlu domain router pusat yang belum dikerjakan.
7. Deployment/upload ditunda sampai fitur yang diingat Bos Robert dimasukkan, diuji, dan direview.
8. QRIS SaaS tidak boleh memakai satu NMID/credential NUHA POS untuk seluruh tenant/outlet.

## 2. URL dan hosting yang sudah dikonfirmasi

### DapurHosting

| Fungsi | URL |
|---|---|
| Platform Resto | `https://rwp.jajajuju.my.id/` |
| Login admin platform | `https://rwp.jajajuju.my.id/platform-login` |
| Tenant dKriuk existing | `https://dkriuk.jajajuju.my.id/login` |

Jika Bos Robert memberi calon pelanggan URL `https://rwp.jajajuju.my.id/`, registration, payment, onboarding job, database tenant, POSLive, DOKU webhook, Cron, dan directory bootstrap semuanya berada di **DapurHosting**.

### IDwebhost (target fresh)

| Fungsi | Target URL |
|---|---|
| Landing page induk | `https://nuhafrandesa.com` |
| Platform Resto/Cafe | `https://resto.nuhafrandesa.com` |
| Login admin platform Resto | `https://resto.nuhafrandesa.com/platform-login` |
| Tenant Resto/Cafe | `https://<tenant>.nuhafrandesa.com/login` |
| Platform minimarket masa depan | `https://minimarket.nuhafrandesa.com` |

Contoh target tenant: `https://ayamku.nuhafrandesa.com/login`.

Jika Bos Robert memberi calon pelanggan URL `https://resto.nuhafrandesa.com/`, semua data tenant berada di **IDwebhost**.

## 3. Batas antar-host (jangan dilanggar)

- Platform IDwebhost tidak boleh bootstrap POSLive DapurHosting.
- Platform DapurHosting tidak boleh bootstrap POSLive IDwebhost.
- Tidak boleh share database platform, database tenant, `.env`, directory input bootstrap, Cron, DOKU webhook, NMID, atau credential gateway antar-host.
- Tenant yang telah memulai registration/payment pada satu host tidak boleh dipindahkan ke host lain tanpa proyek migrasi khusus.
- DapurHosting dan IDwebhost dapat menjalankan versi code yang sama, tetapi environment/data harus tetap terpisah.

## 4. QRIS / DOKU / PTEN — status compliance

### Fakta yang dikonfirmasi dari pesan PTEN/DOKU

1. Satu NMID digunakan untuk satu outlet merchant.
2. QRIS/NMID tidak boleh dibagikan untuk sub-merchant yang bekerja sama dengan PJP acquirer/aggregator/POS.
3. Outlet harus didaftarkan sesuai MCC dan memakai identitas pemilik langsung, bukan identitas NUHA POS/aggregator.
4. Outlet yang sudah mendaftarkan QRIS pada payment gateway/PJP lain tidak dapat mengajukan QRIS ulang di gateway lain.
5. NUHA POS tidak perlu/mestinya diajukan sebagai merchant QRIS tunggal; outlet pengguna NUHA POS yang harus diajukan.

### Dokumentasi DOKU yang telah dibaca

URL: `https://docs.doku.com/wallet-as-a-service/sub-account`

Kesimpulan hasil baca dan query dokumentasi:

- DOKU Sub-Account adalah balance container/routing/payout untuk platform ecosystem, **bukan NMID**.
- Sub-Account sendiri tidak membuat atau menggantikan NMID outlet.
- Untuk QRIS Checkout yang dirutekan ke Sub-Account, identitas QRIS yang terlihat pembayar tetap Brand Short Name/NMID merchant account yang terdaftar, bukan nama/Profile ID Sub-Account.
- Karena itu Sub-Account saja belum membuktikan compliance requirement NMID mandiri per outlet.

### Usulan bisnis Bos Robert yang disetujui sebagai target desain

NUHA POS mendaftar ke DOKU sebagai **Platform Partner / ISV Partner**. Dalam NUHA POS, tenant mengisi menu:

```text
Pengaturan Pembayaran → KYC QRIS
```

Data outlet dikirim ke API onboarding DOKU. Setelah review/approval DOKU/PTEN, setiap outlet memperoleh/ditautkan dengan NMID, Merchant/Sub-Merchant ID bila berlaku, Terminal ID, dan konfigurasi/credential QRIS yang diperlukan. QRIS dynamic kemudian dibuat memakai profil outlet yang aktif.

### Peringatan penting

Istilah/produk API **Platform Partner/ISV/Sub-Merchant Onboarding** belum dikonfirmasi oleh DOKU untuk use case ini. Jangan mengimplementasikan API vendor berdasarkan asumsi, dan jangan mengaktifkan QRIS SaaS production sebelum jawaban tertulis + dokumentasi API diterima.

### Pertanyaan yang menunggu jawaban DOKU/PTEN

1. Apakah DOKU memiliki program resmi Platform Partner/ISV/POS SaaS untuk mendaftarkan merchant outlet individual?
2. Apakah API onboarding menerbitkan atau menautkan NMID per outlet fisik atas identitas pemilik langsung?
3. Dokumen/data KYC/KYB apa yang dibutuhkan per outlet?
4. Apakah tenant multi-cabang harus onboarding/NMID/terminal per cabang?
5. Setelah approval, credential apa yang diterbitkan dan scope-nya apa: merchant, outlet, atau terminal?
6. Apakah DOKU SNAP Direct QRIS bisa memakai profil outlet approved tersebut?
7. Saat scan, apakah nama merchant dan NMID yang tampil benar milik outlet tenant?
8. Apakah settlement langsung ke rekening outlet atau melalui saldo/sub-account NUHA?
9. Bagaimana penanganan outlet yang sudah punya QRIS/NMID melalui PJP lain?
10. Apakah tersedia sandbox, API submit, callback/webhook status, revision/rejection, dan API inquiry onboarding?
11. Apakah NUHA boleh menyimpan credential per outlet; apa standar keamanan/rotasi yang diwajibkan?
12. Minta dokumentasi API, diagram flow, SLA KYC, pricing/fee, kontrak, dan daftar field resmi.

## 5. Fitur yang perlu dibuat setelah DOKU menjawab

Nama working feature:

```text
QRIS Merchant Onboarding per Outlet
```

### Data model target

```text
Tenant
└── Business
    └── Branch / Outlet
        └── QRIS Merchant Profile
            ├── status onboarding
            ├── legal owner / business identity
            ├── documents KYC/KYB
            ├── outlet name/address/postal code
            ├── MCC
            ├── settlement bank account
            ├── NMID
            ├── DOKU merchant/sub-merchant ID (jika berlaku)
            ├── terminal ID
            ├── encrypted QRIS credentials
            ├── provider references
            ├── approval/rejection metadata
            └── audit trail
```

Status target:

```text
draft → submitted → under_review → needs_revision → approved → active
                                           └──────→ rejected
active → suspended / expired
```

### Server rules wajib

- QRIS dynamic hanya boleh dibuat untuk branch/outlet dengan profile `active`.
- Kasir branch A hanya dapat menggunakan profile QRIS branch A.
- NMID, merchant ID, terminal ID tidak boleh dipakai ulang lintas outlet tanpa persetujuan skema provider tertulis.
- Credential dienkripsi; tidak boleh masuk CLI arg, log, audit payload, atau response UI.
- QRIS feature entitlement `qris_dynamic` tetap diperlukan, tetapi tidak cukup sendiri untuk mengaktifkan QRIS.
- Approval internal tidak boleh memalsukan approval provider.
- Outlet yang sudah menggunakan PJP lain harus diarahkan sesuai jawaban DOKU, bukan diajukan ulang secara otomatis.

## 6. Kondisi code/repository saat terakhir

### pos-platform

Path local: `/Users/nuha/Sites/resto/pos-platform`

Commit hardening yang sudah dibuat lokal, **belum dipush**:

```text
981fd18 feat(platform): harden tenant onboarding
```

Isi utama commit:

- DOKU platform payment signature, replay, provider-reference correlation hardening.
- Paid plan menunggu pembayaran sebelum provisioning.
- Durable DB onboarding job/worker dengan lease, retry/backoff, Cron scheduler.
- Bootstrap owner secret encrypted + one-time input file agar secret tidak muncul di CLI/log.
- Public onboarding access token.
- Legacy onboarding backfill.
- Admin recovery menyelesaikan durable job.

Verifikasi terakhir:

```text
php artisan test
163 passed, 3 skipped, 848 assertions

POSLive TenantBootstrapCommandTest
1 passed, 7 assertions
```

Dokumen deployment yang dibuat lokal tetapi **belum di-commit**:

```text
docs/HOSTING_ENVIRONMENT_BOUNDARIES.md
docs/IDWEBHOST_ONBOARDING_RELEASE_RUNBOOK.md
docs/HOSTING_DEPLOYMENT_CHECKLISTS.md
docs/TWO_HOST_ROLLOUT_ROADMAP.md
docs/DEPLOYMENT_PREFLIGHT_WORKSHEET.md
docs/PROJECT_CONTINUATION_NOTE.md
```

### poslive

Path local: `/Users/nuha/Sites/resto/poslive`

Worktree sangat dirty oleh perubahan POS/UAT/preorder/QRIS yang sudah ada. Jangan reset, format global, atau commit seluruh worktree.

Perubahan onboarding scope yang pernah dibuat:

- `config/tenancy.php`: secure shared bootstrap input directory.
- `routes/console.php`: `tenant:bootstrap-database --bootstrap-input`; legacy `platform:provision-tenant` handoff JSON secure.
- `docs/updatev1.md`: listing file terkait.

Catatan project POSLive dari `AGENTS.md`:

- hosting tidak memiliki terminal;
- schema tenant harus punya migration idempoten + browser trigger melalui `public/update-v1.php` dan `UpdateV1Runner`;
- semua perubahan shippable POSLive harus ditambahkan ke daftar `docs/updatev1.md` (kecuali `.env`, `tests/`, docs itu sendiri);
- jangan overwrite hosting `.env`, `vendor/`, atau running `index.php`;
- jangan rerun `RolePermissionSeeder` pada tenant existing;
- POSLive adalah restaurant POS, bukan minimarket project.

## 7. Domain root wildcard IDwebhost — requirement sebelum go-live

Target IDwebhost Resto:

```env
APP_URL=https://resto.nuhafrandesa.com
PLATFORM_TENANT_BASE_DOMAIN=nuhafrandesa.com
TENANCY_CENTRAL_HOSTS=nuhafrandesa.com,www.nuhafrandesa.com,resto.nuhafrandesa.com,minimarket.nuhafrandesa.com
```

DNS target:

```text
nuhafrandesa.com                 → landing page
resto.nuhafrandesa.com           → pos-platform/public
*.nuhafrandesa.com               → poslive/public (fase Resto)
minimarket.nuhafrandesa.com      → reserved/exact host future minimarket
```

Sebelum membuka public registration IDwebhost, implementasikan tenant-code/domain reserved-name validation minimal untuk:

```text
www, resto, minimarket, admin, api, app, mail, ftp,
cpanel, webmail, support, status
```

Saat ini `TenantDomainSuggestionService` membentuk hostname dari `tenant_code + tenant_base_domain`; belum terlihat reserved domain guard khusus. Implementasi dan test harus ditambahkan nanti.

## 8. Deployment state / hard stop

Sampai requirement QRIS dan fitur tambahan selesai:

- **Jangan upload** ke DapurHosting atau IDwebhost.
- **Jangan menjalankan** `update-v1.php` dKriuk.
- **Jangan menjalankan** platform/POSLive production migration.
- **Jangan membuat/menyalakan** Cron onboarding production.
- **Jangan mengubah** DNS wildcard/SSL production.
- **Jangan push** commit tanpa persetujuan eksplisit Bos Robert.

## 9. Dokumen pendukung yang wajib dibaca bila melanjutkan

1. `docs/PROJECT_CONTINUATION_NOTE.md` — file ini.
2. `docs/TWO_HOST_ROLLOUT_ROADMAP.md`.
3. `docs/DEPLOYMENT_PREFLIGHT_WORKSHEET.md`.
4. `docs/HOSTING_ENVIRONMENT_BOUNDARIES.md`.
5. `docs/IDWEBHOST_ONBOARDING_RELEASE_RUNBOOK.md`.
6. `docs/HOSTING_DEPLOYMENT_CHECKLISTS.md`.
7. `/Users/nuha/Sites/resto/poslive/AGENTS.md` sebelum mengubah POSLive.

## 10. Langkah pertama saat percakapan dibuka lagi

1. Baca file ini dan laporkan ringkasan status kepada Bos Robert.
2. Tanyakan apakah jawaban/documentation DOKU/PTEN sudah diterima.
3. Jika sudah ada jawaban DOKU, lakukan business/technical analysis terlebih dahulu dan jangan coding sampai scope/acceptance criteria disetujui.
4. Jika feature lain yang terlupa sudah dijelaskan, capture requirement tersebut ke plan sebelum menyentuh deployment.
5. Setelah QRIS/feature selesai: jalankan test, QA/security review, update release artifact list, baru minta persetujuan deployment.
