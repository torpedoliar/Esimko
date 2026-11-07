"""Validate generated branding and optionally assets inside a compiled APK."""
from pathlib import Path
from PIL import Image
from io import BytesIO
import sys
import zipfile
ROOT = Path(__file__).resolve().parents[1]
RES = ROOT / 'app/src/main/res'
dark = Image.open(RES/'drawable-nodpi/logo_esimko_wordmark.png').convert('RGBA')
light = Image.open(RES/'drawable-nodpi/logo_esimko_wordmark_light.png').convert('RGBA')
assert dark.size == light.size == (904,227)
assert dark.getchannel('A').tobytes() == light.getchannel('A').tobytes()
assert dark.crop((0,0,240,227)).tobytes() == light.crop((0,0,240,227)).tobytes(), 'Crest changed'
for x in range(240,904):
    for y in range(227):
        if light.getpixel((x,y))[3]:
            assert light.getpixel((x,y))[:3] == (255,255,255)
for name,size in [('mdpi',108),('hdpi',162),('xhdpi',216),('xxhdpi',324),('xxxhdpi',432)]:
    im = Image.open(RES/f'mipmap-{name}/ic_launcher_foreground.png').convert('RGBA')
    assert im.size == (size,size)
    # Every foreground pixel is within the adaptive-icon safe circle (66/108 diameter).
    for y in range(size):
        for x in range(size):
            if im.getpixel((x,y))[3] > 10:
                assert (x-size/2)**2+(y-size/2)**2 <= (size*33/108)**2, 'Icon clipping risk'
if len(sys.argv)>1:
    with zipfile.ZipFile(sys.argv[1]) as apk:
        for asset in ['logo_esimko_wordmark.png','logo_esimko_wordmark_light.png','ic_notification_esimko.png']:
            # AAPT optimize shortens resource filenames. Compare decoded pixels,
            # not paths, and ignore RGB in fully transparent pixels (PNG crunching).
            source=Image.open(RES/'drawable-nodpi'/asset).convert('RGBA')
            def visible_pixels(im):
                return [(r,g,b,a) if a else (0,0,0,0) for r,g,b,a in im.getdata()]
            found=False
            for name in apk.namelist():
                if not name.endswith('.png'): continue
                packed=Image.open(BytesIO(apk.read(name))).convert('RGBA')
                if packed.size==source.size and visible_pixels(packed)==visible_pixels(source):
                    found=True
                    break
            assert found, 'APK asset mismatch: '+asset
print('PASS: crest preserved, white wordmark, transparency, safe launcher and APK resources')
