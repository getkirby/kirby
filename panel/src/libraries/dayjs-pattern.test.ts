import { describe, expect, it } from "vitest";
import dayjs from "./dayjs";

describe("dayjs.pattern.at()", () => {
	const data: {
		format: string;
		cursors: { start: number; end?: number; unit: string }[];
	}[] = [
		{
			format: "YYYY-MM-DD",
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
			format: "MM/DD/YY HH:mm",
			cursors: [
				{ start: 0, unit: "month" },
				{ start: 1, unit: "month" },
				{ start: 3, unit: "day" },
				{ start: 4, unit: "day" },
				{ start: 6, unit: "year" },
				{ start: 10, unit: "hour" },
				{ start: 9, end: 11, unit: "hour" }
			]
		}
	];

	describe.each(data)("%format", ({ format, cursors }) => {
		const pattern = dayjs.pattern(format);

		it.each(cursors)("%start - %end: %unit", ({ start, end, unit }) => {
			const part = pattern.at(start, end);
			expect(part!.unit).toBe(unit);
		});
	});
});

describe("dayjs.pattern.format()", () => {
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
	)("%s", (format, expected) => {
		expect(dayjs.pattern(format).format(dt)).toBe(expected);
	});
});

describe("dayjs.pattern.parts", () => {
	const data: Record<
		string,
		{ index: number; unit: string; start: number; end: number }[]
	> = {
		"YYYY-MM-DD": [
			{ index: 0, unit: "year", start: 0, end: 3 },
			{
				index: 1,
				unit: "month",
				start: 5,
				end: 6
			},
			{ index: 2, unit: "day", start: 8, end: 9 }
		],
		"MM/DD/YY HH:mm": [
			{
				index: 0,
				unit: "month",
				start: 0,
				end: 1
			},
			{ index: 1, unit: "day", start: 3, end: 4 },
			{ index: 2, unit: "year", start: 6, end: 7 },
			{
				index: 3,
				unit: "hour",
				start: 9,
				end: 10
			},
			{ index: 4, unit: "minute", start: 12, end: 13 }
		]
	};

	it.each(Object.entries(data))("%s", (format, parts) => {
		expect(dayjs.pattern(format).parts).toEqual(parts);
	});
});
