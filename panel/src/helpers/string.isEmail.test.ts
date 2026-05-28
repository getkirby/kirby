import { describe, expect, it } from "vitest";
import string from "./string";

describe.concurrent("$helper.string.isEmail", () => {
	it("should work with null", () => {
		expect(string.isEmail(null)).toStrictEqual(false);
	});

	it("should work with undefined", () => {
		expect(string.isEmail(undefined)).toStrictEqual(false);
	});

	it("should work with non-string types", () => {
		expect(string.isEmail(42)).toStrictEqual(false);
		expect(string.isEmail(true)).toStrictEqual(false);
		expect(string.isEmail({})).toStrictEqual(false);
	});

	it("should work with empty string", () => {
		expect(string.isEmail("")).toStrictEqual(false);
	});

	it("should accept a plain address", () => {
		expect(string.isEmail("user@example.com")).toStrictEqual(true);
	});

	it("should accept a + alias", () => {
		expect(string.isEmail("user+tag@example.com")).toStrictEqual(true);
	});

	it("should accept a modern TLD", () => {
		expect(string.isEmail("user@example.museum")).toStrictEqual(true);
		expect(string.isEmail("user@example.photography")).toStrictEqual(true);
	});

	it("should accept multi-label domains", () => {
		expect(string.isEmail("user@a.b.c.example.com")).toStrictEqual(true);
	});

	it("should reject a missing @", () => {
		expect(string.isEmail("userexample.com")).toStrictEqual(false);
	});

	it("should reject a missing TLD dot", () => {
		expect(string.isEmail("user@example")).toStrictEqual(false);
	});

	it("should reject whitespace", () => {
		expect(string.isEmail("user @example.com")).toStrictEqual(false);
		expect(string.isEmail("user@ example.com")).toStrictEqual(false);
		expect(string.isEmail("user@example .com")).toStrictEqual(false);
	});

	it("should reject URI-scheme-shaped values", () => {
		expect(string.isEmail("javascript:alert(1)")).toStrictEqual(false);
		expect(string.isEmail("data:text/html,x")).toStrictEqual(false);
		expect(string.isEmail("mailto:user@example.com")).toStrictEqual(false);
	});

	it("should reject scheme-confusion payloads with an @", () => {
		expect(string.isEmail("javascript:foo@bar.baz")).toStrictEqual(false);
	});

	it("should require an alphabetic TLD of 2+ chars", () => {
		expect(string.isEmail("user@example.c")).toStrictEqual(false);
		expect(string.isEmail("user@example.123")).toStrictEqual(false);
	});

	it("should reject `:` in the domain", () => {
		expect(string.isEmail("user@bar.com:8080")).toStrictEqual(false);
	});

	it("should accept trailing mailto-URI parts (query/fragment)", () => {
		expect(string.isEmail("user@bar.com?subject=hi")).toStrictEqual(true);
		expect(string.isEmail("user@bar.com?subject=hi&body=hey")).toStrictEqual(
			true
		);
		expect(string.isEmail("user@bar.com#fragment")).toStrictEqual(true);
	});

	it("should reject trailing mailto-URI parts in strict mode", () => {
		expect(string.isEmail("user@bar.com?subject=hi", true)).toStrictEqual(
			false
		);
		expect(string.isEmail("user@bar.com#fragment", true)).toStrictEqual(false);
	});

	it("should accept bare addresses in strict mode", () => {
		expect(string.isEmail("user@bar.com", true)).toStrictEqual(true);
		expect(string.isEmail("user+tag@example.museum", true)).toStrictEqual(true);
	});

	it("should still reject scheme-confusion payloads in strict mode", () => {
		expect(string.isEmail("javascript:foo@bar.baz", true)).toStrictEqual(false);
		expect(string.isEmail("user@bar.com:8080", true)).toStrictEqual(false);
	});
});
