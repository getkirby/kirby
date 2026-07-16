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

	describe("rules", () => {
		// the slug input uses these rules when the active language defines
		// any; otherwise (no/empty `rules`, e.g. single-language or a
		// `languages: true` setup without a language) it falls back to the
		// system slug rules
		it("is empty by default", () => {
			const language = Language();
			expect(language.rules).toStrictEqual({});
		});

		it("applies the rules of the active language", () => {
			const language = Language();
			const rules = { ä: "ae", ö: "oe", ü: "ue" };

			language.set({ code: "de", name: "German", rules });

			expect(language.rules).toStrictEqual(rules);
		});

		it("falls back to the empty default when set without rules", () => {
			const language = Language();

			language.set({ code: "de", name: "German" });

			expect(language.rules).toStrictEqual({});
		});

		it("is restored to the empty default on reset()", () => {
			const language = Language();

			language.set({ code: "de", rules: { ü: "ue" } });
			language.reset();

			expect(language.rules).toStrictEqual({});
		});
	});
});
