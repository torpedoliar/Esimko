# Release 1.0.33 / code 34 — audit and publication

## Published

- Production release ID: **31**.
- Mode: **mandatory** after explicit user confirmation; `min_mobile_version = 1.0.33`.
- Minimum-version change backed up at `C:/IIS/Esimko/backups/force-1.0.33-20261110_083834`. Public endpoint verified: 1.0.32 forceUpdate=true; 1.0.33 forceUpdate=false. APK unchanged.
- Initially published optional with minimum1.0.32; optional-mode checks below describe the initial activation, superseded by this confirmed mandatory gate.
- APK: `esimko-mobile-v1.0.33.apk`, **52,972,043 bytes**.
- SHA-256: `da3b6d1fe1e9109d76076c5a45766a2adf76e3cc550c0aab1ccc14ba13bb3a2c`.
- Certificate SHA-256: `25df28c036a548e58ee52fb99a2a1d1289fbe665025ad6c453e74aa6fe3420bc` (existing update-compatible certificate; no signing-key migration).
- Pinned URL: https://esimko.com/apk/unduh?id=31
- Latest URL: https://esimko.com/apk/unduh
- No Git commit/push was performed as part of this publication.

## Verification rerun before activation

- PHP 7.4 suite: **112 tests / 633 assertions**, passed.
- Disposable MySQL race proofs: parallel device claims; stale logout after rotation; identical savings keys yield one transaction; payload conflict; cancelled replay; both reconciliation race orders — passed.
- Android suite: **269 tests, no failures**; release build and lint passed. Existing nonfatal lint/toolchain warnings remain documented in the audit.
- Independent source audit: no confirmed P1/P2 blockers in scoped session, pending savings, replay DTO, reconciliation and updater flows. This does not replace real-device end-to-end testing.
- Signed APK payload matched all **514** non-META-INF entries of the freshly built release.
- Manifest package/version/code, existing signing certificate, logo resources, camera/FileProvider contract passed.
- Signed release excludes debug `UiReviewActivity` and `SafetySmokeActivity`.
- Production backend target hashes match local and previous backup/restore proof is intact.

## Release backup before metadata change

Private directory: `C:/IIS/Esimko/backups/release-1.0.33-20261110-prepublish`.

- Previous version.json SHA-256: `f2e06b7f1ade826995abef67a38e3c18f8e9d1e13fd8ae300c7382a445898c48`.
- Release-table JSON snapshot: 30 rows, SHA-256 `3e14671a307cf5181452878caa5a4b54d3e831feb263ffc014e8d4b1d8a71fda`.
- Active 1.0.32 APK copied and hash-verified: `f0c369eead25ba8b51e75ecc1886fd9f09fff778f1357f0850cbe7eef19a97a7`.
- ACL restricted; manifest records sizes/hashes. Backup checksums rechecked before publication.
- New APK staged under its new filename without overwriting the old package.
- Existing release/version checked again under DB lock before inserting the row. Version file staged and replaced after package-row commit.

## Postpublication evidence

- `/api/version/check?version=1.0.32`: latest 1.0.33/code34, updateAvailable true, forceUpdate false.
- `/api/version/check?version=1.0.33`: updateAvailable false, forceUpdate false.
- Version response: HTTP200, `Cache-Control: private, no-store`.
- Both pinned and latest download URLs were fetched again. Both hashes match the released candidate.
- Production-downloaded APK manifest is 1.0.33/code34 and signing certificate matches.
- Homepage and web auth/login: HTTP200.
- Publication changes only release metadata/package; no financial test or real-account login/logout/push was performed.

## Limitations and rollout notes

- Legacy requests without Idempotency-Key are not protected by the new ledger. Protection applies after users update; this release does not force 1.0.32 users to upgrade.
- Per-member shared bearer/session semantics remain; login rotates bearer, logout ends the member session and clears associated push devices. Offline logout cannot guarantee server revocation; queued FCM messages cannot be recalled.
- Idempotency covers new savings deposits/withdrawals, not loan exactly-once delivery.
- API36 offline emulator proved actual Android Keystore persistence and MediaStore PDF write/read/view/share-sheet. Android9 path, no-viewer/full-storage cases, real FCM and actual unknown-source installer cancel/retry remain unverified on physical devices.

## Conditional rollback

Pause concurrent release publication first and inspect current metadata. Restore only this release's version.json backup if production still advertises release31/1.0.33/code34. Remove/disable only the row matching all three identifiers if rollback is approved; retain the APK for audit. Never restore the whole release-table snapshot over live counters or restore the full live database. Backend ledger/transactions/tokens must not be rolled back to clean up a release.

Metadata rollback does not downgrade already installed apps. Any corrective mobile release requires a higher versionCode.
