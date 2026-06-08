import { describe, expect, it } from "vitest";
import type { UnitType } from "dayjs";
import dayjs from "./dayjs";

describe("dayjs.merge()", () => {
	const data: {
		name: string;
		a: string;
		b: string;
		unit: "date" | "time" | UnitType[];
		expected: string;
	}[] = [
		{
			name: "date",
			a: "2020-02-29 16:05:15",
			b: "2021-03-01 18:42:11",
			unit: "date",
			expected: "2021-03-01 16:05:15"
		},
		{
			name: "time",
			a: "2020-02-29 16:05:15",
			b: "2020-03-01 18:42:11",
			unit: "time",
			expected: "2020-02-29 18:42:11"
		},
		{
			name: "year/date/minute",
			a: "2020-02-29 16:05:15",
			b: "2021-03-01 18:42:11",
			unit: ["year", "date", "minute"],
			expected: "2021-02-01 16:42:15"
		}
	];

	it.each(data)("$name", ({ a, b, unit, expected }) => {
		expect(dayjs(a).merge(dayjs(b), unit)).toEqual(dayjs(expected));
	});

	it("Invalid input", () => {
		const a = dayjs("2020-01-01");
		expect(a.merge(undefined)).toStrictEqual(a);
		expect(a.merge(null)).toStrictEqual(a);
		expect(a.merge(dayjs("Invalid"))).toStrictEqual(a);
	});

	it("Unsupported unit alias", () => {
		const a = dayjs("2020-01-01");
		const b = dayjs("2020-02-01");
		expect(() => {
			// @ts-expect-error - testing invalid input
			a.merge(b, "foo");
		}).toThrow("Invalid merge unit alias");
	});
});
