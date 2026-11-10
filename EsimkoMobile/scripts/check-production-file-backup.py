"""Read-only verification of a private file-backup manifest, never prints file contents.

Usage: python check-production-file-backup.py PRIVATE_MANIFEST.json
Manifest: {"backupRoot":"absolute private backup dir", "files":[
 {"path":"relative backup path", "size":123, "sha256":"64hex"}
]}
This checks file integrity only. It does NOT prove database consistency/restore,
permissions, completeness of the operator's inventory or deployment authorization.
"""
import hashlib
import json
from pathlib import Path
import re
import sys


def verify(manifest):
    root = Path(manifest["backupRoot"]).resolve(strict=True)
    entries = manifest.get("files", [])
    if not entries:
        raise ValueError("Empty backup inventory")
    seen = set()
    for entry in entries:
        relative = Path(entry["path"])
        if relative.is_absolute() or ".." in relative.parts:
            raise ValueError("Invalid backup path")
        target = (root / relative).resolve(strict=True)
        if root not in target.parents or not target.is_file():
            raise ValueError("Backup file outside root or not a file")
        identity = str(target).casefold()
        if identity in seen:
            raise ValueError("Duplicate backup entry")
        seen.add(identity)
        expected = entry["sha256"]
        if not re.fullmatch(r"[0-9a-fA-F]{64}", expected):
            raise ValueError("Invalid expected checksum")
        if target.stat().st_size != entry["size"]:
            raise ValueError("Backup size mismatch")
        digest = hashlib.sha256()
        with target.open("rb") as stream:
            for chunk in iter(lambda: stream.read(1024 * 1024), b""):
                digest.update(chunk)
        if digest.hexdigest() != expected.lower():
            raise ValueError("Backup checksum mismatch")
    return len(seen)


if __name__ == "__main__":
    try:
        if len(sys.argv) != 2:
            raise ValueError("One private manifest path required")
        count = verify(json.loads(Path(sys.argv[1]).read_text(encoding="utf-8-sig")))
        print(f"PASS: {count} backup file checksums and sizes verified; database restore gate still required")
    except Exception as error:
        print(f"STOP: backup verification failed ({type(error).__name__})", file=sys.stderr)
        sys.exit(1)
