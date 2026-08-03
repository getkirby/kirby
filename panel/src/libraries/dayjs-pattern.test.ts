import { describe, expect, it } from "vitest";
import dayjs from "./dayjs";
import type { PatternUnit } from "./dayjs-pattern";

describe("dayjs.pattern().at()", () => {
	const data: {
		source: string;
		cursors: { start: number; end?: number; unit: string }[];
	}[] = [
		{
			source: "YYYY-MM-DD",
			cursors: [
				{ start: 0, unit: "year" },
				{ start: 2, unit: "year" },
				{ start: 5, unit: "month" },
				{ start: 6, unit: "month" },
				{ start: 9, unit: "day" },
				{ start: 8, end: 10, unit: "day" },
				{ start: 6, end: 10, unit: "month" },
				{ start: 0, end: 4, unit: "year" }
			]
		},
		{
			source: "MM/DD/YY HH:mm",
			cursors: [
				{ start: 0, unit: "month" },
				{ start: 1, unit: "month" },
				{ start: 3, unit: "day" },
				{ start: 4, unit: "day" },
				{ start: 6, unit: "year" },
				{ start: 10, unit: "hour" },
				{ start: 9, end: 11, unit: "hour" }
			]
		},
		{
			source: "DD. MM. YYYY",
			cursors: [
				{ start: 0, unit: "day" },
				{ start: 2, unit: "day" },
				{ start: 3, unit: "day" },
				{ start: 5, unit: "month" },
				{ start: 7, unit: "month" },
				{ start: 9, unit: "year" }
			]
		},
		{
			source: "MMMM D, YYYY",
			cursors: [
				{ start: 0, unit: "month" },
				{ start: 5, unit: "day" },
				{ start: 6, unit: "day" },
				{ start: 8, unit: "year" }
			]
		},
		{
			source: "h:mm A",
			cursors: [
				{ start: 0, unit: "hour" },
				{ start: 2, unit: "minute" },
				{ start: 5, unit: "meridiem" }
			]
		}
	];

	describe.each(data)("$source", ({ source, cursors }) => {
		const pattern = dayjs.pattern(source);

		it.each(cursors)("$start - $end: $unit", ({ start, end, unit }) => {
			const part = pattern.at(start, end);
			expect(part!.unit).toBe(unit);
		});
	});
});

describe("dayjs.pattern().format()", () => {
	it("no value", () => {
		const pattern = dayjs.pattern("YYYY-MM-DD");
		expect(pattern.format()).toBe(null);
	});

	it("invalid value", () => {
		const pattern = dayjs.pattern("YYYY-MM-DD");
		expect(pattern.format(dayjs("aaaa-bb-cc"))).toBe(null);
	});

	const dt = dayjs("2020-05-04 13:14:03");

	it.each(
		Object.entries({
			"YYYY-MM-DD": "2020-05-04",
			"M/D/YY h:m a": "5/4/20 1:14 pm",
			"H:m:s": "13:14:3"
		})
	)("%s", (source, expected) => {
		expect(dayjs.pattern(source).format(dt)).toBe(expected);
	});
});

describe("dayjs.pattern().parts", () => {
	const data: Record<
		string,
		{ index: number; unit?: string; start: number; end: number }[]
	> = {
		"YYYY-MM-DD": [
			{ index: 0, unit: "year", start: 0, end: 3 },
			{ index: 1, unit: "month", start: 5, end: 6 },
			{ index: 2, unit: "day", start: 8, end: 9 }
		],
		"MM/DD/YY HH:mm": [
			{ index: 0, unit: "month", start: 0, end: 1 },
			{ index: 1, unit: "day", start: 3, end: 4 },
			{ index: 2, unit: "year", start: 6, end: 7 },
			{ index: 3, unit: "hour", start: 9, end: 10 },
			{ index: 4, unit: "minute", start: 12, end: 13 }
		],
		"MMMM D, YYYY": [
			{ index: 0, unit: "month", start: 0, end: 3 },
			{ index: 1, unit: "day", start: 5, end: 5 },
			{ index: 2, unit: "year", start: 8, end: 11 }
		],
		// a multi-character separator creates no phantom part
		"DD. MM. YYYY": [
			{ index: 0, unit: "day", start: 0, end: 1 },
			{ index: 1, unit: "month", start: 4, end: 5 },
			{ index: 2, unit: "year", start: 8, end: 11 }
		],
		"h:mm A": [
			{ index: 0, unit: "hour", start: 0, end: 0 },
			{ index: 1, unit: "minute", start: 2, end: 3 },
			{ index: 2, unit: "meridiem", start: 5, end: 5 }
		],
		// a repeated marker keeps its own position
		"DD.MM.YYYY (DD)": [
			{ index: 0, unit: "day", start: 0, end: 1 },
			{ index: 1, unit: "month", start: 3, end: 4 },
			{ index: 2, unit: "year", start: 6, end: 9 },
			{ index: 3, unit: "day", start: 12, end: 13 }
		]
	};

	it.each(Object.entries(data))("%s", (source, parts) => {
		expect(dayjs.pattern(source).parts).toEqual(parts);
	});

	it("leaves the unit of an unsupported marker undefined", () => {
		expect(dayjs.pattern("QQ").parts).toEqual([
			{ index: 0, unit: undefined, start: 0, end: 1 }
		]);
	});

	it("returns no parts for a pattern without letters", () => {
		expect(dayjs.pattern("").parts).toEqual([]);
		expect(dayjs.pattern("--.--").parts).toEqual([]);
	});
});

describe("dayjs.pattern().source", () => {
	it.each(["DD.MM.YYYY", "YYYY年M月D日", "foo", ""])(
		"keeps %s verbatim",
		(source) => {
			expect(dayjs.pattern(source).source).toBe(source);
		}
	);
});

describe("dayjs.pattern().type", () => {
	const data: [string, string][] = [
		["YYYY-MM-DD", "date"],
		["DD.MM.YYYY", "date"],
		["D. MMMM YYYY", "date"],
		// a pattern with both is still a date
		["YYYY-MM-DD HH:mm", "date"],
		["HH:mm", "time"],
		["hh:mm A", "time"],
		["mm:ss", "time"],
		// a pattern without any usable marker says nothing about
		// what it reads, so it falls back to the default
		["foo", "date"],
		["", "date"]
	];

	it.each(data)("%s is a %s pattern", (source, expected) => {
		expect(dayjs.pattern(source).type).toBe(expected);
	});
});

describe("dayjs.pattern().units", () => {
	const data: [string, PatternUnit[]][] = [
		["DD.MM.YYYY", ["day", "month", "year"]],
		["MM/DD/YYYY", ["month", "day", "year"]],
		["MMMM D, YYYY", ["month", "day", "year"]],
		["hh:mm:ss a", ["hour", "minute", "second", "meridiem"]],
		// markers written without a separator are read as well
		["YYYYMMDD", ["year", "month", "day"]],
		["DDMMYY", ["day", "month", "year"]],
		["HHmm", ["hour", "minute"]],
		// a trailing separator adds no unit
		["YYYY-MM-", ["year", "month"]],
		["", []],
		["--.--", []]
	];

	it.each(data)("%s", (source, expected) => {
		expect(dayjs.pattern(source).units).toEqual(expected);
	});
});
