import { defineConfig, devices } from '@playwright/test';

const ownServer = process.env.E2E_SERVE === '1';
const ownServerUrl = 'http://127.0.0.1:8081';
const baseURL = ownServer ? ownServerUrl : (process.env.E2E_BASE_URL ?? 'http://localhost:8090');

export default defineConfig({
    fullyParallel: false,
    outputDir: 'test-results/artifacts',
    projects: [
        {
            name: 'chromium',
            testIgnore: /visual\.spec\.ts/,
            use: { ...devices['Desktop Chrome'] },
        },
        {
            name: 'visual',
            testMatch: /visual\.spec\.ts/,
            use: { ...devices['Desktop Chrome'] },
        },
    ],
    reporter: process.env.CI ? [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }]] : 'list',
    retries: process.env.CI ? 1 : 0,
    testDir: './tests/E2E',
    use: {
        baseURL,
        screenshot: 'only-on-failure',
        trace: 'on-first-retry',
    },
    webServer: ownServer
        ? {
              command: 'php spark serve --host 127.0.0.1 --port 8081',
              env: { APP_FULL_BASE_URL: `${ownServerUrl}/`, PHP_CLI_SERVER_WORKERS: '4' },
              reuseExistingServer: true,
              timeout: 60_000,
              url: `${ownServerUrl}/login`,
          }
        : undefined,
    workers: 1,
});
