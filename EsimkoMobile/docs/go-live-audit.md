# Mobile go-live audit — release 1.0.33

## Current decision and status

**Published as an optional update: 1.0.33 / code34 / release31.** Minimum version remains 1.0.32. See `release-1.0.33.md` for the final fresh test run, release backup, downloaded-package hash/certificate proof and remaining device-test limits. Publication is not a claim that every production workflow was exercised.

Update 2026-11-10: backend session guard, idempotency ledger, reconciliation route and generic push are deployed; private backup and restore proof are recorded in `production-change-gates.md`. The signed APK was verified against a fresh build and downloaded from production again after publication.

No Git commit/push, real financial test submission, or real-account test login/logout/push was performed. Sections below include historical audit findings and their follow-up status.

## Verified

- Full Gradle testDebugUnitTest: **269 tests, 0 failures, 0 errors** (latest run).
- assembleDebug and assembleRelease: successful.
- lintDebug: **0 errors, 21 warnings**. Warnings include old dependency/target versions, unused resources/icon heuristics, and incompatible third-party custom lint checks (some Compose-specific checks are skipped). No dependency/toolchain migration was attempted immediately before go-live.
- Branding source/resource verification: passed.
- Camera/FileProvider contract verification against release APK: passed; no direct CAMERA permission.
- Release merged manifests exclude debug UiReviewActivity.
- git diff --check: passed.
- Synthetic emulator UI checks: account/activity light/dark, narrow 320dp with 1.3x text and tablet 800dp. Device settings restored. Synthetic debug fixture has no repository calls; later tablet audit performed with emulator Wi-Fi/mobile data disabled.

## Fixes included

1. Activity API module filters corrected to simpanan/pinjaman; transaction detail dispatch normalized to transaksi.
2. Activity query snapshots/cancellation/generation guards prevent old requests replacing current filters; overlapping pagination deduplicated and stable keys corrected.
3. Savings submission/upload flags set synchronously to prevent rapid double taps; successful request ID cannot be submitted again; upload retry reuses original ID. Withdrawal description corrected. Loan submission guarded synchronously as well.
4. Profile/proof images use bounded reads and off-main processing with readable errors; duplicate avatar submissions suppressed.
5. Auth credentials saved atomically; stale login/401/logout callbacks cannot invalidate a newer session. Profile logout delegates to authenticated logout; local clear precedes navigation and captured-bearer revocation survives screen cancellation.
6. FCM registration bound to session, serialized and marked registered only after confirmed success; failures remain retryable on resume/login; credential logging removed.
7. Receipt/lampiran cancellation and session checks strengthened, stale detail/image cleared, superseded requests guarded; storage copy checks cancellation and session per chunk. Android 9/10 API branch made lint-verifiable.
8. Updater detects higher build codes, validates HTTPS, keys downloads by release identity, and revalidates APK before installer invocation; installer URI read grant supplied.
9. Account balance privacy survives refresh; chart hidden too. Responsive account/activity layouts and accessible category selection added. Theme follows selected app theme. Backup/device-transfer exclusions explicitly configured.

## Unresolved release gates

### Backend: device push association survives logout

Local MobileController logout clears member bearer token but does not remove device_tokens association. Background notification payloads may still reach a logged-out device until reassignment. Client fixes cannot reliably prevent Android/FCM-rendered notification payloads.

Required separate backend work: cleanup appropriate associations on logout, serialize logout/register with member-row locking and bearer revalidation, and test stale bearer/concurrent registration/logout/account reassignment. Current backend logout is member-wide, so blanket deletion would also affect other devices; decide behavior consistently with member-wide bearer revocation. Backend was inspected read-only, not changed or deployed.

### Financial request timeout ambiguity

Rapid duplicate taps are prevented in the client. The existing savings API has no demonstrated idempotency key contract. A server-accepted request whose response is lost can still be duplicated by a later manual retry/reopening the form. Exactly-once submission requires backend idempotency/reconciliation; do not describe the client guard as exactly-once delivery.

### Controlled integration checks still required

- Login/logout/account switching against a dedicated test account and verify revoked sessions/token association.
- Savings/withdrawal and loan end-to-end against isolated test data, including response timeout and proof upload retry.
- Actual PDF save/open/share on Android 9 and Android 10+, file permissions/storage errors.
- Real FCM delivery to phone/tablet after login and after logout.
- Signed next-version APK: package, versionName/versionCode, certificate, hash; DownloadManager, unknown-sources permission, installer cancellation and forced-update behavior on device.

No test account credentials were requested or fabricated; no member transaction was created for these checks.

## Follow-up: approved safety implementation (local only)

The earlier gate descriptions above record the original audit state. Local remediation now includes member-locked bearer/expiry revalidation for logout/device registration, token cleanup on rotation/logout, generic lock-screen pushes with association recheck, an additive mobile_savings_requests ledger, and encrypted account-bound Android request persistence. Production has NOT been changed or backed up yet.

Latest verification: PHP 7.4 backend **112 tests / 633 assertions pass**; Android **269 tests pass**, debug/release builds and lint pass. Disposable MySQL 8.0 with no network and a fresh temporary datadir proves concurrent device claims, stale logout after a real member lock wait, parallel identical savings keys yielding one transaction, changed-payload conflict, replay after cancellation without recreation, and both reconciliation race orders (tombstone-first blocks a delayed create; create-first returns the committed ID). This is not proof that production engine/schema/triggers match; production preflight remains required.

Deployment remains HOLD until production-target hashes/schema/engine are audited, backups and isolated restore verified, change window agreed, and device/integration smoke matrix completed. See production-change-gates.md and production-target-manifest.json. No commit/push/publish or production financial test was performed.

Safe reset/edit is now implemented locally through explicit online POST reconciliation: under the same member lock, the server returns an existing transaction or records an abandoned-key tombstone. Only the latter authorizes local draft removal; late creates are rejected. MySQL tests prove both lock-wait race orders. This changes the NEW UNDEPLOYED ledger migration (nullable transaction_id plus state); if preflight discovers any ledger already installed, STOP and plan a separate additive migration instead of editing an applied migration. Offline logout cannot revoke server associations until the request reaches the server. Already queued FCM messages cannot be recalled. Idempotency scope excludes loans and legacy requests without a key.

### Offline device evidence (debug fixture, synthetic data)

Emulator (API 36) with Wi-Fi/mobile data disabled, debug-only `SafetySmokeActivity`:
- PendingSavingsStore: create, process-recreation reload through Android Keystore AES-GCM, member/jenis AAD binding rejection, success ID record, deletion — PASS.
- ReceiptFileStore: synthetic PDF written through MediaStore Downloads, read back with `%PDF` magic (10,876 bytes), opened in an installed PDF viewer, share sheet opened with the content URI — PASS.
- Fixture cleanup removed only its own synthetic exports; Downloads folder empty afterward. Emulator display/network settings restored.
- Not device-proven: Android 9 FileProvider publish path, storage-full behavior, absence of a PDF viewer, install-permission/unknown-sources flow, real FCM delivery, signed upgrade over an existing installation. These remain in the gate list.
