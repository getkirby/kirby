import { test, expect } from "#test";

test.describe("tags component", () => {
	test.beforeEach(async ({ page }) => {
		await page.goto("/panel/lab/components/tags/2_tags");
	});

	test("renders correctly", async ({ page }) => {
		const examples = page.locator(".k-lab-examples");
		await expect(examples).toHaveScreenshot();
	});
});
