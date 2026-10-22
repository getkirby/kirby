import { describe, expect, it } from "vitest";
import System from "./system";

describe("panel.system", () => {
	describe("reset()", () => {
		it("restores all default values", () => {
			const system = System();

			system.set({ isLocal: true, title: "Kirby" });
			system.reset();

			expect(system.state()).toStrictEqual(system.defaults());
		});
	});

	describe("set()", () => {
		it("applies partial state", () => {
			const system = System();

			system.set({ isLocal: true, title: "Kirby" });

			expect(system.title).toStrictEqual("Kirby");
			expect(system.isLocal).toStrictEqual(true);
		});
	});

	describe("state()", () => {
		it("returns correct defaults", () => {
			const system = System();

			expect(system.state()).toStrictEqual({
				ascii: {},
				isLocal: false,
				locales: {},
				slugs: [],
				title: ""
			});
		});
	});
});
