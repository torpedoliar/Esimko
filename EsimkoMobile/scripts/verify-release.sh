#!/usr/bin/env bash
# Run after signing AND on a fresh production download. Fail before publishing on mismatch.
set -euo pipefail
apk=${1:?APK path required}
version=${2:?Expected versionName required}
code=${3:?Expected versionCode required}
build_tools=${ANDROID_BUILD_TOOLS:?Set ANDROID_BUILD_TOOLS to SDK build-tools directory}
java=${JAVA_HOME:?Set JAVA_HOME}/bin/java
[[ -x "$java" ]] || java="$java.exe"
aapt="$build_tools/aapt"
[[ -x "$aapt" ]] || aapt="$aapt.exe"
badging=$("$aapt" dump badging "$apk")
package=$(printf '%s\n' "$badging" | grep '^package:')
[[ "$package" == *"name='com.esimko.mobile'"* ]]
[[ "$package" == *"versionName='$version'"* ]]
[[ "$package" == *"versionCode='$code'"* ]]
cert=$("$java" -jar "$build_tools/lib/apksigner.jar" verify --print-certs "$apk")
[[ "$cert" == *"Signer #1 certificate SHA-256 digest: 25df28c036a548e58ee52fb99a2a1d1289fbe665025ad6c453e74aa6fe3420bc"* ]]
printf '%s\n' "$package"
printf '%s\n' "$cert"
sha256sum "$apk"
