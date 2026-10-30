# Worksheet Preflight Deployment — Jangan Upload Dulu

Status dokumen: persiapan. Tidak ada upload, perubahan DNS, migration, Cron, atau push yang dilakukan berdasarkan dokumen ini.

Deployment ditunda sampai Bos Robert menyampaikan fitur tambahan yang ingin dimasukkan ke release.

## 1. Release boundary saat ini

Release berikutnya harus mencakup, setelah fitur tambahan selesai dan direview:

1. perubahan aplikasi POSLive existing yang sudah ada di worktree;
2. `pos-platform` hardening onboarding commit `981fd18`;
3. dokumentasi dan setup dua hosting;
4. validasi domain pendek tenant Resto pada IDwebhost;
5. fitur tambahan dari Bos Robert.

Tidak ada file yang boleh di-upload sebagian sebelum scope release final disetujui.

## 2. Worksheet DapurHosting — Existing upgrade

| Item | Nilai/Status | Diisi sebelum deployment |
|---|---|---|
| Platform URL | `https://rwp.jajajuju.my.id/` | Dikonfirmasi |
| Platform admin URL | `https://rwp.jajajuju.my.id/platform-login` | Dikonfirmasi |
| Tenant dKriuk URL | `https://dkriuk.jajajuju.my.id/login` | Dikonfirmasi |
| Absolute app path pos-platform | Belum diketahui | Perlu dari File Manager/cPanel |
| Absolute app path POSLive | Belum diketahui | Perlu dari File Manager/cPanel |
| PHP CLI path | Belum diketahui | Perlu dari hosting/cPanel |
| Platform DB backup | Belum dibuat untuk release final | Wajib sebelum upload |
| dKriuk DB backup | Belum dibuat untuk release final | Wajib sebelum upload |
| Platform migration method | Belum diputuskan | Release blocker onboarding platform |
| POSLive browser update key/path | Ada di procedure existing | Verifikasi sebelum release final |
| Cron availability | Belum diverifikasi | Perlu cPanel |
| Shared input directory path | Belum ditentukan | Harus lokal DapurHosting, luar web root |
| Wildcard/domain tenant policy | Existing dKriuk dipertahankan | Tidak ada perubahan domain dKriuk |

### Nilai environment target DapurHosting

```env
PLATFORM_TENANT_APP_PATH=<PATH_POSLIVE_DAPURHOSTING>
PLATFORM_TENANT_APP_PHP_BINARY=<PHP_CLI_DAPURHOSTING>
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_DAPURHOSTING>/.tenant-bootstrap-input

TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_DAPURHOSTING>/.tenant-bootstrap-input
```

Folder input harus `0700`, berada di luar web root, dan berbeda total dari folder IDwebhost.

## 3. Worksheet IDwebhost — Fresh Resto deployment

| Item | Nilai/Status | Diisi sebelum deployment |
|---|---|---|
| Root landing | `https://nuhafrandesa.com` | Target disepakati |
| Platform Resto | `https://resto.nuhafrandesa.com` | Target disepakati |
| Platform Resto admin | `https://resto.nuhafrandesa.com/platform-login` | Target disepakati |
| Tenant URL pattern | `https://<tenant>.nuhafrandesa.com/login` | Target disepakati |
| Platform Minimarket future | `https://minimarket.nuhafrandesa.com` | Reserved |
| Absolute app path pos-platform | Belum diketahui | Perlu dari File Manager/cPanel |
| Absolute app path POSLive | Belum diketahui | Perlu dari File Manager/cPanel |
| PHP CLI path | Indikasi `/usr/local/bin/php` | Harus diverifikasi terhadap `artisan` |
| Platform DB | Fresh / belum ada tenant | Perlu nama DB dan user khusus |
| POSLive central DB | Fresh / belum dikonfirmasi | Perlu nama DB dan user khusus |
| Migration platform method | Belum diputuskan | Release blocker |
| Migration POSLive central method | Belum diputuskan | Release blocker |
| Cron availability | Indikasi tersedia | Verifikasi dari cPanel |
| Shared input directory | Belum dibuat | Wajib `0700`, luar web root |
| DOKU environment | Belum dikonfigurasi | Endpoint/credential khusus IDwebhost |

### DNS/web-root target IDwebhost

| Hostname | Target | Status sebelum go-live |
|---|---|---|
| `nuhafrandesa.com` | Landing page induk | Perlu dibuat |
| `www.nuhafrandesa.com` | Redirect/alias landing | Perlu diputuskan |
| `resto.nuhafrandesa.com` | `pos-platform/public` | Perlu dibuat sebagai subdomain eksplisit |
| `*.nuhafrandesa.com` | `poslive/public` | Perlu diuji sebagai wildcard subdomain |
| `minimarket.nuhafrandesa.com` | Placeholder/reserved, bukan POSLive Resto | Perlu dibuat eksplisit atau di-reserve sebelum wildcard aktif |

Catatan penting: cPanel/IDwebhost harus membuktikan bahwa host eksplisit `resto` dan `minimarket` mengalahkan wildcard `*.nuhafrandesa.com`. Jangan asumsi perilaku ini tanpa uji browser HTTPS pada host test.

### Nilai environment target IDwebhost Resto

```env
# pos-platform
APP_URL=https://resto.nuhafrandesa.com
PLATFORM_TENANT_BASE_DOMAIN=nuhafrandesa.com
PLATFORM_TENANT_APP_PATH=<PATH_POSLIVE_IDWEBHOST>
PLATFORM_TENANT_APP_PHP_BINARY=<PHP_CLI_IDWEBHOST>
PLATFORM_TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_IDWEBHOST>/.tenant-bootstrap-input

# poslive
TENANCY_CENTRAL_HOSTS=nuhafrandesa.com,www.nuhafrandesa.com,resto.nuhafrandesa.com,minimarket.nuhafrandesa.com
TENANT_BOOTSTRAP_INPUT_DIRECTORY=<HOME_IDWEBHOST>/.tenant-bootstrap-input
```

## 4. Requirement yang belum diimplementasikan

Sebelum public registration IDwebhost dibuka dengan root wildcard, implementasikan dan test validasi reserved tenant code/domain. Minimal nama berikut harus ditolak:

```text
www, resto, minimarket, admin, api, app, mail, ftp,
cpanel, webmail, support, status
```

Tenant registration saat ini membuat domain dari tenant code melalui `TenantDomainSuggestionService`. Karena itu validasi harus dipasang sebelum hostname root wildcard dipublikasikan.

## 5. Fitur tambahan Bos Robert

| Fitur yang terlupa | Scope / acceptance criteria | Status |
|---|---|---|
| Belum disampaikan | Menunggu arahan Bos Robert | Blocked by requirement |

Setelah requirement diterima, fitur harus melalui: scope → implementasi → test → security/QA review → update checklist file replace → final release review.

## 6. Larangan sampai release final disetujui

- Jangan upload code baru ke DapurHosting atau IDwebhost.
- Jangan menjalankan `update-v1.php` di dKriuk.
- Jangan menjalankan migration platform/POSLive.
- Jangan membuat/menyalakan Cron onboarding production.
- Jangan mengubah DNS/wildcard/SSL production.
- Jangan push commit tanpa persetujuan eksplisit Bos Robert.
