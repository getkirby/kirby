/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { headers } from "./request.js";

describe.concurrent("request headers", () => {
	it("should create default headers", async () => {
		const result = headers();
		const expected = {
			"content-type": "application/json",
			"x-csrf": false,
			"x-fiber": true,
			"x-fiber-globals": false,
			"x-fiber-referrer": false
		};

		expect(result).toStrictEqual(expected);
	});

	it("should add custom headers", async () => {
		const result = headers({
			"x-foo": "test"
		});

		const expected = {
			"content-type": "application/json",
			"x-csrf": false,
			"x-fiber": true,
			"x-fiber-globals": false,
			"x-fiber-referrer": false,
			"x-foo": "test"
		};

		expect(result).toStrictEqual(expected);
	});

	it("should set options", async () => {
		const result = headers(
			{},
			{
				csrf: "dev",
				globals: ["language"],
				referrer: "/test"
			}
		);

		const expected = {
			"content-type": "application/json",
			"x-csrf": "dev",
			"x-fiber": true,
			"x-fiber-globals": "language",
			"x-fiber-referrer": "/test"
		};

		expect(result).toStrictEqual(expected);
	});
});
