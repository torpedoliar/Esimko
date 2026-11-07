# eSIMKO Android brand assets

`source.png` is the official combined crest + dark eSIMKO wordmark supplied by the user (904 × 227, transparent RGBA). Source SHA-256: `a48b0daca0b268d5a89afecde34f603ed4d939f01a3ce00b5dc5c3d34bad41e6`.

Run `python scripts/generate-branding.py` to regenerate:
- dark combined logo, unchanged from source;
- white-text combined logo, preserving the original coloured crest and alpha;
- square stacked launcher foregrounds in five densities, contained inside the adaptive safe circle;
- monochrome notification small icon (Android requires an alpha silhouette, not a full-colour/adaptive icon);
- local preview `app/build/branding-preview.png`.

All Compose logo uses go through `EsimkoLogo`, using actual background luminance. The Android 12+ system splash uses the launcher icon; it is not a separate outdated wordmark asset.

Run `python scripts/verify-branding.py [signed.apk]` to validate alpha, crest preservation, safe icon geometry, and the actual PNG pixels packaged inside the APK. Pillow is required. Then use `scripts/verify-release.sh` to verify embedded APK version and signing certificate.

Local release prepared: 1.0.26 / code 27. Publication and Git push are separate actions, not performed by these scripts.
