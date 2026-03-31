import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

import { chromium } from 'playwright';

const buildDir = path.resolve('docs/manuals/build');
const manuals = [
  {
    html: path.join(buildDir, 'manual-proveedor.html'),
    pdf: path.resolve('docs/manuals/manual-proveedor.pdf'),
  },
  {
    html: path.join(buildDir, 'manual-trabajador.html'),
    pdf: path.resolve('docs/manuals/manual-trabajador.pdf'),
  },
];

async function ensureBuildArtifacts() {
  for (const manual of manuals) {
    await fs.access(manual.html);
  }
}

async function main() {
  await ensureBuildArtifacts();

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    for (const manual of manuals) {
      await page.goto(pathToFileURL(manual.html).href, { waitUntil: 'load' });
      await page.pdf({
        path: manual.pdf,
        format: 'A4',
        printBackground: true,
        margin: {
          top: '12mm',
          right: '10mm',
          bottom: '12mm',
          left: '10mm',
        },
      });
      console.log(`PDF generado: ${manual.pdf}`);
    }
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
