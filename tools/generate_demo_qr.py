#!/usr/bin/env python
"""Generate and verify a reproducible, non-payment demo QR card."""

from __future__ import annotations

import argparse
import base64
import html
import os
import re
import shutil
import subprocess
import tempfile
from pathlib import Path
from typing import Iterable

import qrcode
import zxingcpp
from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
WIDTH = 2048
HEIGHT = 2896
CREAM = "#FBF6EE"
ESPRESSO = "#2B1B12"
BRICK = "#A63D2F"
WHITE = "#FFFFFF"
BLACK = "#000000"
PAYLOAD_SUFFIX = "bukan-pembayaran-sungguhan"
OUTPUT_PNG = ROOT / "public" / "uploads" / "qris-demo-lowermanhattan.png"
OUTPUT_SVG = ROOT / "public" / "uploads" / "qris-demo-lowermanhattan.svg"
PLACEHOLDER_SVG = ROOT / "public" / "uploads" / "qris-placeholder.svg"
PREVIEW_PNG = ROOT / "docs" / "screenshots" / "qr_demo" / "kartu_qr_demo.png"


def font_path() -> Path:
    windir = Path(os.environ.get("WINDIR", "C:/Windows"))
    candidates = [
        windir / "Fonts" / "georgiab.ttf",
        Path("/usr/share/fonts/truetype/msttcorefonts/Georgia_Bold.ttf"),
        Path("/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf"),
        Path("/Library/Fonts/Georgia Bold.ttf"),
        windir / "Fonts" / "arialbd.ttf",
    ]
    for candidate in candidates:
        if candidate.is_file():
            return candidate
    raise FileNotFoundError(
        "No Georgia Bold or serif fallback font found; install a system serif font."
    )


def load_font(path: Path, size: int) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(str(path), size=size)


def wrap_text(
    text: str, font: ImageFont.FreeTypeFont, max_width: int
) -> list[str]:
    words = text.split()
    lines: list[str] = []
    current = ""
    for word in words:
        candidate = f"{current} {word}".strip()
        if current and font.getlength(candidate) > max_width:
            lines.append(current)
            current = word
        else:
            current = candidate
    if current:
        lines.append(current)
    return lines


def luminance(color: str) -> float:
    channels = [int(color[index : index + 2], 16) / 255 for index in (1, 3, 5)]
    linear = [
        value / 12.92 if value <= 0.04045 else ((value + 0.055) / 1.055) ** 2.4
        for value in channels
    ]
    return 0.2126 * linear[0] + 0.7152 * linear[1] + 0.0722 * linear[2]


def contrast_ratio(foreground: str, background: str) -> float:
    light, dark = sorted(
        (luminance(foreground), luminance(background)), reverse=True
    )
    return (light + 0.05) / (dark + 0.05)


def centered_text(
    draw: ImageDraw.ImageDraw,
    text: str,
    y: int,
    font: ImageFont.FreeTypeFont,
    fill: str,
) -> None:
    draw.text((WIDTH // 2, y), text, font=font, fill=fill, anchor="mm")


def make_qr(payload: str) -> list[list[bool]]:
    code = qrcode.QRCode(
        version=None,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=1,
        border=4,
    )
    code.add_data(payload, optimize=0)
    code.make(fit=True)
    return code.get_matrix()


def create_layout(
    store_name: str, demo_label: str, disclaimer: str, project_line: str
) -> tuple[Image.Image, list[list[bool]], int, int, int, list[str], ImageFont.FreeTypeFont]:
    font_file = font_path()
    title_font = load_font(font_file, 150)
    while title_font.getlength(store_name) > 1760:
        title_font = load_font(font_file, title_font.size - 2)

    label_font = load_font(font_file, 88)
    footer_font = load_font(font_file, 82)
    footer_lines = wrap_text(disclaimer, footer_font, 1740)
    project_font = load_font(font_file, 70)
    qr_matrix = make_qr(f"DEMO|{store_name}|{PAYLOAD_SUFFIX}")

    module_size = 1260 // len(qr_matrix)
    qr_size = module_size * len(qr_matrix)
    qr_x = (WIDTH - qr_size) // 2
    qr_y = 660

    image = Image.new("RGB", (WIDTH, HEIGHT), CREAM)
    draw = ImageDraw.Draw(image)
    draw.rounded_rectangle(
        (44, 44, WIDTH - 44, HEIGHT - 44),
        radius=76,
        fill=CREAM,
        outline=ESPRESSO,
        width=10,
    )

    centered_text(draw, store_name, 270, title_font, ESPRESSO)
    draw.rounded_rectangle(
        (614, 382, 1434, 532), radius=34, fill=BRICK
    )
    centered_text(draw, demo_label, 457, label_font, WHITE)

    draw.rounded_rectangle(
        (qr_x - 20, qr_y - 20, qr_x + qr_size + 20, qr_y + qr_size + 20),
        radius=28,
        fill=WHITE,
        outline=ESPRESSO,
        width=5,
    )
    for row_index, row in enumerate(qr_matrix):
        for column_index, dark in enumerate(row):
            if dark:
                left = qr_x + column_index * module_size
                top = qr_y + row_index * module_size
                draw.rectangle(
                    (left, top, left + module_size - 1, top + module_size - 1),
                    fill=BLACK,
                )

    line_height = 104
    first_y = 2210 - ((len(footer_lines) - 1) * line_height) // 2
    for index, line in enumerate(footer_lines):
        centered_text(
            draw, line, first_y + index * line_height, footer_font, ESPRESSO
        )
    centered_text(draw, project_line, 2500, project_font, ESPRESSO)

    return image, qr_matrix, qr_x, qr_y, module_size, footer_lines, font_file


def svg_text(
    text: str, y: int, font_size: int, fill: str, font_family: str = "Georgia"
) -> str:
    return (
        f'<text x="{WIDTH // 2}" y="{y}" text-anchor="middle" '
        f'dominant-baseline="middle" font-family="{font_family}, serif" '
        f'font-size="{font_size}" font-weight="700" fill="{fill}">'
        f"{html.escape(text)}</text>"
    )


def create_svg(
    store_name: str,
    demo_label: str,
    disclaimer: str,
    project_line: str,
    qr_matrix: list[list[bool]],
    qr_x: int,
    qr_y: int,
    module_size: int,
    footer_lines: list[str],
    font_file: Path,
) -> str:
    title_font = load_font(font_file, 150)
    while title_font.getlength(store_name) > 1760:
        title_font = load_font(font_file, title_font.size - 2)
    label_font = load_font(font_file, 88)
    footer_font = load_font(font_file, 82)
    project_font = load_font(font_file, 70)
    qr_size = module_size * len(qr_matrix)

    modules: list[str] = []
    for row_index, row in enumerate(qr_matrix):
        for column_index, dark in enumerate(row):
            if dark:
                x = qr_x + column_index * module_size
                y = qr_y + row_index * module_size
                modules.append(f"M{x} {y}h{module_size}v{module_size}h-{module_size}z")

    footer_start = 2210 - ((len(footer_lines) - 1) * 104) // 2
    parts = [
        '<?xml version="1.0" encoding="UTF-8"?>',
        (
            '<svg xmlns="http://www.w3.org/2000/svg" '
            'width="173.397mm" height="245.363mm" '
            f'viewBox="0 0 {WIDTH} {HEIGHT}" role="img" '
            'aria-labelledby="title description" shape-rendering="crispEdges">'
        ),
        "<title id=\"title\">Demo QR - Lower Manhattan Coffee Shop</title>",
        (
            '<desc id="description">Kode QR demo non-pembayaran. '
            "Bukan pembayaran sungguhan.</desc>"
        ),
        f'<rect width="{WIDTH}" height="{HEIGHT}" fill="{CREAM}"/>',
        (
            f'<rect x="44" y="44" width="{WIDTH - 88}" height="{HEIGHT - 88}" '
            f'rx="76" fill="{CREAM}" stroke="{ESPRESSO}" stroke-width="10"/>'
        ),
        svg_text(store_name, 270, title_font.size, ESPRESSO),
        '<rect x="614" y="382" width="820" height="150" rx="34" fill="#A63D2F"/>',
        svg_text(demo_label, 457, label_font.size, WHITE),
        (
            f'<rect x="{qr_x - 20}" y="{qr_y - 20}" '
            f'width="{qr_size + 40}" height="{qr_size + 40}" rx="28" '
            f'fill="{WHITE}" stroke="{ESPRESSO}" stroke-width="5"/>'
        ),
        (
            f'<path fill="{BLACK}" d="{"".join(modules)}" '
            'shape-rendering="crispEdges"/>'
        ),
    ]
    for index, line in enumerate(footer_lines):
        parts.append(
            svg_text(
                line, footer_start + index * 104, footer_font.size, ESPRESSO
            )
        )
    parts.append(svg_text(project_line, 2500, project_font.size, ESPRESSO))
    parts.append("</svg>")
    return "\n".join(parts)


def decoded_text(image: Image.Image) -> str:
    result = zxingcpp.read_barcode(image)
    if result is None:
        raise RuntimeError("QR decoder could not read the generated image.")
    return result.text


def render_svg(svg: str) -> Image.Image:
    windir = Path(os.environ.get("WINDIR", "C:/Windows"))
    browser_candidates = [
        Path("C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"),
        Path("C:/Program Files/Microsoft/Edge/Application/msedge.exe"),
        Path("C:/Program Files/Google/Chrome/Application/chrome.exe"),
        Path("C:/Program Files (x86)/Google/Chrome/Application/chrome.exe"),
    ]
    browser = next((path for path in browser_candidates if path.is_file()), None)
    if browser is None:
        browser_name = shutil.which("msedge") or shutil.which("google-chrome")
        browser = Path(browser_name) if browser_name else None
    if browser is None:
        raise FileNotFoundError(
            "SVG verification requires Microsoft Edge or Google Chrome."
        )

    encoded_svg = base64.b64encode(svg.encode("utf-8")).decode("ascii")
    document = (
        "<!doctype html><html><head><meta charset='utf-8'>"
        f"<style>html,body{{margin:0;width:{WIDTH}px;height:{HEIGHT}px;overflow:hidden}}"
        f"img{{display:block;width:{WIDTH}px;height:{HEIGHT}px}}</style></head>"
        f"<body><img alt='' src='data:image/svg+xml;base64,{encoded_svg}'></body></html>"
    )
    with tempfile.TemporaryDirectory(prefix="qr-demo-svg-") as temporary:
        temp_path = Path(temporary)
        html_path = temp_path / "render.html"
        screenshot_path = temp_path / "render.png"
        html_path.write_text(document, encoding="utf-8")
        subprocess.run(
            [
                str(browser),
                "--headless=new",
                "--disable-gpu",
                "--hide-scrollbars",
                "--no-first-run",
                "--force-device-scale-factor=1",
                f"--window-size={WIDTH},{HEIGHT}",
                f"--user-data-dir={temp_path / 'profile'}",
                f"--screenshot={screenshot_path}",
                html_path.resolve().as_uri(),
            ],
            check=True,
            capture_output=True,
            timeout=90,
        )
        rendered = Image.open(screenshot_path).convert("RGB")
    if rendered.size != (WIDTH, HEIGHT):
        raise RuntimeError(
            f"SVG renderer returned {rendered.width}x{rendered.height}, "
            f"expected {WIDTH}x{HEIGHT}."
        )
    return rendered


def verify(
    image: Image.Image,
    svg: str,
    payload: str,
    output_png: Path,
) -> None:
    rendered_svg = render_svg(svg)

    verification_images: Iterable[tuple[str, Image.Image]] = [
        ("PNG", image),
        ("SVG-rendered PNG", rendered_svg),
        ("PNG at 390px", image.resize((390, round(HEIGHT * 390 / WIDTH)), Image.Resampling.LANCZOS)),
        ("PNG at 600px", image.resize((600, round(HEIGHT * 600 / WIDTH)), Image.Resampling.LANCZOS)),
    ]
    for label, candidate in verification_images:
        result = decoded_text(candidate)
        if result != payload or result.startswith("000201"):
            raise RuntimeError(f"{label} decoded unexpected content: {result!r}")
        print(f"SCAN {label}: {result}")

    for name, foreground, background in (
        ("espresso on cream", ESPRESSO, CREAM),
        ("white on brick", WHITE, BRICK),
        ("black on white", BLACK, WHITE),
    ):
        ratio = contrast_ratio(foreground, background)
        print(f"CONTRAST {name}: {ratio:.2f}:1")
        if ratio < 4.5:
            raise RuntimeError(f"Text contrast below 4.5:1 for {name}.")

    if not output_png.is_file() or output_png.stat().st_size >= 1_500_000:
        raise RuntimeError("PNG output is missing or exceeds the 1.5 MB target.")
    if image.mode != "RGB" or image.size != (WIDTH, HEIGHT):
        raise RuntimeError("Generated PNG has unexpected dimensions or color mode.")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--store-name", default="Lower Manhattan Coffee Shop"
    )
    parser.add_argument("--demo-label", default="MODE DEMO")
    parser.add_argument(
        "--disclaimer",
        default="Bukan pembayaran sungguhan. Hanya untuk demonstrasi aplikasi.",
    )
    parser.add_argument(
        "--project-line", default="Proyek akademik – UBSI Kaliabang"
    )
    args = parser.parse_args()

    if not args.store_name.strip() or not args.demo_label.strip():
        parser.error("Store name and demo label must not be empty.")
    payload = f"DEMO|{args.store_name}|{PAYLOAD_SUFFIX}"
    if payload.startswith("000201") or re.search(
        r"\b(?:BTN|GPN|GOPAY|OVO|DANA|SHOPEEPAY)\b", payload, re.IGNORECASE
    ):
        parser.error("Payload contains a disallowed payment-related marker.")

    image, matrix, qr_x, qr_y, module_size, footer_lines, font_file = create_layout(
        args.store_name, args.demo_label, args.disclaimer, args.project_line
    )
    OUTPUT_PNG.parent.mkdir(parents=True, exist_ok=True)
    PREVIEW_PNG.parent.mkdir(parents=True, exist_ok=True)

    image.save(OUTPUT_PNG, format="PNG", optimize=True, dpi=(300, 300))
    image.resize(
        (600, round(HEIGHT * 600 / WIDTH)), Image.Resampling.LANCZOS
    ).save(PREVIEW_PNG, format="PNG", optimize=True)

    svg = create_svg(
        args.store_name,
        args.demo_label,
        args.disclaimer,
        args.project_line,
        matrix,
        qr_x,
        qr_y,
        module_size,
        footer_lines,
        font_file,
    )
    OUTPUT_SVG.write_text(svg + "\n", encoding="utf-8")
    PLACEHOLDER_SVG.write_text(svg + "\n", encoding="utf-8")
    verify(image, svg, payload, OUTPUT_PNG)
    print(
        f"PNG: {OUTPUT_PNG.relative_to(ROOT)} "
        f"{image.width}x{image.height} RGB {OUTPUT_PNG.stat().st_size} bytes @ 300 dpi"
    )
    print(f"SVG: {OUTPUT_SVG.relative_to(ROOT)} ({OUTPUT_SVG.stat().st_size} bytes)")
    print(
        f"PREVIEW: {PREVIEW_PNG.relative_to(ROOT)} "
        f"{Image.open(PREVIEW_PNG).width}x{Image.open(PREVIEW_PNG).height}"
    )
    print(f"FONT: {font_file}")


if __name__ == "__main__":
    main()
