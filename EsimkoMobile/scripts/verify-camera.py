"""Check production manifest camera intent contract after building/signing."""
import os
from pathlib import Path
import subprocess
import sys
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
ns = '{http://schemas.android.com/apk/res/android}'
manifest = ET.parse(root/'app/src/main/AndroidManifest.xml').getroot()
assert all(p.get(ns+'name') != 'android.permission.CAMERA' for p in manifest.findall('uses-permission'))
provider = manifest.find('application').find('provider')
assert provider.get(ns+'authorities') == '${applicationId}.provider'
assert provider.get(ns+'grantUriPermissions') == 'true'
paths = ET.parse(root/'app/src/main/res/xml/file_paths.xml').getroot()
assert any(p.tag=='cache-path' and p.get('path')=='.' for p in paths)
if len(sys.argv)>1:
    aapt = Path(os.environ['ANDROID_BUILD_TOOLS'])/'aapt.exe'
    permissions = subprocess.check_output([str(aapt),'dump','permissions',sys.argv[1]],text=True)
    assert "android.permission.CAMERA" not in permissions, 'Merged APK unexpectedly requests CAMERA'
    print(permissions)
print('PASS: external-camera permission contract and FileProvider cache mapping')
