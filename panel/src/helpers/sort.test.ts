import { afterEach, describe, expect, it, vi } from "vitest";
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

	const values = [
		"Item 10",
		"item 2",
		"Item 1",
		"",
		" padded ",
		"0x1F",
		"0xA",
		"2024-05-12",
		"2023-12-01",
		"Ärger",
		"Zebra",
		"Öl",
		"10",
		"9",
		"1.5",
		"photo-10.jpg",
		"photo-2.jpg"
	];

	it.each([
		[
			{},
			[
				"",
				"1.5",
				"9",
				"0xA",
				"10",
				"0x1F",
				"2023-12-01",
				"2024-05-12",
				"Ärger",
				"Item 1",
				"Item 10",
				"Öl",
				"Zebra",
				"item 2",
				" padded ",
				"photo-2.jpg",
				"photo-10.jpg"
			]
		],
		[
			{ insensitive: true },
			[
				"",
				"1.5",
				"9",
				"0xA",
				"10",
				"0x1F",
				"2023-12-01",
				"2024-05-12",
				"Ärger",
				"Item 1",
				"item 2",
				"Item 10",
				"Öl",
				" padded ",
				"photo-2.jpg",
				"photo-10.jpg",
				"Zebra"
			]
		],
		[
			{ desc: true },
			[
				"photo-10.jpg",
				"photo-2.jpg",
				" padded ",
				"item 2",
				"Zebra",
				"Öl",
				"Item 10",
				"Item 1",
				"Ärger",
				"2024-05-12",
				"2023-12-01",
				"0x1F",
				"10",
				"0xA",
				"9",
				"1.5",
				""
			]
		]
	])("sorts mixed values with %j", (options, expected) => {
		expect([...values].sort(sort(options))).toStrictEqual(expected);
	});

	describe("preparing values", () => {
		afterEach(() => {
			vi.restoreAllMocks();
		});

		it("prepares each value only once", () => {
			// 37 and 100 are coprime, so this shuffles 0–99
			const items = Array.from(
				{ length: 100 },
				(_, i) => `Item ${(i * 37) % 100}`
			);
			const parse = vi.spyOn(Date, "parse");
			items.sort(sort());
			expect(parse).toHaveBeenCalledTimes(items.length);
		});
	});
});
