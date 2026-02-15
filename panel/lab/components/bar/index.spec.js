import { test, expect } from "#test";

test.describe("bar component", () => {
	test.beforeEach(async ({ page }) => {
		await page.goto("/panel/lab/components/bar");
	});

	test("renders correctly", async ({ page }) => {
		const examples = page.locator(".k-lab-examples");
		await expect(examples).toHaveScreenshot();
	});
});
