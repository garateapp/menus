import fs from 'node:fs/promises';
import path from 'node:path';

import { chromium } from 'playwright';

const baseUrl = process.env.MANUAL_BASE_URL ?? 'https://menus.test';
const outputDir = path.resolve('docs/manuals/screenshots');
const credentials = {
  supplier: {
    email: process.env.MANUAL_SUPPLIER_EMAIL ?? 'supplier@greenex.cl',
    password: process.env.MANUAL_SUPPLIER_PASSWORD ?? 'password',
  },
  worker: {
    email: process.env.MANUAL_WORKER_EMAIL ?? 'worker1@greenex.cl',
    password: process.env.MANUAL_WORKER_PASSWORD ?? 'password',
  },
};

const supplierShots = [
  { name: 'supplier-dashboard.png', url: '/supplier/dashboard', fullPage: true },
  { name: 'supplier-calendar-week.png', url: '/supplier/weekly-menus?view=week&date=2026-04-06', fullPage: true },
  {
    name: 'supplier-calendar-drawer.png',
    url: '/supplier/weekly-menus?view=week&date=2026-04-06&selected_date=2026-04-06',
    fullPage: true,
  },
  {
    name: 'supplier-daily-report.png',
    url: '/supplier/reports/daily?date=2026-03-23',
    fullPage: true,
  },
  {
    name: 'supplier-weekly-report.png',
    url: '/supplier/reports/weekly?weekly_menu_id=1&day=2026-03-23',
    fullPage: true,
  },
];

const workerShots = [
  { name: 'worker-dashboard.png', url: '/worker/dashboard', fullPage: true },
  { name: 'worker-calendar-week.png', url: '/worker/menus?view=week&date=2026-04-06', fullPage: true },
  {
    name: 'worker-calendar-drawer.png',
    url: '/worker/menus?view=week&date=2026-04-06&selected_date=2026-04-06',
    fullPage: true,
  },
  { name: 'worker-selections.png', url: '/worker/selections', fullPage: true },
];

async function ensureOutputDir() {
  await fs.mkdir(outputDir, { recursive: true });
}

async function captureLoginScreenshot(browser) {
  const page = await browser.newPage({
    baseURL: baseUrl,
    ignoreHTTPSErrors: true,
    viewport: { width: 1600, height: 1200 },
  });

  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.screenshot({
    path: path.join(outputDir, 'login.png'),
    fullPage: true,
  });
  await page.close();
}

async function login(page, email, password) {
  await page.goto('/login', { waitUntil: 'networkidle' });
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await Promise.all([
    page.waitForURL('**/dashboard', { waitUntil: 'networkidle' }),
    page.getByRole('button', { name: 'Entrar' }).click(),
  ]);
}

async function captureAuthenticatedSet(browser, userKey, shots) {
  const context = await browser.newContext({
    baseURL: baseUrl,
    ignoreHTTPSErrors: true,
    viewport: { width: 1600, height: 1200 },
  });

  const page = await context.newPage();
  const user = credentials[userKey];

  await login(page, user.email, user.password);

  for (const shot of shots) {
    await page.goto(shot.url, { waitUntil: 'networkidle' });
    await page.screenshot({
      path: path.join(outputDir, shot.name),
      fullPage: shot.fullPage,
    });
  }

  await context.close();
}

async function main() {
  await ensureOutputDir();

  const browser = await chromium.launch({ headless: true });

  try {
    await captureLoginScreenshot(browser);
    await captureAuthenticatedSet(browser, 'supplier', supplierShots);
    await captureAuthenticatedSet(browser, 'worker', workerShots);
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
