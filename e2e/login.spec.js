const playwright = require('@playwright/test');

playwright.test('Login', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/');
  await page.getByPlaceholder('Ingresa tu nombre de usuario').click();
  await page.getByPlaceholder('Ingresa tu nombre de usuario').fill('admin');
  await page.getByPlaceholder('Ingresa tu nombre de usuario').press('Tab');
  await page.getByPlaceholder('Ingresa tu contraseña').fill('123456');
  await page.getByRole('button', { name: 'Ingresar' }).click();
});
