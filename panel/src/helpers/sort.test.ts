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
