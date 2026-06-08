import { describe, expect, it } from "vitest";
import type { UnitType } from "dayjs";
import dayjs from "./dayjs";

describe("dayjs.validate()", () => {
	const data: Record<
		string,
		{
			boundary: string;
			type: "min" | "max";
			unit?: UnitType;
			inputs: Record<string, boolean>;
		}
	> = {
		"min by day": {
			boundary: "2020-01-05",
			type: "min",
			inputs: {
				"2020-01-05": true,
				"2020-01-06": true,
				"2020-01-04": false
			}
		},
		"max by day": {
			boundary: "2020-01-05",
			type: "max",
			inputs: {
				"2020-01-05": true,
				"2020-01-06": false,
				"2020-01-04": true
			}
		},
		"min by month": {
			boundary: "2020-01-05",
			type: "min",
			unit: "month",
			inputs: {
				"2020-01-05": true,
				"2020-01-06": true,
				"2020-01-04": true,
				"2019-12-12": false
			}
		},
		"max by month": {
			boundary: "2020-01-05",
			type: "max",
			unit: "month",
			inputs: {
				"2020-01-05": true,
				"2020-01-06": true,
				"2020-01-04": true,
				"2020-02-12": false
			}
		},
		"time-only": {
			boundary: "15:05:00",
			type: "max",
			unit: "second",
			inputs: {
				"15:05:00": true,
				"15:00:00": true,
				"15:10:00": false
			}
		}
	};

	it.each(Object.entries(data))(
		"%s",
		(_name, { boundary, type, unit, inputs }) => {
			for (const input in inputs) {
				const result = (dayjs.iso(input) ?? dayjs(input)).validate(
					boundary,
					type,
					unit
				);
				expect(result).toBe(inputs[input]);
			}
		}
	);

	it("no parameters", () => {
		expect(dayjs().validate()).toBe(true);
		expect(dayjs("Invalid").validate()).toBe(false);
	});

	it("invalid dayjs object", () => {
		expect(dayjs("Invalid").validate("2020-01-01")).toBe(false);
	});

	it("invalid boundary", () => {
		expect(dayjs("2020-01-05").validate("not a date")).toBe(false);
	});
});
