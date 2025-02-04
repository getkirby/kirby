/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { globals } from "./request.js";

describe.concurrent("request globals", () => {
	it("should create globals from string", async () => {
		const result = globals("language");
		expect(result).toStrictEqual("language");
	});

	it("should create globals from array", async () => {
		const result = globals(["language"]);
		expect(result).toStrictEqual("language");
	});

	it("should skip globals", async () => {
		const result = globals();
		expect(result).toStrictEqual(false);
	});
});
