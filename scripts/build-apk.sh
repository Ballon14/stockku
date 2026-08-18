#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

export ANDROID_HOME="${ANDROID_HOME:-/opt/android-sdk}"

echo "==> Membangun APK..."
(cd android && ./gradlew assembleDebug --no-daemon)

echo "==> Menyalin APK ke public/downloads/..."
mkdir -p public/downloads
cp android/app/build/outputs/apk/debug/app-debug.apk public/downloads/stockku.apk

echo "==> Selesai: public/downloads/stockku.apk ($(du -h public/downloads/stockku.apk | cut -f1))"