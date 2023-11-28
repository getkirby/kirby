/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import { body } from "./request.js";

describe.concurrent("request globals", () => {
	it("should create body from object", async () => {
		const result = body({
			a: "A"
		});

		expect(result).toStrictEqual(JSON.stringify({ a: "A" }));
	});

	it("should create body from string", async () => {
		const result = body("test");
		expect(result).toStrictEqual("test");
	});

	it("should create body from FormData", async () => {
		const formData = new FormData();
		formData.append("a", "A");

		const result = body(formData);

		expect(result).toStrictEqual(JSON.stringify({ a: "A" }));
	});
});
