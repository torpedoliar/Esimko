# Local camera correction — 1.0.27 / code 28

## Source-supported cause

The loan attachment screen launched external `TakePicture` while declaring CAMERA permission but never obtaining its runtime grant. Android can throw SecurityException in that configuration. The external camera app owns camera access, so this app no longer declares CAMERA; the optional hardware feature stays. No stack trace was obtained from the connected device, so this is a confirmed source defect, not a confirmed diagnosis from a captured crash.

## Changes

- Guard camera file preparation, FileProvider URI creation and intent launch. Show a recoverable error and allow gallery selection instead of crashing.
- Persist pending slot/URI/path through activity recreation.
- Process file input/compression on IO, bound input to 20 MB, reject empty/unreadable output, clean temporary photo on completion/cancel.
- Correct sampled decoding to bound the longest side including panoramas; respect JPEG compression failure.
- Gallery documents follow the same guarded, bounded processing.
- Regression tests cover sampling and input limits; `scripts/verify-camera.py signed.apk` checks source and merged APK permissions/provider mapping.

## Validation limits

JVM tests do not exercise camera OEM apps, Android grants, process recreation or FileProvider intent handling end to end. Record device tests separately; do not claim a complete capture flow based only on a successful build or installed APK. No production account simulation or financial writes are needed for source/unit validation.
