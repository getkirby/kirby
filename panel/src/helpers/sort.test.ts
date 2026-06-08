import { describe, expect, it } from "vitest";
import sort from "./sort";

describe("$helper.sort()", () => {
	it("should sort", () => {
		const users = [
			{ username: "Homer" },
			{ username: "Marge" },
			{ username: "Bart" },
			{ username: "Lisa" },
			{ username: "Maggie" }
		];

		const expected = [
			{ username: "Bart" },
			{ username: "Homer" },
			{ username: "Lisa" },
			{ username: "Maggie" },
			{ username: "Marge" }
		];

		const sorter = sort();

		users.sort((a, b) => sorter(a.username, b.username));

		expect(users).toEqual(expected);
	});

	it("should return 0 when both values are empty", () => {
		expect(sort()("", "")).toBe(0);
	});

	it("should sort empty before non-empty", () => {
		expect(sort()("", "a")).toBeLessThan(0);
		expect(sort()("a", "")).toBeGreaterThan(0);
	});

	it("should reverse the order when desc", () => {
		expect(sort({ desc: true })("a", "b")).toBeGreaterThan(0);
	});

	it("should sort case-insensitively", () => {
		expect(sort({ insensitive: true })("A", "a")).toBe(0);
		expect(sort()("A", "a")).not.toBe(0);
	});

	it("should compare hex codes", () => {
		expect(sort()("0x1A", "0x1B")).toBeLessThan(0);
		expect(sort()("0xFF", "0x10")).toBeGreaterThan(0);
		expect(sort()("0x10", "0x10")).toBe(0);
	});

	it("should sort numeric tokens before string tokens", () => {
		expect(sort()("10", "abc")).toBeLessThan(0);
		expect(sort()("abc", "10")).toBeGreaterThan(0);
	});

	it("should sort numbers naturally", () => {
		expect(sort()("item2", "item10")).toBeLessThan(0);
	});

	it("should use locale comparison for unicode", () => {
		expect(sort()("é", "z")).toBeLessThan(0);
		expect(sort()("z", "é")).toBeGreaterThan(0);
		expect(sort()("é", "é")).toBe(0);
	});

	it("should tiebreak equal-ish tokens by string form", () => {
		expect(sort()("a01", "a1")).toBeLessThan(0);
		expect(sort()("a1", "a01")).toBeGreaterThan(0);
	});

	it("should compare strings with an uneven number of tokens", () => {
		expect(sort()("z", "z1")).toBeLessThan(0);
		expect(sort()("z1", "z")).toBeGreaterThan(0);
	});

	it("should compare parseable dates", () => {
		expect(sort()("2020-01-01", "2020-06-15")).toBeLessThan(0);
	});

	it("should locale-compare a non-final unicode token", () => {
		expect(sort()("é1", "é2")).toBeLessThan(0);
	});

	it("should return 0 for identical multi-token strings", () => {
		expect(sort()("abc 123 def", "abc 123 def")).toBe(0);
	});
});
