import { test, expect } from "#test";

test.describe("progress component", () => {
	test.beforeEach(async ({ page }) => {
		await page.goto("/panel/lab/components/progress");
	});

	test("renders correctly", async ({ page }) => {
		const examples = page.locator(".k-lab-examples");
		await expect(examples).toHaveScreenshot();
	});
});
