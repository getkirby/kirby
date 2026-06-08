import { describe, expect, it } from "vitest";
import { ISOFormat } from "./dayjs-iso";
import type { UnitType } from "dayjs";
import dayjs from "./dayjs";

describe("dayjs.iso()", () => {
	const data: {
		input: string;
		units: Record<string, number>;
		format?: ISOFormat;
	}[] = [
		{
			input: "2020-02-29 16:05:15",
			units: { year: 2020, month: 1, date: 29, hour: 16, minute: 5, second: 15 }
		},
		{
			input: "2020-02-29",
			units: { year: 2020, month: 1, date: 29 },
			format: "date"
		},
		{
			input: "16:05:15",
			units: { hour: 16, minute: 5, second: 15 },
			format: "time"
		}
	];

	it.each(data)("%input", ({ input, units, format }) => {
		const dt = dayjs.iso(input, format);

		for (const unit in units) {
			expect(dt!.get(unit as UnitType)).toStrictEqual(units[unit]);
		}
	});

	it("should return null for an invalid date", () => {
		expect(dayjs.iso("not a date")).toBeNull();
	});
});

describe("dayjs.toISO()", () => {
	const data: {
		date: Date;
		expected: string;
		format?: ISOFormat;
	}[] = [
		{
			date: new Date(2020, 6, 3, 17, 24, 11),
			expected: "2020-07-03 17:24:11"
		},
		{
			date: new Date(2020, 6, 3, 17, 24, 11),
			expected: "2020-07-03",
			format: "date"
		},
		{
			date: new Date(2020, 6, 3, 17, 24, 11),
			expected: "17:24:11",
			format: "time"
		}
	];

	it.each(data)("$expected", ({ date, expected, format }) => {
		expect(dayjs(date).toISO(format)).toStrictEqual(expected);
	});
});
