import { afterEach, describe, expect, it } from "vitest";
import dayjs from "./dayjs";
import { toName } from "./dayjs-locale";

afterEach(() => {
	dayjs.locale("en");
});

describe("dayjs.locale()", () => {
	it("builds and activates a locale", () => {
		expect(dayjs.locale("de")).toStrictEqual("de");
		expect(dayjs.locale()).toStrictEqual("de");
		expect(dayjs.Ls.de).toBeDefined();
		expect(dayjs("2024-03-01").format("MMMM")).toStrictEqual("März");
	});

	it("maps a Kirby code with a region to a locale", () => {
		expect(dayjs.locale("pt_BR")).toStrictEqual("pt-br");
		expect(dayjs.locale()).toStrictEqual("pt-br");
	});

	it("keeps a region the browser has data for", () => {
		expect(dayjs.locale("es_419")).toStrictEqual("es-419");
		expect(dayjs.locale("is_IS")).toStrictEqual("is-is");
	});

	it("keeps the script of a Kirby code", () => {
		expect(dayjs.locale("sr@latin")).toStrictEqual("sr-latn");
		expect(dayjs("2024-06-01").format("MMMM")).toStrictEqual("jun");
	});

	it("stays on English for an unknown language", () => {
		expect(dayjs.locale("xx")).toStrictEqual("en");
		expect(dayjs.locale()).toStrictEqual("en");
	});

	it("reads the active locale without a code", () => {
		dayjs.locale("de");
		expect(dayjs.locale()).toStrictEqual("de");
		expect(dayjs.locale(undefined)).toStrictEqual("de");
		expect(dayjs.locale("")).toStrictEqual("de");
	});

	it("still registers a locale object by hand", () => {
		dayjs.locale("test", { name: "test", months: Array(12).fill("Test") });
		expect(dayjs.locale()).toStrictEqual("test");
		expect(dayjs("2024-06-01").format("MMMM")).toStrictEqual("Test");
	});

	it("localizes formatting", () => {
		dayjs.locale("de");
		expect(dayjs("2024-06-01").format("MMMM")).toStrictEqual("Juni");
	});

	it("keeps a CJK month name in one piece", () => {
		dayjs.locale("ja");
		expect(dayjs("2024-06-01").format("MMMM")).toStrictEqual("6月");
	});

	it("formats Persian in the Gregorian calendar", () => {
		dayjs.locale("fa");
		expect(dayjs("2024-01-01").format("MMMM")).toStrictEqual("ژانویهٔ");
		expect(dayjs("2024-01-01").format("D")).toStrictEqual("1");
	});

	it("localizes the day period", () => {
		dayjs.locale("ja");
		expect(dayjs("2024-06-01 15:00").format("A")).toStrictEqual("午後");
	});

	it("maps every weekday width", () => {
		dayjs.locale("en");

		const dt = dayjs("2024-06-03");

		expect(dt.format("dd")).toStrictEqual("M");
		expect(dt.format("ddd")).toStrictEqual("Mon");
		expect(dt.format("dddd")).toStrictEqual("Monday");
	});
});

describe("dayjs.locale() with a declining language", () => {
	it("declines the month name next to a day", () => {
		dayjs.locale("ru");
		expect(dayjs("2024-01-01").format("MMMM")).toStrictEqual("январь");
		expect(dayjs("2024-01-01").format("D MMMM YYYY")).toStrictEqual(
			"1 января 2024"
		);
	});

	it("declines the month name for Greek", () => {
		dayjs.locale("el");
		expect(dayjs("2024-01-01").format("MMMM")).toStrictEqual("Ιανουάριος");
		expect(dayjs("2024-01-01").format("D MMMM YYYY")).toStrictEqual(
			"1 Ιανουαρίου 2024"
		);
	});

	it("declines the month name for Czech", () => {
		dayjs.locale("cs");
		expect(dayjs("2024-01-01").format("MMMM")).toStrictEqual("leden");
		expect(dayjs("2024-01-01").format("D. MMMM YYYY")).toStrictEqual(
			"1. ledna 2024"
		);
	});
});

describe("dayjs.locale.toName()", () => {
	const cases: [unknown, string][] = [
		["de", "de"],
		["en", "en"],
		["pt_BR", "pt-BR"],
		["pt_PT", "pt-PT"],
		["es_419", "es-419"],
		["es_ES", "es-ES"],
		["is_IS", "is-IS"],
		["sv_SE", "sv-SE"],
		["sr@latin", "sr-Latn"],
		["zh_TW", "zh-TW"],
		["DE", "de"],
		["de_XX", "de"],
		["xx", "en"],
		["", "en"],
		[null, "en"],
		[42, "en"]
	];

	for (const [code, expected] of cases) {
		it(`maps ${JSON.stringify(code)} to ${expected}`, () => {
			expect(toName(code as string)).toStrictEqual(expected);
		});
	}
});
