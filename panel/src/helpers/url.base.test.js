/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import url from "./url.js";

describe("$helper.url.base", () => {
	it("should return the origin", () => {
		const result = url.base();
		expect(result).toStrictEqual(new URL(window.location.origin));
	});

	it("should return the base href", () => {
		const base = document.createElement("base");
		base.href = "http://localhost:3000";

		const result = url.base();
		expect(result).toStrictEqual(new URL("http://localhost:3000"));
	});
});
