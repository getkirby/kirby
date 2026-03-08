import { describe, expect, it } from "vitest";
import System from "./system";

describe("panel.system", () => {
	describe("reset()", () => {
		it("restores all default values", () => {
			const system = System();

			system.set({ csrf: "dev", title: "Kirby" });
			system.reset();

			expect(system.state()).toStrictEqual(system.defaults());
		});
	});

	describe("set()", () => {
		it("applies partial state", () => {
			const system = System();

			system.set({ csrf: "dev", title: "Kirby" });

			expect(system.csrf).toStrictEqual("dev");
			expect(system.title).toStrictEqual("Kirby");
			expect(system.isLocal).toStrictEqual(false);
		});
	});

	describe("state()", () => {
		it("returns correct defaults", () => {
			const system = System();

			expect(system.state()).toStrictEqual({
				ascii: {},
				csrf: null,
				isLocal: false,
				locales: {},
				slugs: [],
				title: null
			});
		});
	});
});
