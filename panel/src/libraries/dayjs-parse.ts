/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { Dayjs, PluginFunc } from "dayjs";
import type { DatetimeType, DayjsFactory } from "./dayjs";
import type { Locale } from "./dayjs-locale";
import {
	DayjsPattern,
	type PatternPart,
	type PatternUnit
} from "./dayjs-pattern";

export interface ParseOptions {
	pattern?: string;
	strict?: boolean;
	type?: DatetimeType;
}

/**
 * The units a datetime is assembled from.
 * The meridiem is excluded as it only shifts the hour,
 * never becomes a value of its own.
 */
type DatetimeUnit = Exclude<PatternUnit, "meridiem">;

const hierarchy: DatetimeUnit[] = [
	"year",
	"month",
	"day",
	"hour",
	"minute",
	"second"
];

/**
 * What a unit falls back to when the pattern does not provide it
 */
const minimum: Record<DatetimeUnit, number> = {
	year: 1970,
	month: 1,
	day: 1,
	hour: 0,
	minute: 0,
	second: 0
};

/**
 * Everything between the tokens:
 * what input is split on and what `normalize()` strips.
 */
const separators = new RegExp(`[^\\p{L}\\p{N}]+`, "gu");

/**
 * The unit orders a guess tries the input against.
 * Parsing takes care of separators, number widths etc.,
 * so a variation only has to say which unit comes first.
 */
const variations = {
	date: [
		"DD MM YYYY",
		"MM DD YYYY",
		"DD MM",
		"MM DD",
		"MMMM YYYY",
		"DD",
		"MMMM",
		"YYYY",
		"YYYY MM DD",
		"YYYY MM"
	].map(DayjsPattern.from),
	time: ["hh mm ss a", "HH mm ss", "hh mm a", "HH mm", "hh a", "HH"].map(
		DayjsPattern.from
	)
};

/**
 * Whether a variation reads the units it shares
 * with the source pattern in the same order
 */
function aligns(units: PatternUnit[], target: PatternUnit[]): boolean {
	const a = units.filter((unit) => target.includes(unit) === true);
	const b = target.filter((unit) => units.includes(unit) === true);
	return a.length > 0 && a.join(" ") === b.join(" ");
}

/**
 * The patterns a guess tries, informed by the source pattern.
 * Strict parsing already handles everything that matches the
 * pattern exactly, and guessing beyond that is unavoidable,
 * but it should still follow the pattern's order.
 */
function candidates(type: DatetimeType, target: PatternUnit[]): DayjsPattern[] {
	const list = type === "time" ? variations.time : variations.date;

	if (target.length === 0) {
		return list;
	}

	// a guess may only read units that the pattern also shows
	const usable = list.filter((variation) =>
		variation.units.some((unit) => target.includes(unit) === true)
	);

	if (usable.length === 0) {
		return list;
	}

	// the patterns aligning with the source pattern come first
	return [
		...usable.filter((variation) => aligns(variation.units, target) === true),
		...usable.filter((variation) => aligns(variation.units, target) === false)
	];
}

/**
 * Joins the tokens a day period was split into, so that the
 * spaces and dots many locales do not count as
 * separators, e.g. turn `p. m.` back into a single token
 */
function collapse(tokens: string[], parts: PatternPart[]): string[] {
	const excess = tokens.length - parts.length;

	if (excess <= 0) {
		return tokens;
	}

	const index = parts.findIndex((part) => part.unit === "meridiem");

	if (index === -1) {
		return tokens;
	}

	return [
		...tokens.slice(0, index),
		tokens.slice(index, index + excess + 1).join(""),
		...tokens.slice(index + excess + 1)
	];
}

/**
 * Splits digit runs that were typed without any separator at all,
 * e.g. `02032024` for a `DD.MM.YYYY` source pattern
 */
function expand(tokens: string[], parts: PatternPart[]): string[] {
	if (tokens.length >= parts.length) {
		return tokens;
	}

	const result: string[] = [];
	let index = 0;

	for (const token of tokens) {
		let width = 0;
		let count = 0;

		while (index + count < parts.length && width < token.length) {
			const part = parts[index + count];
			width += part.end - part.start + 1;
			count++;
		}

		if (count > 1 && width === token.length && /^\d+$/.test(token) === true) {
			let offset = 0;

			for (let position = 0; position < count; position++) {
				const part = parts[index + position];
				const size = part.end - part.start + 1;

				result.push(token.slice(offset, offset + size));
				offset += size;
			}

			index += count;
			continue;
		}

		result.push(token);
		index++;
	}

	return result;
}

/**
 * Matches the tokens of the input positionally against the
 * parts of a pattern, which let's us parse independent of the
 * separators and the number widths used in the input.
 */
function match(
	dayjs: DayjsFactory,
	tokens: string[],
	pattern: DayjsPattern
): Dayjs | null {
	const parts = pattern.parts;

	// a token count that misses the pattern is not wrong yet:
	// too few means digits were typed without any separator,
	// too many that a day period carries spaces or dots
	tokens =
		tokens.length < parts.length
			? expand(tokens, parts)
			: collapse(tokens, parts);

	// only complete input matches,
	// partial input is the job of the guess
	if (tokens.length !== parts.length) {
		return null;
	}

	const values = toValues(dayjs, tokens, parts);

	if (values === null) {
		return null;
	}

	return toDatetime(dayjs, values);
}

/**
 * All month names of a locale
 */
function months(data?: Locale): string[] {
	const [full, short] = [data?.months, data?.monthsShort].map((names) =>
		Array.isArray(names) === true
			? names
			: [...(names?.s ?? []), ...(names?.f ?? [])]
	);

	if (full.length === 0) {
		return [];
	}

	if (short.length > 0) {
		return [...full, ...short];
	}

	return [...full, ...full.map((name) => name.slice(0, 3))];
}

/**
 * Reduces a value to its comparable form: lowercased and
 * stripped of everything that does not carry information, so
 * that the abbreviations many locales write with a trailing
 * dot (`Okt.`, `Sept.`) still match a dot-less input token
 */
function normalize(value: string): string {
	return value.normalize("NFC").toLowerCase().replace(separators, "");
}

/**
 * Splits input into the tokens that carry information
 */
function split(input: string, on: RegExp = separators): string[] {
	return input.split(on).filter((token) => token !== "");
}

/**
 * Assembles the parsed values into a datetime object
 */
function toDatetime(
	dayjs: DayjsFactory,
	values: Partial<Record<PatternUnit, number>>
): Dayjs | null {
	// index of the most significant unit provided by the pattern
	const significant = hierarchy.findIndex((unit) => values[unit] !== undefined);

	if (significant === -1) {
		return null;
	}

	const now = dayjs();
	const current: Record<DatetimeUnit, number> = {
		year: now.year(),
		month: now.month() + 1,
		day: now.date(),
		hour: now.hour(),
		minute: now.minute(),
		second: now.second()
	};

	// Filling in what the pattern does not provide:
	// units more significant than the most significant one
	// are taken from now, less significant ones use their minimum.
	for (const [index, unit] of hierarchy.entries()) {
		values[unit] ??= index < significant ? current[unit] : minimum[unit];
	}

	const filled = values as Record<DatetimeUnit, number>;
	const { year, month, day, hour, minute, second } = filled;

	const dt = dayjs(new Date(year, month - 1, day, hour, minute, second));

	// Reject values that rolled over into another
	// month or year, e.g. February 30
	if (dt.year() !== year || dt.month() !== month - 1 || dt.date() !== day) {
		return null;
	}

	return dt;
}

/**
 * Resolves a day period to `0` (am) or `1` (pm)
 */
function toMeridiem(dayjs: DayjsFactory, token: string): number | null {
	const needle = normalize(token);
	const locale = dayjs.Ls[dayjs.locale()] as Locale;
	const meridiem = locale?.meridiem;

	if (typeof meridiem === "function") {
		for (const index of [0, 1]) {
			const periods = [
				meridiem(index * 12, 0, true),
				meridiem(index * 12, 0, false)
			];

			if (periods.some((period) => normalize(period) === needle) === true) {
				return index;
			}
		}
	}

	if (/^(a|am)$/.test(needle) === true) {
		return 0;
	}

	if (/^(p|pm)$/.test(needle) === true) {
		return 1;
	}

	return null;
}

/**
 * Resolves a localized month name (full or short) to its
 * number (1-12), in the active locale first, then English
 */
function toMonth(dayjs: DayjsFactory, token: string): number | null {
	const needle = normalize(token);
	const locales = [dayjs.locale(), "en"];

	for (const locale of locales) {
		const index = months(dayjs.Ls[locale]).findIndex(
			(name) => normalize(name) === needle
		);

		if (index !== -1) {
			return (index % 12) + 1;
		}
	}

	return null;
}

/**
 * Converts a token to an integer inside the provided range.
 */
function toNumber(token: string, min: number, max: number): number | null {
	if (/^\d+$/.test(token) === false) {
		return null;
	}

	const value = parseInt(token, 10);

	if (value < min || value > max) {
		return null;
	}

	return value;
}

/**
 * Builds regex that splits input against a pattern into tokens.
 */
function toSeparators(pattern: DayjsPattern): RegExp {
	const { parts, source } = pattern;
	const covered = new Set<number>();

	for (const part of parts) {
		for (let index = part.start; index <= part.end; index++) {
			covered.add(index);
		}
	}

	const literals = new Set<string>();

	for (let index = 0; index < source.length; index++) {
		if (covered.has(index) === false) {
			literals.add(source[index]);
		}
	}

	if (literals.size === 0) {
		return separators;
	}

	const escaped = [...literals]
		.map((char) => char.replace(/[\\\]^[-]/g, "\\$&"))
		.join("");

	return new RegExp(`(?:[^\\p{L}\\p{N}]|[${escaped}])+`, "u");
}

/**
 * Reads and converts a token as the unit it is representing.
 */
function toValue(
	dayjs: DayjsFactory,
	token: string,
	unit: PatternUnit,
	hasMeridiem: boolean
): number | null {
	switch (unit) {
		case "year":
			return toYear(token);
		case "month":
			return toNumber(token, 1, 12) ?? toMonth(dayjs, token);
		case "day":
			return toNumber(token, 1, 31);
		case "hour":
			return hasMeridiem === true
				? toNumber(token, 1, 12)
				: toNumber(token, 0, 23);
		case "minute":
		case "second":
			return toNumber(token, 0, 59);
		case "meridiem":
			return toMeridiem(dayjs, token);
	}
}

/**
 * Reads a value for every part of the pattern,
 * or null when a token does not fit the unit its part declares.
 */
function toValues(
	dayjs: DayjsFactory,
	tokens: string[],
	parts: PatternPart[]
): Partial<Record<PatternUnit, number>> | null {
	const values: Partial<Record<PatternUnit, number>> = {};
	const hasMeridiem = parts.some((part) => part.unit === "meridiem");

	for (const part of parts) {
		if (part.unit === undefined) {
			return null;
		}

		const value = toValue(dayjs, tokens[part.index], part.unit, hasMeridiem);

		if (value === null) {
			return null;
		}

		values[part.unit] = value;
	}

	// the meridiem is no value of its own,
	// it only shifts the hour it is written next to
	if (values.meridiem !== undefined && values.hour !== undefined) {
		if (values.meridiem === 1 && values.hour < 12) {
			values.hour += 12;
		} else if (values.meridiem === 0 && values.hour === 12) {
			values.hour = 0;
		}
	}

	return values;
}

/**
 * Converts an input token to a year.
 */
function toYear(token: string): number | null {
	if (/^\d{4}$/.test(token) === true) {
		return parseInt(token, 10);
	}

	if (/^\d{2}$/.test(token) === false) {
		return null;
	}

	const value = parseInt(token, 10);
	return value + (value <= 68 ? 2000 : 1900);
}

declare module "dayjs" {
	/**
	 * @deprecated 6.0.0 Use `dayjs.parse()` instead
	 */
	function interpret(
		input: string,
		format?: DatetimeType,
		pattern?: string
	): Dayjs | null;

	/**
	 * Parses input against a display pattern: exactly, and
	 * unless `strict` is set, with a fallback to informed guesses.
	 * @since 6.0.0
	 */
	function parse(input: string, options?: ParseOptions): Dayjs | null;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Object.assign(dayjs, {
		/**
		 * @deprecated 6.0.0
		 */
		interpret(
			input: string,
			format: DatetimeType = "date",
			pattern?: string
		): Dayjs | null {
			return dayjs.parse(input, { pattern, type: format });
		},
		parse(input: string, options: ParseOptions = {}): Dayjs | null {
			if (typeof input !== "string" || input === "") {
				return null;
			}

			const pattern = new DayjsPattern(options.pattern ?? "");

			// what the source pattern prescribes is what was
			// most likely typed, so matching it first against the input
			if (pattern.parts.length > 0) {
				const dt = match(dayjs, split(input, toSeparators(pattern)), pattern);

				if (dt !== null) {
					return dt;
				}
			}

			// everything below is guessing, which strict input rules out
			if (options.strict === true) {
				return null;
			}

			// the guess reads input in ways the strict pattern cannot:
			// partial, in another order or without the pattern's literals.
			// The first order that reads/fits all of it wins.
			const tokens = split(input);
			const type = options.type ?? pattern.type;

			for (const candidate of candidates(type, pattern.units)) {
				// a two-digit number on its own is a day, not a year, so the
				// bare year only reads a year that is written out
				if (
					candidate.source === "YYYY" &&
					/^\d{4}$/.test(tokens[0] ?? "") === false
				) {
					continue;
				}

				const dt = match(dayjs, tokens, candidate);

				if (dt !== null) {
					return dt;
				}
			}

			return null;
		}
	});
};

export default plugin;
