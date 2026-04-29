import { describe, expect, it } from "vitest";
import { isAbortError } from "./error";

describe("isAbortError()", () => {
	it("returns true for an Error with name AbortError", () => {
		const error = new Error("Aborted");
		error.name = "AbortError";
		expect(isAbortError(error)).toBe(true);
	});

	it("returns true for a native AbortError", () => {
		const error = AbortSignal.abort().reason;
		expect(isAbortError(error)).toBe(true);
	});

	it("returns false for a regular Error", () => {
		expect(isAbortError(new Error("Oops"))).toBe(false);
	});

	it("returns false for a non-Error with name AbortError", () => {
		expect(isAbortError({ name: "AbortError" })).toBe(false);
	});

	it("returns false for null", () => {
		expect(isAbortError(null)).toBe(false);
	});

	it("returns false for a string", () => {
		expect(isAbortError("AbortError")).toBe(false);
	});
});
