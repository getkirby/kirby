/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Translation from "./translation.js";

describe.concurrent("panel.translation", () => {
	it("should have a default state", async () => {
		const translation = Translation();

		const state = {
			code: null,
			data: {},
			direction: "ltr",
			name: null,
			weekday: 1
		};

		expect(translation.key()).toStrictEqual("translation");
		expect(translation.state()).toStrictEqual(state);
	});

	it("should set lang & direction", async () => {
		const translation = Translation();

		translation.set({
			code: "en",
			direction: "ltr"
		});

		expect(document.documentElement.lang).toStrictEqual("en");
		expect(document.body.dir).toStrictEqual("ltr");

		translation.set({
			code: "fr",
			direction: "rtl"
		});

		expect(document.documentElement.lang).toStrictEqual("fr");
		expect(document.body.dir).toStrictEqual("rtl");
	});

	it("should translate", async () => {
		const translation = Translation();

		translation.set({
			data: {
				simple: "Test",
				template: "Hello {{ name }}",
				object: { theme: "dark" }
			}
		});

		// simple
		expect(translation.translate("simple")).toStrictEqual("Test");

		// with fallback
		expect(translation.translate("does-not-exist", {}, "Test")).toStrictEqual(
			"Test"
		);

		// with object as result
		expect(translation.translate("object")).toStrictEqual({
			theme: "dark"
		});

		// with data
		expect(translation.translate("template", { name: "Peter" })).toStrictEqual(
			"Hello Peter"
		);

		// with invalid input
		expect(translation.translate(123)).toBeUndefined();
	});
});
