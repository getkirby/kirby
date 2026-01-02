/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Language from "./language.js";

describe.concurrent("panel.language", () => {
	it("should have a default state", async () => {
		const language = Language();

		const state = {
			code: null,
			default: false,
			direction: "ltr",
			hasAbsoluteUrl: false,
			name: null,
			rules: null
		};

		expect(language.key()).toStrictEqual("language");
		expect(language.state()).toStrictEqual(state);
	});

	it("should have isDefault getter", async () => {
		const language = Language();

		expect(language.isDefault).toStrictEqual(false);

		language.set({
			default: true
		});

		expect(language.isDefault).toStrictEqual(true);
	});

	it("should have hasAbsoluteUrl property", async () => {
		const language = Language();

		// default state
		expect(language.hasAbsoluteUrl).toStrictEqual(false);

		// set to true
		language.set({
			hasAbsoluteUrl: true
		});
		expect(language.hasAbsoluteUrl).toStrictEqual(true);

		// set to false
		language.set({
			hasAbsoluteUrl: false
		});
		expect(language.hasAbsoluteUrl).toStrictEqual(false);
	});
});
