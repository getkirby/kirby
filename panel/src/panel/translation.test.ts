import { afterEach, describe, expect, it } from "vitest";
import dayjs from "@/libraries/dayjs";
import html, { HtmlString } from "./html";
import Translation from "./translation";

describe("panel.translation", () => {
	afterEach(() => {
		dayjs.locale("en");
	});

	describe("reset()", () => {
		it("restores all default values", () => {
			const translation = Translation();

			translation.set({ code: "de", name: "German", weekday: 0 });
			translation.reset();

			expect(translation.state()).toStrictEqual(translation.defaults());
		});
	});

	describe("set()", () => {
		it("applies partial state", () => {
			const translation = Translation();

			translation.set({ code: "de", name: "German" });

			expect(translation.code).toStrictEqual("de");
			expect(translation.name).toStrictEqual("German");
			expect(translation.direction).toStrictEqual("ltr");
		});

		it("updates document.documentElement.lang", () => {
			const translation = Translation();
			translation.set({ code: "en", direction: "ltr" });
			expect(document.documentElement.lang).toStrictEqual("en");

			translation.set({ code: "fr", direction: "rtl" });
			expect(document.documentElement.lang).toStrictEqual("fr");
		});

		it("updates document.body.dir", () => {
			const translation = Translation();
			translation.set({ code: "en", direction: "ltr" });
			expect(document.body.dir).toStrictEqual("ltr");

			translation.set({ code: "ar", direction: "rtl" });
			expect(document.body.dir).toStrictEqual("rtl");
		});

		it("activates the dayjs locale of the new translation", () => {
			const translation = Translation();

			translation.set({ code: "de" });

			expect(dayjs.locale()).toStrictEqual("de");
		});

		it("falls back to English without a code", () => {
			const translation = Translation();

			translation.set({ code: "de" });
			translation.set({ code: null });

			expect(dayjs.locale()).toStrictEqual("en");
		});
	});

	describe("state()", () => {
		it("returns correct defaults", () => {
			const translation = Translation();

			expect(translation.state()).toStrictEqual({
				code: null,
				data: {},
				direction: "ltr",
				name: null,
				weekday: 1
			});
		});
	});

	describe("translate()", () => {
		it("returns a simple string", () => {
			const translation = Translation();
			translation.set({ data: { simple: "Test" } });
			expect(translation.translate("simple")).toStrictEqual("Test");
		});

		it("interpolates template placeholders", () => {
			const translation = Translation();
			translation.set({ data: { greeting: "Hello {{ name }}" } });
			expect(
				translation.translate("greeting", { name: "Peter" })
			).toStrictEqual("Hello Peter");
		});

		it("uses the key as fallback when key does not exist", () => {
			const translation = Translation();
			expect(translation.translate("missing.key")).toStrictEqual("missing.key");
		});

		it("accepts fallback as second argument", () => {
			const translation = Translation();
			expect(translation.translate("missing.key", "Fallback")).toStrictEqual(
				"Fallback"
			);
		});

		it("accepts fallback as third argument alongside data", () => {
			const translation = Translation();
			expect(
				translation.translate("missing.key", { name: "Peter" }, "Fallback")
			).toStrictEqual("Fallback");
		});

		it("returns undefined for non-string keys", () => {
			const translation = Translation();
			expect(translation.translate(123)).toBeUndefined();
		});
	});

	describe("translateHtml()", () => {
		it("marks the translation as trusted HTML", () => {
			const translation = Translation();
			translation.set({ data: { confirm: "Delete <b>the page</b>?" } });

			const result = translation.translateHtml("confirm");

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).toStrictEqual("Delete <b>the page</b>?");
		});

		it("escapes interpolated values", () => {
			const translation = Translation();
			translation.set({ data: { greeting: "Hello <b>{{ name }}</b>" } });

			expect(
				translation.translateHtml("greeting", { name: "<script>" }).toString()
			).toStrictEqual("Hello <b>&lt;script&gt;</b>");
		});

		it("escapes nested values", () => {
			const translation = Translation();
			translation.set({ data: { greeting: "Hello {{ user.name }}" } });

			expect(
				translation
					.translateHtml("greeting", { user: { name: "<script>" } })
					.toString()
			).toStrictEqual("Hello &lt;script&gt;");
		});

		it("passes trusted values through unescaped", () => {
			const translation = Translation();
			translation.set({ data: { greeting: "Hello {{ name }}" } });

			expect(
				translation
					.translateHtml("greeting", { name: html("<b>Peter</b>") })
					.toString()
			).toStrictEqual("Hello <b>Peter</b>");
		});

		it("keeps the placeholder fallback for empty values", () => {
			const translation = Translation();
			translation.set({ data: { greeting: "Hello {{ name }}" } });

			expect(
				translation.translateHtml("greeting", { name: null }).toString()
			).toStrictEqual("Hello …");
		});

		it("uses the key as fallback when key does not exist", () => {
			const translation = Translation();
			expect(translation.translateHtml("missing.key").toString()).toStrictEqual(
				"missing.key"
			);
		});

		it("accepts fallback as second argument", () => {
			const translation = Translation();
			expect(
				translation.translateHtml("missing.key", "<b>Fallback</b>").toString()
			).toStrictEqual("<b>Fallback</b>");
		});

		it("accepts fallback as third argument alongside data", () => {
			const translation = Translation();
			expect(
				translation
					.translateHtml("missing.key", { name: "Peter" }, "Fallback")
					.toString()
			).toStrictEqual("Fallback");
		});

		it("returns an empty HtmlString for non-string keys", () => {
			const translation = Translation();
			const result = translation.translateHtml(123 as unknown as string);

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).toStrictEqual("");
		});
	});
});
