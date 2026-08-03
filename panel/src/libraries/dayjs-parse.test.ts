import {
	afterAll,
	afterEach,
	beforeAll,
	describe,
	expect,
	it,
	vi
} from "vitest";
import type { Dayjs } from "dayjs";
import dayjs from "./dayjs";

function parse(input: string, pattern: string = ""): Dayjs | null {
	return dayjs.parse(input, { pattern });
}

const nato = [
	"Alpha",
	"Bravo",
	"Charlie",
	"Delta",
	"Echo",
	"Foxtrot",
	"Golf",
	"Hotel",
	"India",
	"Juliett",
	"Kilo",
	"Lima"
];

function locale(name: string, data: Record<string, unknown>): void {
	dayjs.locale(name, { name, ...data } as never, true);
	dayjs.locale(name);
}

function toMonth(token: string): number | null {
	const dt = dayjs.parse(token, { pattern: "MMMM", strict: true });
	return dt === null ? null : dt.month() + 1;
}

beforeAll(() => {
	vi.useFakeTimers();
	vi.setSystemTime(new Date(2022, 0, 15));
});

afterAll(() => {
	vi.useRealTimers();
});

describe("dayjs.parse()", () => {
	const expected: Record<string, string> = {
		"2021-03-05": "2021-03-05",
		"2021-03-5": "2021-03-05",
		"2021-03-": "2021-03-01",
		"2021-03": "2021-03-01",
		"2021-3-04": "2021-03-04",
		"2021-3-4": "2021-03-04",
		"2021-3-": "2021-03-01",
		"2021-3": "2021-03-01",
		"2021-": "2021-01-01",
		20210305: "2021-03-05",
		2021: "2021-01-01",
		"05": "2022-01-05",
		5: "2022-01-05",
		"05-03-2021": "2021-03-05",
		"05-03-21": "2021-03-05",

		"31.12.2021": "2021-12-31",
		"31.1.2021": "2021-01-31",
		"2.03.2021": "2021-03-02",
		"2.3.2021": "2021-03-02",
		"02.03.21": "2021-03-02",
		"2.03.21": "2021-03-02",
		"02.3.21": "2021-03-02",
		"2.3.21": "2021-03-02",

		"31/12/2021": "2021-12-31",
		"31/1/2021": "2021-01-31",
		"2/03/2021": "2021-03-02",
		"2/3/2021": "2021-03-02",
		"02/03/21": "2021-03-02",
		"2/03/21": "2021-03-02",
		"02/3/21": "2021-03-02",
		"2/3/21": "2021-03-02",

		"01. Dec 2021": "2021-12-01",
		"1. Dec 2021": "2021-12-01",
		"01. Dec 21": "2021-12-01",
		"1. Dec 21": "2021-12-01",
		"01. Dec": "2022-12-01",
		"1. Dec": "2022-12-01",

		"1. December": "2022-12-01",
		"1. December 21": "2021-12-01",
		"12. December": "2022-12-12",
		"01. December": "2022-12-01",

		"02.03.": "2022-03-02",

		"2.": "2022-01-02",
		"02.": "2022-01-02",
		"2.3.": "2022-03-02",
		"02.3.": "2022-03-02",

		"Aug 05, 2021": "2021-08-05",
		"Aug 05 2021": "2021-08-05",
		"Aug 5, 2021": "2021-08-05",
		"Aug 5 2021": "2021-08-05",
		"Aug 05, 21": "2021-08-05",
		"Aug 05 21": "2021-08-05",
		"Aug 5, 21": "2021-08-05",
		"Aug 5 21": "2021-08-05",
		"Aug 12": "2022-08-12",
		"Aug 05": "2022-08-05",
		"Aug 5": "2022-08-05",

		December: "2022-12-01",
		Dec: "2022-12-01",

		"Feb 2022": "2022-02-01",
		"February 2022": "2022-02-01",
		"02 2022": "2022-02-01",
		"2 2022": "2022-02-01",

		"02032024": "2024-03-02",
		"March 2 2024": "2024-03-02",
		"March 2, 2024": "2024-03-02",
		"March 2 24": "2024-03-02",
		"March 2. 2024": "2024-03-02",

		"01-2024": "2024-01-01",
		"01/2024": "2024-01-01",
		"08/2025": "2025-08-01",
		"06-26-2025": "2025-06-26",

		"2021.03.05": "2021-03-05",
		"2021/03/05": "2021-03-05",
		"2024 March 02": "2024-03-02",
		"2024-March-02": "2024-03-02"
	};

	it.each(Object.entries(expected))("%s should be %s", (input, value) => {
		expect(parse(input)?.toISO("date") ?? null).toBe(value);
	});

	const invalid = [
		"",
		"not a date",
		"2. März 2024",
		"23. Juni 2024",
		"32.13.2021",
		"00.00.2021",
		"March 32 2024",
		"..."
	];

	it.each(invalid)("%s should be null", (input) => {
		expect(parse(input)).toBeNull();
	});
});

describe("dayjs.parse() with a pattern", () => {
	// the pattern doesn't restrict what is accepted, it only decides
	// which candidates are tried first, so that an ambiguous input
	// follows the field order the user actually sees
	const data: [string, string, string | null][] = [
		["MM/DD/YYYY", "3/2", "2022-03-02"],
		["DD.MM.YYYY", "3.2", "2022-02-03"],
		["YYYY-MM-DD", "3-2", "2022-03-02"],
		["MM/DD/YYYY", "3/2/17", "2017-03-02"],
		["DD.MM.YYYY", "3.2.17", "2017-02-03"],
		["YYYY-MM-DD", "17-3-2", "2017-03-02"],
		// a year-first pattern reads a leading short year as a year,
		// every other pattern reads it as a day
		["YYYY-MM-DD", "17-3", "2017-03-01"],
		["DD.MM.YYYY", "17-3", "2022-03-17"],
		["", "17-3", "2022-03-17"],
		// but a single two-digit number stays a day everywhere, it is
		// far more likely one than a year
		["YYYY-MM-DD", "05", "2022-01-05"],
		["YYYY-MM-DD", "17", "2022-01-17"],
		// without a usable pattern the day-first order still wins
		["", "3/2", "2022-02-03"],
		["", "3/2/17", "2017-02-03"],
		["foo", "3/2", "2022-02-03"],
		// unambiguous input is unaffected by the pattern
		["MM/DD/YYYY", "12/31/2021", "2021-12-31"],
		["DD.MM.YYYY", "12/31/2021", "2021-12-31"],
		["MM/DD/YYYY", "2021-03-05", "2021-03-05"],
		// input that no candidate can read stays null
		["MM/DD/YYYY", "32/13/2021", null],
		// a pattern without a day never reads a day: the field
		// would only display the units around it and drop what
		// was actually typed
		["MM-YYYY", "3", "2022-03-01"],
		["MM-YYYY", "03", "2022-03-01"],
		["MM-YYYY", "13", null],
		["MM-YYYY", "24", null],
		["MMMM YYYY", "3", "2022-03-01"],
		["MMMM YYYY", "24", null],
		// but a pattern with one still reads a single number as
		// a day, even where a month would be possible as well
		["DD.MM.YYYY", "3", "2022-01-03"],
		["MM/DD/YYYY", "3", "2022-01-03"],
		// separator-less candidates stay available
		["DD.MM.YYYY", "02032024", "2024-03-02"],
		["YYYY-MM-DD", "20210305", "2021-03-05"]
	];

	it.each(data)("%s: %s", (pattern, input, expected) => {
		expect(parse(input, pattern)?.toISO("date") ?? null).toBe(expected);
	});

	it("matches the pattern before it guesses", () => {
		const dt = parse("23.06.2024", "DD.MM.YYYY");
		expect(dt?.toISO("date")).toBe("2024-06-23");
	});

	it("guesses input the pattern cannot read", () => {
		// fewer tokens than the pattern has parts, so the exact
		// match rejects it and the guess takes over
		const dt = parse("3.2", "DD.MM.YYYY");
		expect(dt?.toISO("date")).toBe("2022-02-03");
	});

	it("reads a time pattern as a time", () => {
		// the pattern knows it is a time, so the caller
		// does not have to say so
		expect(parse("5:12", "HH:mm")?.toISO("time")).toBe("05:12:00");
		expect(parse("5:12 pm", "h:mm A")?.toISO("time")).toBe("17:12:00");
		// only a time candidate reads it as 12:34
		expect(parse("1234", "HH:mm")?.toISO("time")).toBe("12:34:00");
	});

	it("returns null for input neither of them reads", () => {
		expect(parse("nonsense", "DD.MM.YYYY")).toBe(null);
		expect(parse("", "DD.MM.YYYY")).toBe(null);
	});

	it("returns null for a pattern that carries no datetime unit", () => {
		// the meridiem only shifts an hour,
		// it never becomes a datetime of its own
		expect(dayjs.parse("pm", { pattern: "a", strict: true })).toBe(null);
		expect(parse("pm", "a")).toBe(null);
	});

	it("returns null for a pattern with an unsupported unit", () => {
		// units outside the supported ones, a weekday for example,
		// are not read back into a datetime: rather than guess which
		// token belongs to one, the exact match drops the pattern
		const pattern = "dddd, DD.MM.YYYY";

		expect(dayjs.parse("Sunday, 23.06.2024", { pattern, strict: true })).toBe(
			null
		);
		expect(parse("Sunday, 23.06.2024", pattern)).toBe(null);
		// the guess still reads the units it does support
		expect(parse("23.06.2024", pattern)?.toISO("date")).toBe("2024-06-23");
	});
});

describe("dayjs.parse() with a type", () => {
	const expected: Record<string, string> = {
		"12:22:37": "12:22:37",
		"12:22": "12:22:00",
		"1:06": "01:06:00",
		"9:12:20": "09:12:20",
		12: "12:00:00",
		9: "09:00:00",
		"10:22:33 pm": "22:22:33",
		"10:22 am": "10:22:00",
		"5:00 am": "05:00:00",
		"5:12 pm": "17:12:00",
		"5 am": "05:00:00",
		"5 pm": "17:00:00",
		1111: "11:11:00",
		1234: "12:34:00",
		// a meridiem may be abbreviated, and input without a
		// separator is sliced by the widths of the units
		"5:12 p": "17:12:00",
		"1222 pm": "12:22:00",
		122237: "12:22:37"
	};

	it.each(Object.entries(expected))("%s should be %s", (input, value) => {
		const dt = dayjs.parse(input, { type: "time" });
		expect(dt?.toISO("time") ?? null).toBe(value);
	});

	const invalid = ["1290", "25:61"];

	it.each(invalid)("%s should be null", (input) => {
		expect(dayjs.parse(input, { type: "time" })).toBeNull();
	});

	it("reads a bare number as a day by default", () => {
		expect(parse("12")?.toISO("date")).toBe("2022-01-12");
	});

	it("takes the type from the pattern when not provided", () => {
		expect(parse("12", "HH:mm")?.toISO("time")).toBe("12:00:00");
	});

	it("wins over the type the pattern implies", () => {
		const dt = dayjs.parse("12", { pattern: "DD.MM.YYYY", type: "time" });
		expect(dt?.toISO("time")).toBe("12:00:00");
	});
});

describe("dayjs.parse() with strict", () => {
	it("returns null for empty input", () => {
		expect(dayjs.parse("", { pattern: "YYYY-MM-DD", strict: true })).toBe(null);
	});

	it("does not fall back to guessing", () => {
		expect(dayjs.parse("3.2", { pattern: "DD.MM.YYYY", strict: true })).toBe(
			null
		);
	});

	describe("date", () => {
		const data: [string, string, string | null][] = [
			["MM-DD-YYYY", "06-26-2025", "2025-06-26"],
			// separators and widths of the input don't matter
			["MM-DD-YYYY", "6/26/2025", "2025-06-26"],
			["MM/YYYY", "01-2024", "2024-01-01"],
			["MM/YYYY", "1 2024", "2024-01-01"],
			["DD.MM.YYYY", "31.12.2021", "2021-12-31"],
			["DD.MM.YYYY", "31.13.2021", null],
			["DD.MM.YYYY", "00.01.2021", null],
			// not a leap year
			["DD.MM.YYYY", "29.02.2021", null],
			["DD.MM.YYYY", "29.02.2020", "2020-02-29"],
			["DD.MM.YY", "02.03.21", "2021-03-02"],
			["DD.MM.YY", "02.03.99", "1999-03-02"],
			["MMMM D, YYYY", "March 2, 2024", "2024-03-02"],
			["MMMM D, YYYY", "March 32, 2024", null],
			["YYYY-MM-DD", "2024-March-02", "2024-03-02"],
			["MMMM D, YYYY", "3 2 2024", "2024-03-02"],
			["YYYY-MM-DD", "2024-Foo-02", null],
			// missing units are filled in
			["DD", "05", "2022-01-05"],
			// input without any separator is sliced by the widths the
			// pattern declares, so that a date can be typed in one go
			["DD.MM.YYYY", "02032024", "2024-03-02"],
			["YYYY-MM-DD", "20210305", "2021-03-05"],
			["DD.MM.YY", "020324", "2024-03-02"],
			["DD.MM.YYYY", "31132021", null],
			// but only when it adds up to the full pattern
			["DD.MM.YYYY", "0203", null],
			["D.M.YYYY", "02032024", null],
			// token count has to match
			["MM-DD-YYYY", "06-26", null],
			["MM-DD-YYYY", "06-26-2025-01", null],
			// literal letters of the pattern separate tokens as well
			["YYYY年M月D日", "2024年6月23日", "2024-06-23"],
			["YYYY年M月D日", "2024年13月23日", null]
		];

		it.each(data)("%s: %s", (pattern, input, expected) => {
			const dt = dayjs.parse(input, { pattern, strict: true });
			expect(dt?.toISO("date") ?? null).toBe(expected);
		});
	});

	describe("year", () => {
		const data: [string, string, string | null][] = [
			// both widths are accepted for both tokens, so that the
			// field order of the pattern is never lost to the guess
			["DD.MM.YYYY", "3.2.2017", "2017-02-03"],
			["DD.MM.YYYY", "3.2.17", "2017-02-03"],
			["DD.MM.YY", "3.2.17", "2017-02-03"],
			["DD.MM.YY", "3.2.2017", "2017-02-03"],
			// a month-first pattern stays month-first with a short year
			["MM/DD/YYYY", "3/2/2017", "2017-03-02"],
			["MM/DD/YYYY", "3/2/17", "2017-03-02"],
			// two-digit years follow dayjs' rule
			["DD.MM.YYYY", "3.2.68", "2068-02-03"],
			["DD.MM.YYYY", "3.2.69", "1969-02-03"],
			// a single or three digits are only ever a half-typed year
			["DD.MM.YYYY", "3.2.7", null],
			["DD.MM.YY", "3.2.7", null],
			["DD.MM.YYYY", "3.2.201", null],
			["DD.MM.YY", "3.2.201", null],
			// years below 1000 still have to be padded to four digits
			["DD.MM.YYYY", "3.2.0999", "0999-02-03"],
			["DD.MM.YYYY", "3.2.999", null],
			// non-numeric input is rejected
			["DD.MM.YYYY", "3.2.20a7", null]
		];

		it.each(data)("%s: %s", (pattern, input, expected) => {
			const dt = dayjs.parse(input, { pattern, strict: true });
			expect(dt?.toISO("date") ?? null).toBe(expected);
		});
	});

	describe("time", () => {
		const data: [string, string, string | null][] = [
			["HH:mm", "5:12", "05:12:00"],
			["HH:mm", "12:90", null],
			["HH:mm", "25:12", null],
			// token count has to match
			["HH:mm", "1290", null],
			["h:mm a", "5:12 pm", "17:12:00"],
			["h:mm a", "12:00 am", "00:00:00"],
			["h:mm a", "12:00 pm", "12:00:00"],
			// only a/am and p/pm are a meridiem
			["h:mm a", "5:12 xx", null],
			["h:mm a", "5:12 4", null],
			// hours are limited to 1-12 with a meridiem
			["h:mm a", "17:12 pm", null],
			["HH:mm:ss", "23:59:59", "23:59:59"]
		];

		it.each(data)("%s: %s", (pattern, input, expected) => {
			const dt = dayjs.parse(input, { pattern, strict: true });
			expect(dt?.toISO("time") ?? null).toBe(expected);
		});
	});
});

describe("month names", () => {
	afterEach(() => {
		dayjs.locale("en");
	});

	describe("english", () => {
		const full: [string, number][] = [
			["January", 1],
			["February", 2],
			["March", 3],
			["April", 4],
			["May", 5],
			["June", 6],
			["July", 7],
			["August", 8],
			["September", 9],
			["October", 10],
			["November", 11],
			["December", 12]
		];

		it.each(full)("resolves the full name %s", (token, month) => {
			expect(toMonth(token)).toBe(month);
		});

		const short: [string, number][] = [
			["Jan", 1],
			["Feb", 2],
			["Mar", 3],
			["Sep", 9],
			["Dec", 12]
		];

		it.each(short)("resolves the short name %s", (token, month) => {
			expect(toMonth(token)).toBe(month);
		});

		// the token comes from user input, so neither the casing nor
		// the trailing dot of an abbreviation may decide the outcome
		const normalized: [string, number][] = [
			["march", 3],
			["MARCH", 3],
			["mArCh", 3],
			["Mar.", 3],
			["mar.", 3]
		];

		it.each(normalized)("normalizes %s", (token, month) => {
			expect(toMonth(token)).toBe(month);
		});
	});

	describe("casing", () => {
		// dayjs matches `MMM` and `MMMM` case-sensitively,
		// `parse()` must not: the names come from user input
		const expected: Record<string, string> = {
			"march 2024": "2024-03-01",
			"MARCH 2024": "2024-03-01",
			"mArCh 2, 2024": "2024-03-02",
			"2 march 2024": "2024-03-02",
			"mar 2024": "2024-03-01",
			"aug 5, 2021": "2021-08-05",
			"1. dec 2021": "2021-12-01",
			december: "2022-12-01"
		};

		it.each(Object.entries(expected))("%s should be %s", (input, value) => {
			expect(parse(input)?.toISO("date") ?? null).toBe(value);
		});

		it("reads the names of the active locale in any case", () => {
			locale("casing", { months: nato });

			expect(parse("charlie 2024")?.toISO("date")).toBe("2024-03-01");
			expect(parse("CHARLIE 2024")?.toISO("date")).toBe("2024-03-01");
			// including the short names derived from the full ones
			expect(parse("cha 2024")?.toISO("date")).toBe("2024-03-01");
		});

		it("reads names with non-ASCII letters in any case", () => {
			locale("nonascii", { months: ["März", ...nato.slice(1)] });

			expect(parse("März 2024")?.toISO("date")).toBe("2024-01-01");
			expect(parse("märz 2024")?.toISO("date")).toBe("2024-01-01");
			expect(parse("MÄRZ 2024")?.toISO("date")).toBe("2024-01-01");
		});
	});

	describe("locale precedence", () => {
		it("prefers the active locale over english", () => {
			locale("precedence", { months: [...nato.slice(1), "January"] });

			// the active locale reads `January` as the twelfth month,
			// which has to win over the english first month
			expect(toMonth("January")).toBe(12);
		});
	});

	describe("invalid input", () => {
		const tokens = ["", "Foo", "Fooo", "13", "-", "."];

		it.each(tokens)("returns null for %s", (token) => {
			expect(toMonth(token)).toBe(null);
		});
	});

	describe("locale data", () => {
		it("returns null for a locale without month names", () => {
			locale("empty", {});
			expect(toMonth("Alpha")).toBe(null);
		});

		it("returns null for declined names without any form", () => {
			locale("formless", { months: () => "" });
			expect(toMonth("Alpha")).toBe(null);
		});

		it("reads both forms of declined names", () => {
			// locales like Russian ship the standalone and the format
			// names as two lists on the function that picks between them
			locale("declined", {
				months: Object.assign(() => "", {
					s: nato,
					f: nato.map((name) => name + "a")
				})
			});

			expect(toMonth("Charlie")).toBe(3);
			expect(toMonth("Charliea")).toBe(3);
		});

		it("uses the short names the locale ships", () => {
			locale("shorts", {
				months: nato,
				monthsShort: nato.map((name) => name.slice(0, 2))
			});

			expect(toMonth("Charlie")).toBe(3);
			expect(toMonth("Ch")).toBe(3);
			// the three-letter names are only derived where
			// the locale does not provide its own
			expect(toMonth("Cha")).toBe(null);
		});

		it("derives short names when the locale ships none", () => {
			locale("noshort", { months: nato });

			expect(toMonth("Charlie")).toBe(3);
			// dayjs formats `MMM` as the first three characters
			// of the full name for these locales
			expect(toMonth("Cha")).toBe(3);
		});

		it("keeps the first month a duplicate name resolves to", () => {
			locale("dupes", { months: ["Same", "Same", ...nato.slice(2)] });

			expect(toMonth("Same")).toBe(1);
		});
	});
});
