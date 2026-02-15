import { test, expect } from "#test";

test.describe("tag component", () => {
	test.beforeEach(async ({ page }) => {
		await page.route("https://picsum.photos/**", (route) =>
			route.fulfill({
				status: 200,
				contentType: "image/svg+xml",
				body: '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#ff0000"/></svg>'
			})
		);

		await page.goto("/panel/lab/components/tags/1_tag");
	});

	test("renders in dark theme", async ({ page }) => {
		const examples = page.locator(".k-lab-examples");
		await expect(examples).toHaveScreenshot();
	});

	test("renders in light theme", async ({ page }) => {
		await page.getByText("Light").click();
		const examples = page.locator(".k-lab-examples");
		await expect(examples).toHaveScreenshot();
	});
});
