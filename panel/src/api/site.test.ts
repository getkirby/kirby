import { describe, expect, it, vi } from "vitest";
import type Api from "./index";
import site from "./site";

describe("api.site.update()", () => {
	it("should send a PATCH request", async () => {
		const api = { patch: vi.fn(), post: vi.fn() };
		await site(api as unknown as Api).update({ title: "Site" });

		expect(api.patch).toHaveBeenCalledWith("site", { title: "Site" });
		expect(api.post).not.toHaveBeenCalled();
	});
});
