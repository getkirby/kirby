import { test } from "@playwright/test";

test("authenticate", async ({ page }) => {
	await page.goto("/env/auth/admin");
	await page.context().storageState({ path: "e2e/session.json" });
});
