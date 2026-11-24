import { type App } from "vue";
import { describe, expect, it, vi } from "vitest";
import Legacy from "./legacy";
import Panel from "./panel";

describe("panel.legacy", () => {
	it("should install the global property aliases", () => {
		const panel = Panel.create(app);

		const globalProperties: Record<string, unknown> = {};
		Legacy.install({
			config: { globalProperties }
		} as unknown as App);

		// direct references
		expect(globalProperties.$api).toBe(panel.api);
		expect(globalProperties.$events).toBe(panel.events);
		expect(globalProperties.$html).toBe(panel.html);
		expect(globalProperties.$reload).toBe(panel.reload);
		expect(globalProperties.$url).toBe(panel.url);

		// translator is also assigned back onto the panel
		expect(globalProperties.$t).toBe(panel.t);
		expect(panel.$t).toBe(panel.t);

		// bound function shortcuts
		expect(globalProperties.$dialog).toBeTypeOf("function");
		expect(globalProperties.$drawer).toBeTypeOf("function");
		expect(globalProperties.$dropdown).toBeTypeOf("function");
		expect(globalProperties.$go).toBeTypeOf("function");
	});

	it("should call into the Panel features through the shortcuts", async () => {
		const panel = Panel.create(app);

		// the shortcut binds panel.view.open at install time,
		// so the spy needs to be in place before installing
		const open = vi
			.spyOn(panel.view, "open")
			.mockResolvedValue(panel.view.state());

		const globalProperties: Record<string, (...args: unknown[]) => unknown> =
			{};
		Legacy.install({
			config: { globalProperties }
		} as unknown as App);

		await globalProperties.$go("/some/path");
		expect(open).toHaveBeenCalledWith("/some/path");
		open.mockRestore();
	});
});
