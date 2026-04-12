import { describe, expect, it, beforeEach } from "vitest";
import Theme, { defaults } from "./theme";
import Panel from "./panel";

describe("panel.theme", () => {
	beforeEach(() => {
		localStorage.clear();
	});

	describe("current", () => {
		it("falls back to system when no setting or config", () => {
			const panel = Panel.create(app);
			const theme = Theme(panel);
			theme.system = "light";
			expect(theme.current).toStrictEqual("light");
		});

		it("uses setting over system", () => {
			const panel = Panel.create(app);
			const theme = Theme(panel);
			theme.system = "light";
			theme.set("dark");
			expect(theme.current).toStrictEqual("dark");
		});

		it("uses config over system when no setting", () => {
			const panel = Panel.create(app);
			panel.config.theme = "dark";
			const theme = Theme(panel);
			theme.system = "light";
			expect(theme.current).toStrictEqual("dark");
		});

		it("prefers setting over config", () => {
			const panel = Panel.create(app);
			panel.config.theme = "dark";
			const theme = Theme(panel);
			theme.set("light");
			expect(theme.current).toStrictEqual("light");
		});

		it("resolves system setting to actual system theme", () => {
			const panel = Panel.create(app);
			const theme = Theme(panel);
			theme.system = "dark";
			theme.set("system");
			expect(theme.current).toStrictEqual("dark");
		});

		it("resolves system config to actual system theme", () => {
			const panel = Panel.create(app);
			panel.config.theme = "system";
			const theme = Theme(panel);
			theme.system = "light";
			expect(theme.current).toStrictEqual("light");
		});
	});

	describe("defaults()", () => {
		it("sets system to dark when media matches dark", () => {
			const media = { matches: true } as MediaQueryList;
			expect(defaults(media).system).toStrictEqual("dark");
		});

		it("sets system to light when media does not match", () => {
			const media = { matches: false } as MediaQueryList;
			expect(defaults(media).system).toStrictEqual("light");
		});

		it("sets system to light when no media query available", () => {
			expect(defaults().system).toStrictEqual("light");
		});

		it("restores setting from localStorage", () => {
			localStorage.setItem("kirby$theme", "dark");
			expect(defaults().setting).toStrictEqual("dark");
		});
	});

	describe("reset()", () => {
		it("clears setting and removes from localStorage", () => {
			const panel = Panel.create(app);
			const theme = Theme(panel);
			theme.set("dark");
			theme.reset();
			expect(theme.setting).toStrictEqual(null);
			expect(localStorage.getItem("kirby$theme")).toStrictEqual(null);
		});
	});

	describe("set()", () => {
		it("updates setting and persists to localStorage", () => {
			const panel = Panel.create(app);
			const theme = Theme(panel);
			theme.set("dark");
			expect(theme.setting).toStrictEqual("dark");
			expect(localStorage.getItem("kirby$theme")).toStrictEqual("dark");
		});
	});
});
