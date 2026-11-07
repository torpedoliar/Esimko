# eSIMKO Android brand assets

`source.png` is the official combined crest + dark eSIMKO wordmark supplied by the user (904 × 227, transparent RGBA). Source SHA-256: `a48b0daca0b268d5a89afecde34f603ed4d939f01a3ce00b5dc5c3d34bad41e6`.

Run `python scripts/generate-branding.py` to regenerate:
- dark combined logo, unchanged from source;
- white-text combined logo, preserving the original coloured crest and alpha;
- unchanged full-colour stacked crest + wordmark foregrounds, inside the adaptive safe circle;
- legacy/default `ic_launcher.png` and circular `ic_launcher_round.png` at mdpi/hdpi/xhdpi/xxhdpi/xxxhdpi (48/72/96/144/192px), with a white background;
- Android 26 adaptive XML and Android 33 variants with a separate white-alpha `monochrome` layer. The themed layer preserves the complete foreground alpha; it never replaces the colour foreground;
- white-alpha notification icons at 24dp in all five densities (24/36/48/72/96px). The old 96px `drawable-nodpi` small icon is removed to avoid density-independent sizing. No full-colour/adaptive launcher is used as a small icon;
- local preview `app/build/branding-preview.png`.

All Compose logo uses go through `EsimkoLogo`, using actual background luminance. Splash audit: both existing themes inherit native `android:Theme.Material.Light.NoActionBar`; neither overrides `windowSplashScreenAnimatedIcon` or `windowBackground`. Android 12+ therefore uses the launcher icon and native theme background. Older Android keeps the native starting-window defaults. No splash dependency, Activity change, or custom splash asset is needed.

Run `python scripts/verify-branding.py --self-test` for local image/resource checks plus optimized-name parser, density/API selection, and PNG normalization self-tests. Pillow is required. Existing colour foregrounds and combined logos remain unchanged.

After building/signing separately, run `python scripts/verify-branding.py path/to/signed.apk`. APK checks use SDK `aapt2 dump resources` to resolve logical resource names/configurations to packaged paths, including shortened names from `aapt2 optimize`; they compare decoded pixels at every density, verify adaptive layer bindings (including API33 monochrome), and check manifest launcher bindings. Invisible RGB changes from PNG crunching are ignored. Pass `--aapt2 path/to/aapt2` or set `ANDROID_BUILD_TOOLS`; SDK lookup also supports `ANDROID_HOME`, `ANDROID_SDK_ROOT`, and `local.properties`. This reads an APK only: no Gradle, device, install, network, or signing operation. A stale APK without the new resources must fail. Then use `scripts/verify-release.sh` separately to verify embedded APK version and signing certificate; the branding verifier does not authenticate signatures.

Local release prepared: 1.0.26 / code 27. Publication and Git push are separate actions, not performed by these scripts.
