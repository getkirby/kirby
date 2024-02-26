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
});
