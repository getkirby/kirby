/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import string from "./string";

describe.concurrent("$helper.string.camelToKebab", () => {
	it("should convert camelCase", () => {
		const result = string.camelToKebab("helloWorld");
		expect(result).toBe("hello-world");
	});
});
