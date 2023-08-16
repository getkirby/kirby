/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import { chunks } from "./array";

describe.concurrent("$helper.array.chunks()", () => {
	it("should return an empty array if the input array is empty", () => {
		const input = [];
		const result = chunks(input, 2);
		expect(result).toEqual([]);
	});

	it("should return the input array if the chunk size is greater than the array length", () => {
		const input = [1, 2, 3];
		const result = chunks(input, 4);
		expect(result).toEqual([input]);
	});

	it("should split the input array into chunks of the specified size", () => {
		const input = [1, 2, 3, 4, 5];
		const result = chunks(input, 2);
		expect(result).toEqual([[1, 2], [3, 4], [5]]);
	});

	it("should not modify the input array", () => {
		const input = [1, 2, 3];
		chunks(input, 2);
		expect(input).toEqual([1, 2, 3]);
	});
});
