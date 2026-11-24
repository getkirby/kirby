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
			// a cursor inside the leading literal belongs to the first part
			source: "[Am] D. MMMM YYYY",
			cursors: [
				{ start: 0, unit: "day" },
				{ start: 2, unit: "day" },
				{ start: 5, unit: "day" },
				{ start: 8, unit: "month" },
				{ start: 13, unit: "year" },
				{ start: 0, end: 17, unit: "day" }
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

describe("dayjs.pattern().at(start, end, dt)", () => {
	// the cursor sits in the rendered string, not in the pattern
	const dt = dayjs("2020-09-04");

	const cursors: { start: number; end?: number; unit: string }[] = [
		{ start: 0, unit: "day" },
		{ start: 2, unit: "month" },
		{ start: 10, unit: "month" },
		{ start: 12, unit: "year" },
		{ start: 2, end: 11, unit: "month" }
	];

	it.each(cursors)("$start - $end: $unit", ({ start, end, unit }) => {
		const part = dayjs.pattern("D MMMM YYYY").at(start, end, dt);
		expect(part!.unit).toBe(unit);
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

describe("dayjs.pattern().literals", () => {
	const data: Record<string, string[]> = {
		"DD.MM.YYYY": [],
		"DD.MM.YYYY [um] HH:mm": ["um"],
		"D [de] MMMM [de] YYYY": ["de", "de"],
		"HH[h]mm": ["h"],
		"DD.MM.YYYY[]": [""]
	};

	it.each(Object.entries(data))("%s", (source, expected) => {
		expect(dayjs.pattern(source).literals).toEqual(expected);
	});
});

describe("dayjs.pattern().parts()", () => {
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
		expect(dayjs.pattern(source).parts()).toEqual(parts);
	});

	it("leaves the unit of an unsupported marker undefined", () => {
		expect(dayjs.pattern("QQ").parts()).toEqual([
			{ index: 0, unit: undefined, start: 0, end: 1 }
		]);
	});

	it("returns no parts for a pattern without letters", () => {
		expect(dayjs.pattern("").parts()).toEqual([]);
		expect(dayjs.pattern("--.--").parts()).toEqual([]);
	});

	it("falls back to the pattern for an invalid datetime", () => {
		expect(dayjs.pattern("D MMMM YYYY").parts(dayjs("nope"))).toEqual(
			dayjs.pattern("D MMMM YYYY").parts()
		);
	});

	it("reads no part from what the pattern escapes", () => {
		expect(dayjs.pattern("DD.MM.YYYY [um] HH:mm").parts()).toEqual([
			{ index: 0, unit: "day", start: 0, end: 1 },
			{ index: 1, unit: "month", start: 3, end: 4 },
			{ index: 2, unit: "year", start: 6, end: 9 },
			{ index: 3, unit: "hour", start: 16, end: 17 },
			{ index: 4, unit: "minute", start: 19, end: 20 }
		]);
	});
});

describe("dayjs.pattern().parts(dt)", () => {
	// a marker and what it prints can differ in width,
	// e.g. `D` printing `13` or `MMMM` printing `September`
	const dt = dayjs("2020-09-04 13:14:03");

	const data: Record<string, string[]> = {
		// what each part covers in the rendered string
		"D MMMM YYYY": ["4", "September", "2020"],
		"MMMM D, YYYY": ["September", "4", "2020"],
		"DD.MM.YYYY": ["04", "09", "2020"],
		"YYYY-MM-DD": ["2020", "09", "04"],
		"M/D/YY h:m a": ["9", "4", "20", "1", "14", "pm"],
		"D. MMM YYYY, H:m:s": ["4", "Sep", "2020", "13", "14", "3"],
		"DD.MM.YYYY [um] HH:mm": ["04", "09", "2020", "13", "14"],
		"D [de] MMMM [de] YYYY": ["4", "September", "2020"],
		"HH[h]mm": ["13", "14"]
	};

	it.each(Object.entries(data))("%s", (source, expected) => {
		const pattern = dayjs.pattern(source);
		const formatted = pattern.format(dt)!;

		const covered = pattern
			.parts(dt)
			.map((part) => formatted.slice(part.start, part.end + 1));

		expect(covered).toEqual(expected);
	});

	it("positions the parts in the rendered string", () => {
		expect(dayjs.pattern("D MMMM YYYY").parts(dt)).toEqual([
			{ index: 0, unit: "day", start: 0, end: 0 },
			{ index: 1, unit: "month", start: 2, end: 10 },
			{ index: 2, unit: "year", start: 12, end: 15 }
		]);
	});
});

describe("dayjs.pattern().source", () => {
	it.each(["DD.MM.YYYY", "YYYY年M月D日", "foo", ""])(
		"keeps %s verbatim",
		(source) => {
			expect(dayjs.pattern(source).source).toBe(source);
		}
	);

	it.each([undefined, null])("reads %s as an empty pattern", (source) => {
		const pattern = dayjs.pattern(source);
		expect(pattern.source).toBe("");
		expect(pattern.parts()).toEqual([]);
		expect(pattern.units).toEqual([]);
	});
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
		["HH[h]mm", "time"],
		["HH:mm [Uhr]", "time"],
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
		["YYYYMMDD", ["year", "month", "day"]],
		["DDMMYY", ["day", "month", "year"]],
		["HHmm", ["hour", "minute"]],
		["DD.MM.YYYY [um] HH:mm", ["day", "month", "year", "hour", "minute"]],
		["HH[h]mm", ["hour", "minute"]],
		["[Am] D. MMMM", ["day", "month"]],
		["YYYY-MM-", ["year", "month"]],
		["", []],
		["--.--", []]
	];

	it.each(data)("%s", (source, expected) => {
		expect(dayjs.pattern(source).units).toEqual(expected);
	});
});
