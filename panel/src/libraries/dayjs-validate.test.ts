import { describe, expect, it } from "vitest";
import dayjs from "./dayjs";

describe("dayjs.validate()", () => {
	const data: Record<
		string,
		{
			boundary: string;
			type: "min" | "max";
			inputs: Record<string, boolean>;
		}
	> = {
		min: {
			boundary: "2020-01-05",
			type: "min",
			inputs: {
				"2020-01-05": true,
				"2020-01-06": true,
				"2020-01-04": false
			}
		},
		max: {
			boundary: "2020-01-05",
			type: "max",
			inputs: {
				"2020-01-05": true,
				"2020-01-06": false,
				"2020-01-04": true
			}
		},
		// a date boundary starts at midnight, so every later
		// time on the same day is past a `max`
		"max with a date boundary": {
			boundary: "2020-01-05",
			type: "max",
			inputs: {
				"2020-01-05 00:00:00": true,
				"2020-01-05 00:00:01": false,
				"2020-01-04 23:59:59": true
			}
		},
		// the time of a boundary is never ignored
		"min with a datetime boundary": {
			boundary: "2020-01-05 09:30:00",
			type: "min",
			inputs: {
				"2020-01-05 09:30:00": true,
				"2020-01-05 09:30:01": true,
				"2020-01-05 09:29:59": false,
				"2020-01-05 00:00:00": false
			}
		},
		"max with a datetime boundary": {
			boundary: "2020-01-05 09:00:00",
			type: "max",
			inputs: {
				"2020-01-05 09:00:00": true,
				"2020-01-05 08:59:59": true,
				"2020-01-05 23:59:59": false,
				"2020-01-04 09:00:00": true
			}
		},
		"time-only": {
			boundary: "15:05:00",
			type: "max",
			inputs: {
				"15:05:00": true,
				"15:00:00": true,
				"15:10:00": false
			}
		}
	};

	it.each(Object.entries(data))("%s", (_name, { boundary, type, inputs }) => {
		for (const input in inputs) {
			const result = (dayjs.iso(input) ?? dayjs(input)).validate(
				boundary,
				type
			);
			expect(result).toBe(inputs[input]);
		}
	});

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
