# Production change gates — logout/push and savings idempotency

Status: LOCAL IMPLEMENTATION ONLY. No production maintenance, migration, login, financial test, deployment or publication is authorized by this document alone.

## Access blocker recorded 2026-11-10

Read-only production preflight could **not** be completed. The only non-interactive credential reference found locally (PowerShell history for `administrator@49.50.9.81`) was rejected by the server; the host key was verified as `SHA256:/k2LmAEI86lFp7rjzqZ+6DJaIz6SlNaRhuOacecIhTQ`, matching `~/.ssh/known_hosts`. No further password attempts were made to avoid lockout.

Required before the next production step (choose one, separately authorized):
- Provide a current credential through a protected local reference without pasting it into chat or committing it; or
- Run `EsimkoMobile/scripts/production-preflight-readonly.php` from the application root on the server and return its JSON; or
- Provide a reviewed read-only output of equivalent schema/engine/version/hash data.

Do **not** run `update-iis.ps1` as-is for this change: it performs `git pull`, a blanket `artisan migrate --force`, cache rebuilds and storage-link work, which exceeds the approved additive-only scope.

## Mandatory order

1. Compare production code/schema read-only; identify drift.
2. Pass isolated PHP 7.4/MySQL and Android regression/concurrency tests.
3. Review exact file/migration manifest and agree change window/endpoint impact.
4. Back up all impacted production files + consistent database + required private assets.
5. Verify checksums and restore database into an isolated, outbound-disabled test environment.
6. Recheck production drift. STOP if changed until rediff/rebackup.
7. Apply only reviewed additive migration and manifest-listed files within agreed window.
8. Health checks/read-only smoke. Restore write availability only if healthy.
9. Separate checkpoint for signed APK publication/minimum-version policy.

## Preflight (read-only)

- IIS site/root/app pool and PHP/OPcache settings; PHP version/extensions; MySQL version/charset/engine/indexes.
- Actual schema/engine/triggers/events/routines for anggota, transaksi, device_tokens and migration ledger. All atomic-write tables must be InnoDB; STOP on nontransactional engine/unknown trigger side effects. Do not convert production engines automatically.
- Existing device_tokens.token duplicates/index limits. No blanket deduplication or unique index on 600-character token.
- Disk capacity and database size; estimate backup I/O and locks. No secret values in logs/chat/Git.
- SHA-256 for every target file. Do not deploy unrelated files or local .env/vendor changes.

## Backup inventory and proof

Use a timestamped, access-restricted directory outside webroot (public). Prefer a controlled second copy. Never upload production data to a third-party service without authorization.

- Originals for every overwritten controller/support/model/middleware/route/config file. Record files that are new/absent so rollback is precise.
- .env/web.config only in private backup if relevant, never printed or committed.
- Consistent database dump with schema/data and existing triggers/routines/events; strategy selected from actual engines. Single-transaction alone is insufficient for nontransactional tables.
- Focused member/device/migration snapshots as private recovery evidence. Never restore revoked credentials automatically.
- Private attachments only if tests/actions overwrite them; no storage relocation/deletion.
- Before later publication: version.json, release metadata and active APK/certificate/hash.
- Manifest: original path, size, SHA-256, backup path, command exit status. Confirm checksum of copy.
- Restore proof: import into a DIFFERENT isolated DB with scheduler/events/email/FCM/outbound integrations disabled before importing. Do not boot clone with production credentials. Check schema/indexes/row counts and synthetic test behavior. Restrict and later dispose cloned personal data under a separate reviewed cleanup procedure.

STOP if space, dump exit, checksum, inventory or restore validation fails. A backup file merely existing is not restore proof.

## Migration/deploy

- Only the explicitly reviewed new savings-request table migration. No blanket `artisan migrate` of all pending migrations. Review pretend SQL and metadata-lock impact.
- No changes to historical transactions, saldo, payroll, journals, disbursement or public/storage.
- New helper files before controller activation; prevent mixed-version request handling using the agreed write-endpoint gate/atomic deployment method. No ad-hoc all-site outage.
- Lint with production PHP; clear only required Laravel caches. OPcache/app-pool refresh only when needed and scheduled.
- Observe errors, request latency, DB locks/deadlocks and ownership/authorization failures before proceeding.

## Test matrix

### Isolated data, outbound disabled
- Shared bearer semantics: new login rotates token; old logout/register cannot affect newer session. Logout clears member associations, not reassigned token of another account.
- Concurrency: parallel identical idempotency requests produce one transaction; same key/different payload conflict; rollback insert failure; lost response after commit; repeat after app restart/saldo change/cancel; cross-account isolation.
- Android: confirm cancellation sends no write; repeated submit/proof retry; unresolved request recovery; private persistence fails closed; session/account change while request pending.
- Synthetic loan/documents/consents/detail/receipt checks; loan exactly-once guarantee is NOT part of savings idempotency.
- PDF Android 9 and 10+: open/share/save, no viewer, storage full, account switch, cancel.
- Device push only to designated test project/devices. Already queued FCM messages cannot be revoked.
- Signed APK install over existing version without uninstall/clear data: embedded version/code, package, certificate and hash; unknown-source permission, cancel/retry, force-update/offline.

### Production default read-only

Health endpoints, routes/schema/file hashes, cache headers, IIS/Laravel logs and latency. No real member financial write.

Production login/logout/test push requires separately identified test account/device, scope approval, before/after snapshot and cleanup instructions. Financial live test requires additional explicit approval and application-valid reversal reviewed per record; cancelled status alone may not undo side effects. Never delete journals or overwrite the live DB to hide simulation.

## Rollback

- Restore only manifest-listed file originals after checking concurrent drift. Preserve request ledger and real transaction links.
- Do not drop new idempotency table or restore old member/device tokens. Do not restore entire live DB over newer transactions.
- If old code lacks retry protection, keep affected write endpoint gated until forward-fix; availability must not take priority over financial integrity.
- Release metadata rollback does not downgrade installed APK. A mobile corrective release needs a greater versionCode.

## GO evidence

Attach test logs, MySQL concurrency proof, exact deployed hashes, successful backup/restore evidence, smoke matrix results, unresolved limitations and reviewed rollback. Any critical unknown => HOLD publication, regardless of deadline.

## Execution record — 2026-11-10 (backend only)

### Read-only preflight (production)
- PHP 7.4.33, Laravel 7.30.6, MySQL 9.6.0, database `esimko`, 79 tables, free disk ~46.5 GB.
- `anggota`, `transaksi`, `angsuran`, `penjualan`, `device_tokens`, `migrations`, `rilis_apk`: InnoDB. No triggers.
- `device_tokens` has non-unique indexes on `fid_anggota` and `token`; 6 rows, 0 duplicate token groups.
- Migration ledger had 7 rows. Two old local migrations are NOT in the ledger, so a blanket `artisan migrate` must never be used.
- Production targets matched git HEAD after line-ending normalization; no unknown drift.
- Laravel config/route caches absent; OPcache not loaded in CLI; Composer PSR-4 `App\` => `app` present, so new classes autoload without dump-autoload.

### Backup and restore proof
- Private backup: `C:/IIS/Esimko/backups/safety_20261110_003418` (ACL restricted).
- Files: `MobileController.php` sha256 `10b2a703...44a2`, `FcmSender.php` `527a76c0...bbe`, `routes/api.php` `9ff1732c...a560`, `version.json` `f2e06b7f...8c48`, plus `composer.lock`. All copies matched source.
- New files confirmed absent before deployment.
- Primary dump `esimko.sql`: 163,734,659 bytes, sha256 `9271af9e...ff31`, `Dump completed` marker.
- Portable dump `esimko_restore.sql`: 163,734,691 bytes, sha256 `1147b337...89af`, GTID purged off.
- Restore test created `esimko_restore_check_*` on the same MySQL instance, imported the portable dump, then dropped it. 79/79 tables; row counts matched for anggota 1564, transaksi 232442, angsuran 211830, penjualan 99800, device_tokens 6, rilis_apk 30, migrations 7. Schemas matched after normalizing AUTO_INCREMENT and redundant column charset. No temporary database remains.

### Deployment (additive, no maintenance)
- Uploaded `app/Support/MobileSessionGuard.php` (`6dacd48e...8764`), `app/Support/MobileSavingsIdempotency.php` (`615c42f3...5102`), and the ledger migration.
- Applied ONLY: `php artisan migrate --path=database/migrations/2026_11_10_000001_create_mobile_savings_requests_table.php --force`; table is InnoDB with unique `(member_id, request_key)` and nullable `transaction_id`/`state`.
- Uploaded `MobileController.php` (`3df9e33e...c4d`), `FcmSender.php` (`10df023e...8160`), `routes/api.php` (`6ce5a507...d311`); production hashes match local.
- Cleared config/route/view caches. Route `POST api/mobile/transaksi/{jenis}/rekonsiliasi` present.
- Post-deploy read-only checks: version endpoint 200 (1.0.32, no update), no-auth reconcile 401, invalid bearer 401, fake login 404, homepage 200, web `/auth/login` 200. No error log lines after deployment. Live rows unchanged: ledger 0, device_tokens 6, migrations 8.

### Rollback for this deployment
- Restore the five files from the private backup, then clear config/route/view caches.
- Keep `mobile_savings_requests` and its rows; do not drop it. Do not restore old tokens or the whole DB.
- If reverting to code without idempotency, hold the mobile write endpoints until a forward fix is available.
- APK publication is still pending and requires a new versionCode; restoring release metadata cannot downgrade installed apps.

### Remaining before publish/go-live
- Release candidate built: **1.0.33 / code 34**, signed with the existing update certificate.
  - APK: `EsimkoMobile/app/build/outputs/apk/release/esimko-mobile-v1.0.33.apk`
  - SHA-256: `da3b6d1fe1e9109d76076c5a45766a2adf76e3cc550c0aab1ccc14ba13bb3a2c`
  - Manifest/version/certificate, branding and camera contracts verified.
  - Installed over the existing app on the emulator without uninstall/clear data; versionName 1.0.33 / code 34; cold launch without crash.
- Publishing, mandatory/optional mode, and `rilis_apk`/`version.json` update are still pending explicit approval. The RC was NOT uploaded.
- Device smoke: Android 9 and 10+, PDF open/share, push after login and after logout, installer cancel/retry.
- Real FCM/login test needs a designated test account/device and separate approval.
- Rotate the SSH credential that was shared in chat after this work.
- Commit/push of local changes has not been done; it requires separate approval.
