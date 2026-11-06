# Android 1.0.25 — corrective release

## Root cause of failed 1.0.24 upgrade

The file published as `esimko-mobile-v1.0.24.apk` contained manifest versionName `1.0.23`, versionCode `24`. The Gradle version was changed after the build, but that build output was signed without rebuilding. This was independently confirmed on the binary downloaded from production release id 23. Server metadata alone is not sufficient release verification.

## Release requirements

- Set versionName `1.0.25`, versionCode `26` BEFORE building.
- Build unit tests and release; sign with the existing update-compatible certificate.
- Run `EsimkoMobile/scripts/verify-release.sh APK 1.0.25 26` with JAVA_HOME and ANDROID_BUILD_TOOLS set.
- The gate must reject the mislabeled 1.0.24 binary.
- Verify installation over the old signed app on a test emulator, without uninstalling/clearing app data.
- Before publication, snapshot production version.json and all rilis_apk rows. Never modify financial records or run migrations for this release.
- Publish a new release row; set minimum mobile version to 1.0.25 (mandatory). Do not overwrite old APKs.
- Download the public APK again and run the SAME manifest/certificate/hash gate. Verify both the latest download route and the release-pinned URL.

## UI and updater corrections

- Home owns and consumes top/horizontal safeDrawing insets for both the update banner and tab subtree, avoiding overlap and double status-bar spacing.
- Notification center explicitly uses safeDrawing and consumes Scaffold padding; status-bar contrast follows the surface theme.
- Update buttons wrap on narrow screens. Mandatory update explanatory text is scrollable.
- DownloadManager completion is queried, with persisted download id for process recreation, instead of relying on a background completion broadcast.
- SHA-256 hashing runs on IO. Missing/invalid hash, wrong package, wrong embedded version or non-increasing versionCode prevents in-app installation.
- Installer handoff only runs while the owning lifecycle is RESUMED, using the foreground Activity context. The verified file remains available if the system confirmation is cancelled, so retry does not redownload.
- Android still requires the user to grant unknown-source permission and confirm installation. This is not silent installation.
- Profile cache is memory-only, bound to the current credential session, and invalidated synchronously on credential change/logout/401. In-flight results from older sessions are discarded. The old unencrypted profile_cache.json is deleted, not read. A fresh app process still needs one profile request; opening Account within a fresh 60-second session cache avoids the duplicate request. Stale financial snapshots are not displayed as current data.

## Production rollback

Backup: `C:\IIS\Esimko\backup-release-1.0.25-20261107\` (metadata snapshot, not financial DB).

Pause concurrent release publication first. Restore version.json from the backup and remove ONLY the new release row, conditioned on its id AND version 1.0.25 AND code 26; retain APKs for audit. Do not restore the entire rilis_apk snapshot over live counters. Purge the version endpoint cache if necessary. Rollback metadata does not downgrade apps already installed on devices.

## Transition from 1.0.23

The app currently installed executes the old updater. If it downloads but cannot open the installer, use the public download in a browser, then open the downloaded APK and confirm Update. Do not uninstall or clear app data as a workaround. The corrected updater takes effect after installing 1.0.25.
