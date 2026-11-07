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
for density, size in [('mdpi',108), ('hdpi',162), ('xhdpi',216), ('xxhdpi',324), ('xxxhdpi',432)]:
    foreground.resize((size,size), Image.Resampling.LANCZOS).save(RES / f'mipmap-{density}/ic_launcher_foreground.png')
# A monochrome alpha silhouette is required for Android small notification icons.
# Adaptive/colour launchers must not be used as small icons.
small = Image.new('RGBA', (96,96))
crest_icon = source.crop((41,6,227,223))
crest_icon.thumbnail((64,72), Image.Resampling.LANCZOS)
white = Image.new('RGBA', crest_icon.size, 'white')
white.putalpha(crest_icon.getchannel('A'))
small.alpha_composite(white, ((96-white.width)//2,(96-white.height)//2))
small.save(RES / 'drawable-nodpi/ic_notification_esimko.png')
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
print('Generated dark/white variants, five launcher densities, notification icon, preview')
