"""Generate Android logo variants from the official combined transparent PNG.
Run from any directory with Python + Pillow. No network or production access.
"""
from pathlib import Path
from PIL import Image, ImageDraw
import hashlib

ROOT = Path(__file__).resolve().parents[1]
RES = ROOT / 'app/src/main/res'
SOURCE = ROOT / 'branding/source.png'
source = Image.open(SOURCE).convert('RGBA')
assert source.size == (904, 227), 'Review source geometry before regenerating'
# The crest remains full colour; only the wordmark/tagline to its right is white.
light = source.copy()
for y in range(light.height):
    for x in range(240, light.width):
        _, _, _, alpha = light.getpixel((x, y))
        light.putpixel((x, y), (255, 255, 255, alpha))
source.save(RES / 'drawable-nodpi/logo_esimko_wordmark.png')
light.save(RES / 'drawable-nodpi/logo_esimko_wordmark_light.png')
crest = source.crop((41, 6, 227, 223))
wordmark = source.crop((240, 20, 855, 200))
foreground = Image.new('RGBA', (432, 432))
crest.thumbnail((124, 144), Image.Resampling.LANCZOS)
foreground.alpha_composite(crest, ((432 - crest.width) // 2, 108))
wordmark.thumbnail((176, 52), Image.Resampling.LANCZOS)
foreground.alpha_composite(wordmark, ((432 - wordmark.width) // 2, 258))
densities = [('mdpi',108,48,24), ('hdpi',162,72,36), ('xhdpi',216,96,48),
             ('xxhdpi',324,144,72), ('xxxhdpi',432,192,96)]
for density, size, legacy_size, _ in densities:
    target = RES / f'mipmap-{density}'
    target.mkdir(parents=True, exist_ok=True)
    colour = foreground.resize((size,size), Image.Resampling.LANCZOS)
    colour.save(target / 'ic_launcher_foreground.png')
    monochrome = Image.new('RGBA', colour.size, 'white')
    monochrome.putalpha(colour.getchannel('A'))
    monochrome.save(target / 'ic_launcher_monochrome.png')
    # Pre-26/default fallback: same complete crest + wordmark, white background.
    legacy = Image.new('RGBA', (legacy_size,legacy_size), 'white')
    legacy.alpha_composite(foreground.resize(legacy.size, Image.Resampling.LANCZOS))
    legacy.save(target / 'ic_launcher.png')
    # Supersampling keeps the circular edge smooth at every density.
    mask = Image.new('L', (legacy_size*4,legacy_size*4))
    ImageDraw.Draw(mask).ellipse((0,0,mask.width-1,mask.height-1), fill=255)
    legacy.putalpha(mask.resize(legacy.size, Image.Resampling.LANCZOS))
    legacy.save(target / 'ic_launcher_round.png')
for version in (26, 33):
    target = RES / f'mipmap-anydpi-v{version}'
    target.mkdir(parents=True, exist_ok=True)
    themed = '    <monochrome android:drawable="@mipmap/ic_launcher_monochrome"/>\n' if version == 33 else ''
    adaptive = ('<?xml version="1.0" encoding="utf-8"?>\n'
                '<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">\n'
                '    <background android:drawable="@color/ic_launcher_background"/>\n'
                '    <foreground android:drawable="@mipmap/ic_launcher_foreground"/>\n'
                + themed + '</adaptive-icon>\n')
    for name in ('ic_launcher', 'ic_launcher_round'):
        (target / f'{name}.xml').write_text(adaptive, encoding='utf-8')
# A monochrome alpha silhouette is required for Android small notification icons.
# Adaptive/colour launchers must not be used as small icons.
small = Image.new('RGBA', (96,96))
crest_icon = source.crop((41,6,227,223))
crest_icon.thumbnail((64,72), Image.Resampling.LANCZOS)
white = Image.new('RGBA', crest_icon.size, 'white')
white.putalpha(crest_icon.getchannel('A'))
small.alpha_composite(white, ((96-white.width)//2,(96-white.height)//2))
for density, _, _, notification_size in densities:
    target = RES / f'drawable-{density}'
    target.mkdir(parents=True, exist_ok=True)
    icon = small.resize((notification_size,notification_size), Image.Resampling.LANCZOS)
    # Resampling transparent RGBA can change invisible RGB; force white again.
    notification = Image.new('RGBA', icon.size, 'white')
    notification.putalpha(icon.getchannel('A'))
    notification.save(target / 'ic_notification_esimko.png')
# A 96px nodpi bitmap renders as 96px on every density, not the required 24dp.
(RES / 'drawable-nodpi/ic_notification_esimko.png').unlink(missing_ok=True)
preview = Image.new('RGB',(1000,540),'#eeeeee')
draw = ImageDraw.Draw(preview)
for top, bg, logo, title in [(0,'#ffffff',source,'Light background / dark text'),(170,'#0D3020',light,'Dark background / white text')]:
    draw.rectangle((0,top,1000,top+169), fill=bg)
    draw.text((20,top+10),title,fill='#777777' if top==0 else '#dddddd')
    logo = logo.copy(); logo.thumbnail((620,125),Image.Resampling.LANCZOS)
    preview.paste(logo,(20,top+35),logo)
for x, round_mask in [(30,False),(250,True)]:
    icon = Image.new('RGBA',(180,180),'white')
    fg = foreground.resize((180,180),Image.Resampling.LANCZOS)
    icon.alpha_composite(fg)
    if round_mask:
        mask=Image.new('L',(180,180)); ImageDraw.Draw(mask).ellipse((0,0,179,179),fill=255);icon.putalpha(mask)
    preview.paste(icon,(x,350),icon)
(ROOT/'app/build').mkdir(parents=True,exist_ok=True)
preview.save(ROOT/'app/build/branding-preview.png')
print('Source SHA256:',hashlib.sha256(SOURCE.read_bytes()).hexdigest())
print('Generated dark/white logos; five densities of colour/themed/legacy/round launchers and 24dp notification icons; API26/API33 adaptive XML; preview')
