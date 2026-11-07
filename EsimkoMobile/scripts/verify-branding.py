"""Validate source branding; optionally check resource bindings in a signed APK.
Pillow required. APK inspection also needs SDK aapt2 (no Gradle/device access).
Signing/version checks remain in verify-release.sh.
"""
from pathlib import Path
from PIL import Image
from io import BytesIO
import argparse
import hashlib
import os
import re
import shutil
import subprocess
import xml.etree.ElementTree as ET
import zipfile

ROOT = Path(__file__).resolve().parents[1]
RES = ROOT / 'app/src/main/res'
ANDROID = '{http://schemas.android.com/apk/res/android}'
DENSITIES = [('mdpi',108,48,24), ('hdpi',162,72,36), ('xhdpi',216,96,48),
             ('xxhdpi',324,144,72), ('xxxhdpi',432,192,96)]


def image(path):
    with Image.open(path) as im:
        return im.convert('RGBA')


def pixels(im):
    # AAPT PNG crunching may alter RGB only in fully transparent pixels.
    data = bytearray(im.tobytes())
    for i in range(0, len(data), 4):
        if not data[i+3]:
            data[i:i+3] = b'\0\0\0'
    return im.size, hashlib.sha256(data).digest()


def white_alpha(im):
    assert im.getchannel('A').getextrema() == (0,255), 'Missing transparent/opaque pixels'
    data = im.tobytes()
    assert all(data[i:i+3] == b'\xff\xff\xff' for i in range(0,len(data),4) if data[i+3]), 'Not white alpha-only artwork'


def safe_circle(im):
    size = im.width
    for y in range(size):
        for x in range(size):
            if im.getpixel((x,y))[3] > 10:
                assert (x-size/2)**2+(y-size/2)**2 <= (size*33/108)**2, 'Icon clipping risk'


def resource_table(text):
    """Resolve logical resource names to paths, including aapt2 optimize short names."""
    resources, ids, current = {}, {}, None
    for line in text.splitlines():
        entry = re.match(r'\s*resource (0x[0-9a-fA-F]+) (\w+/\w+)\s*$', line)
        if entry:
            resource_id, current = entry.groups()
            ids[current] = resource_id.lower()
            resources[current] = []
        packed = re.match(r'\s*\(([^)]*)\) \(file\) (\S+)', line)
        if packed and current:
            resources[current].append(packed.groups())
    return resources, ids


def packed_path(resources, name, density, version=0):
    matches = []
    for config, path in resources.get(name, []):
        parts = config.split('-')
        api = next((int(p[1:]) for p in parts if re.fullmatch(r'v\d+',p)), 0)
        # minSdk/optimization can remove redundant v26 or v4 qualifiers.
        if density in parts and (api == version if version == 33 else api <= max(version,4)):
            matches.append(path)
    assert len(matches) == 1, f'APK missing/ambiguous {name} ({density}, API {version}): {matches}'
    return matches[0]


def find_aapt2(explicit):
    if explicit:
        return str(Path(explicit).resolve())
    executable = 'aapt2.exe' if os.name == 'nt' else 'aapt2'
    tools = os.environ.get('ANDROID_BUILD_TOOLS')
    if tools and (Path(tools)/executable).is_file():
        return str(Path(tools)/executable)
    if shutil.which(executable):
        return shutil.which(executable)
    sdk = os.environ.get('ANDROID_SDK_ROOT') or os.environ.get('ANDROID_HOME')
    properties = ROOT/'local.properties'
    if not sdk and properties.exists():
        sdk = next((line.split('=',1)[1].strip() for line in properties.read_text().splitlines() if line.startswith('sdk.dir=')), None)
    if sdk:
        candidates = sorted((Path(sdk)/'build-tools').glob(f'*/{executable}'),
                            key=lambda p: tuple(int(n) for n in re.findall(r'\d+',p.parent.name)))
        if candidates:
            return str(candidates[-1])
    raise AssertionError('APK inspection requires SDK aapt2: pass --aapt2 or set ANDROID_BUILD_TOOLS')


def verify_apk(apk_path, aapt2, expected):
    def dump(*args):
        return subprocess.check_output([aapt2,'dump',*args,str(apk_path)], text=True, encoding='utf-8')
    resources, ids = resource_table(dump('resources'))
    with zipfile.ZipFile(apk_path) as apk:
        for name, density, path in expected:
            packed = packed_path(resources,name,density)
            assert pixels(image(BytesIO(apk.read(packed)))) == pixels(image(path)), f'APK pixels differ: {name} ({density}) at {packed}'
        for name in ('mipmap/ic_launcher','mipmap/ic_launcher_round'):
            for version in (26,33):
                packed = packed_path(resources,name,'anydpi',version)
                tree = dump('xmltree','--file',packed)
                assert 'E: adaptive-icon' in tree, f'Not adaptive XML: {packed}'
                layers = [('background','color/ic_launcher_background'),('foreground','mipmap/ic_launcher_foreground')]
                if version == 33:
                    layers.append(('monochrome','mipmap/ic_launcher_monochrome'))
                for layer, target in layers:
                    match = re.search(rf'E: {layer}\b([^\n]*\n(?:(?!\s*E:).*(?:\n|$))*)',tree)
                    assert match and re.search(r':drawable[^\n]*@'+ids[target]+r'\b',match.group(0),re.I), f'APK {name} API {version}: wrong {layer} binding'
        manifest = dump('xmltree','--file','AndroidManifest.xml')
        for attribute, target in [('icon','mipmap/ic_launcher'),('roundIcon','mipmap/ic_launcher_round')]:
            assert re.search(r':'+attribute+r'\([^\n]*@'+ids[target]+r'\b',manifest,re.I), f'Wrong manifest {attribute}'
    print('PASS: APK resource table, all raster pixels, adaptive layers/API33 monochrome and manifest bindings (optimized filenames supported)')


def self_test():
    resources, ids = resource_table('''    resource 0x7f0e0000 mipmap/ic_launcher
      (mdpi-v4) (file) res/a.png type=PNG
      (anydpi) (file) res/b.xml type=XML
      (anydpi-v33) (file) res/c.xml type=XML
''')
    assert ids['mipmap/ic_launcher'] == '0x7f0e0000'
    assert packed_path(resources,'mipmap/ic_launcher','mdpi') == 'res/a.png'
    assert packed_path(resources,'mipmap/ic_launcher','anydpi',26) == 'res/b.xml'
    assert packed_path(resources,'mipmap/ic_launcher','anydpi',33) == 'res/c.xml'
    try:
        packed_path(resources,'mipmap/ic_launcher','hdpi')
    except AssertionError:
        pass
    else:
        raise AssertionError('Missing-density detection broken')
    a = Image.new('RGBA',(1,1),(255,0,0,0))
    b = Image.new('RGBA',(1,1),(0,255,0,0))
    assert pixels(a) == pixels(b)
    b.putpixel((0,0),(0,255,0,1))
    assert pixels(a) != pixels(b)
    print('PASS: optimized-name parser, API/density selection, missing-resource rejection, PNG normalization')


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('apk', nargs='?', type=Path)
    parser.add_argument('--aapt2', help='SDK build-tools aapt2 executable')
    parser.add_argument('--self-test', action='store_true')
    args = parser.parse_args()
    if args.self_test:
        self_test()
    expected = []
    dark = image(RES/'drawable-nodpi/logo_esimko_wordmark.png')
    light = image(RES/'drawable-nodpi/logo_esimko_wordmark_light.png')
    source = image(ROOT/'branding/source.png')
    assert hashlib.sha256((ROOT/'branding/source.png').read_bytes()).hexdigest() == 'a48b0daca0b268d5a89afecde34f603ed4d939f01a3ce00b5dc5c3d34bad41e6', 'Official source changed'
    assert dark.size == light.size == (904,227)
    assert dark.tobytes() == source.tobytes(), 'Dark logo changed from official source'
    assert dark.getchannel('A').tobytes() == light.getchannel('A').tobytes()
    assert dark.crop((0,0,240,227)).tobytes() == light.crop((0,0,240,227)).tobytes(), 'Crest changed'
    for x in range(240,904):
        for y in range(227):
            if light.getpixel((x,y))[3]:
                assert light.getpixel((x,y))[:3] == (255,255,255)
    for name in ('logo_esimko_wordmark','logo_esimko_wordmark_light'):
        expected.append((f'drawable/{name}','nodpi',RES/f'drawable-nodpi/{name}.png'))
    for density,size,legacy_size,small_size in DENSITIES:
        foreground = image(RES/f'mipmap-{density}/ic_launcher_foreground.png')
        assert foreground.size == (size,size)
        safe_circle(foreground)
        mono = image(RES/f'mipmap-{density}/ic_launcher_monochrome.png')
        assert mono.size == foreground.size
        assert mono.getchannel('A').tobytes() == foreground.getchannel('A').tobytes()
        white_alpha(mono)
        base = Image.new('RGBA',(legacy_size,legacy_size),'white')
        base.alpha_composite(image(RES/'mipmap-xxxhdpi/ic_launcher_foreground.png').resize(base.size,Image.Resampling.LANCZOS))
        for name in ('ic_launcher_foreground','ic_launcher_monochrome','ic_launcher','ic_launcher_round'):
            path = RES/f'mipmap-{density}/{name}.png'
            im = image(path)
            if name in ('ic_launcher','ic_launcher_round'):
                assert im.size == (legacy_size,legacy_size)
                if name == 'ic_launcher':
                    assert pixels(im) == pixels(base), 'Legacy crest/wordmark changed'
                else:
                    assert im.getpixel((0,0))[3] == 0 and im.getpixel((legacy_size//2,legacy_size//2))[3] == 255
                    # Rounded fallback may change only the background edge, not artwork.
                    assert im.crop((legacy_size//4,legacy_size//4,legacy_size*3//4,legacy_size*3//4)).tobytes() == base.crop((legacy_size//4,legacy_size//4,legacy_size*3//4,legacy_size*3//4)).tobytes()
            expected.append((f'mipmap/{name}',density,path))
        path = RES/f'drawable-{density}/ic_notification_esimko.png'
        small = image(path)
        assert small.size == (small_size,small_size), 'Small icon must be 24dp'
        white_alpha(small)
        # Ignore sub-visible Lanczos ringing when measuring padding.
        bounds = small.getchannel('A').point(lambda a: 255 if a > 10 else 0).getbbox()
        assert bounds and bounds[0] > 0 and bounds[1] > 0 and bounds[2] < small_size and bounds[3] < small_size, 'Notification padding missing'
        expected.append(('drawable/ic_notification_esimko',density,path))
    assert not (RES/'drawable-nodpi/ic_notification_esimko.png').exists(), 'Remove density-independent 96px small icon'
    for version in (26,33):
        for name in ('ic_launcher','ic_launcher_round'):
            root = ET.parse(RES/f'mipmap-anydpi-v{version}/{name}.xml').getroot()
            assert root.tag == 'adaptive-icon'
            layers = {node.tag:node.get(ANDROID+'drawable') for node in root}
            wanted = {'background':'@color/ic_launcher_background','foreground':'@mipmap/ic_launcher_foreground'}
            if version == 33:
                wanted['monochrome'] = '@mipmap/ic_launcher_monochrome'
            assert layers == wanted, f'Wrong adaptive bindings: {name} API {version}'
    print('PASS: official source/colour crest+wordmark, alpha, safe geometry, 5-density legacy/round/themed/24dp notification resources, API26/API33 adaptive XML')
    if args.apk:
        verify_apk(args.apk.resolve(),find_aapt2(args.aapt2),expected)


if __name__ == '__main__':
    main()
