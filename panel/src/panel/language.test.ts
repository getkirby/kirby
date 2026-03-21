import { describe, expect, it } from "vitest";
import Language from "./language";

describe("panel.language", () => {
	describe("isDefault", () => {
		it("is false by default", () => {
			const language = Language();
			expect(language.isDefault).toStrictEqual(false);
		});

		it("reflects the default property", () => {
			const language = Language();
			language.set({ default: true });
			expect(language.isDefault).toStrictEqual(true);
		});
	});

	describe("reset()", () => {
		it("restores all default values", () => {
			const language = Language();

			language.set({ code: "de", name: "German", default: true });
			language.reset();

			expect(language.state()).toStrictEqual(language.defaults());
		});
	});

	describe("set()", () => {
		it("applies partial state", () => {
			const language = Language();

			language.set({ code: "de", name: "German" });

			expect(language.code).toStrictEqual("de");
			expect(language.name).toStrictEqual("German");
			expect(language.direction).toStrictEqual("ltr");
		});
	});

	describe("state()", () => {
		it("returns correct defaults", () => {
			const language = Language();

			expect(language.state()).toStrictEqual({
				code: null,
				default: false,
				direction: "ltr",
				hasCustomDomain: false,
				name: null,
				rules: {}
			});
		});
	});
});
