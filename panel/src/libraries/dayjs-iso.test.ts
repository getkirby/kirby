import { describe, expect, it } from "vitest";
import type { UnitType } from "dayjs";
import dayjs, { type DatetimeType } from "./dayjs";

describe("dayjs.iso()", () => {
	const data: {
		input: string;
		units: Record<string, number>;
		format?: DatetimeType;
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
		},
		// without a format, any of the three ISO formats is accepted
		{
			input: "2020-02-29 16:05:15",
			units: { year: 2020, month: 1, date: 29, hour: 16, minute: 5, second: 15 }
		},
		{
			input: "2020-02-29",
			units: { year: 2020, month: 1, date: 29, hour: 0, minute: 0, second: 0 }
		},
		{
			input: "16:05:15",
			units: { hour: 16, minute: 5, second: 15 }
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

	// parsing is strict: out-of-range values must not silently roll over
	const invalid: [string, DatetimeType | undefined][] = [
		["2020-02-30", "date"],
		["2020-02-30", undefined],
		["2020-13-05", "date"],
		["2020-13-05", undefined],
		["24:00:00", "time"],
		["24:00:00", undefined],
		["25:61:00", "time"],
		["2020-01-01", "time"],
		["16:05:15", "date"],
		["2020-01-01 00:00:00+00:00", undefined],
		["", undefined]
	];

	it.each(invalid)("%s (%s) should be null", (input, format) => {
		expect(dayjs.iso(input, format)).toBeNull();
	});

	it("should fall back to datetime for an unknown format", () => {
		const format = "nope" as DatetimeType;

		expect(dayjs.iso("2020-02-29 16:05:15", format)!.get("date")).toStrictEqual(
			29
		);
		expect(dayjs.iso("2020-02-30 16:05:15", format)).toBeNull();
	});
});

describe("dayjs.toISO()", () => {
	const data: {
		date: Date;
		expected: string;
		format?: DatetimeType;
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

	it("should fall back to datetime for an unknown format", () => {
		const date = new Date(2020, 6, 3, 17, 24, 11);
		const format = "nope" as DatetimeType;
		expect(dayjs(date).toISO(format)).toStrictEqual("2020-07-03 17:24:11");
	});
});
