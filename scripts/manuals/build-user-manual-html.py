from __future__ import annotations

from pathlib import Path
from datetime import datetime

import markdown
from bs4 import BeautifulSoup


ROOT = Path(__file__).resolve().parents[2]
MANUALS_DIR = ROOT / "docs" / "manuals"
BUILD_DIR = MANUALS_DIR / "build"
CSS_PATH = MANUALS_DIR / "assets" / "manual.css"
MANUAL_FILES = [
    MANUALS_DIR / "manual-proveedor.md",
    MANUALS_DIR / "manual-trabajador.md",
]


def build_document(manual_path: Path, css: str) -> str:
    source = manual_path.read_text(encoding="utf-8")
    html_body = markdown.markdown(
        source,
        extensions=["tables", "fenced_code", "attr_list", "sane_lists"],
    )

    soup = BeautifulSoup(html_body, "html.parser")

    for image in soup.find_all("img"):
        relative = image.get("src", "")
        absolute = (manual_path.parent / relative).resolve()
        image["src"] = absolute.as_uri()

    title = soup.find("h1").get_text(strip=True) if soup.find("h1") else manual_path.stem
    generated_at = datetime.now().strftime("%d-%m-%Y %H:%M")

    return f"""<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{title}</title>
    <style>{css}</style>
  </head>
  <body>
    <main>
      <article>
        <div class="meta">
          <strong>{title}</strong><br />
          Documento generado automáticamente desde Markdown el {generated_at}.
        </div>
        {str(soup)}
      </article>
    </main>
  </body>
</html>
"""


def main() -> None:
    BUILD_DIR.mkdir(parents=True, exist_ok=True)
    css = CSS_PATH.read_text(encoding="utf-8")

    for manual_path in MANUAL_FILES:
        output_path = BUILD_DIR / f"{manual_path.stem}.html"
        output_path.write_text(build_document(manual_path, css), encoding="utf-8")
        print(f"HTML generado: {output_path}")


if __name__ == "__main__":
    main()
