/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { Dayjs, PluginFunc } from "dayjs";

export type Meridiem = (
	hour: number,
	minute: number,
	isLowercase: boolean
) => string;

export interface Months {
	(dt: Dayjs, pattern: string): string;
	f: string[];
	s: string[];
}

export interface Locale {
	meridiem?: Meridiem;
	months?: Months | string[];
	monthsShort?: Months | string[];
	name: string;
	ordinal?: (n: number) => number | string;
	weekdays?: string[];
	weekdaysMin?: string[];
	weekdaysShort?: string[];
}

/**
 * Matching strings that ask for the declined month name
 */
const declined = /D[oD]?(\[[^[\]]*\]|[^A-Za-z])+MMMM?/;

/**
 * Kirby appends the alphabet a language is written in with an
 * `@`, e.g. `sr@latin`, where the browser expects it as part of
 * the name instead: `sr-Latn`.
 */
const scripts: Record<string, string> = {
	cyrillic: "-Cyrl",
	latin: "-Latn"
};

function formatter(
	name: string,
	options: Intl.DateTimeFormatOptions
): Intl.DateTimeFormat {
	return new Intl.DateTimeFormat(name, {
		// Options are pinned: `fa` would otherwise format
		// in the Persian calendar and in Persian digits
		calendar: "gregory",
		numberingSystem: "latn",
		...options
	});
}

function locale(name: string): Locale {
	return {
		meridiem: meridiem(name),
		months: months(name, "long"),
		monthsShort: months(name, "short"),
		name: name.toLowerCase(),
		weekdays: weekdays(name, "long"),
		weekdaysMin: weekdays(name, "narrow"),
		weekdaysShort: weekdays(name, "short")
	};
}

/**
 * Returns day periods of a locale, e.g. `午前`/`午後` for `ja`
 */
function meridiem(name: string): Meridiem {
	const format = formatter(name, { hour: "numeric", hour12: true });
	const periods = [0, 12].map(
		(hour) => part(format, new Date(2000, 0, 1, hour), "dayPeriod") ?? ""
	);

	function period(hour: number, minute: number, isLowercase: boolean): string {
		const value = periods[hour < 12 ? 0 : 1];
		return isLowercase === true ? value.toLowerCase() : value;
	}

	return period;
}

/**
 * Returns twelve month names of a locale, as the callable dayjs
 * expects for declining languages: it answers with the form a
 * format string asks for, keeping the standalone names on `.s`
 * and the ones a full date uses on `.f`
 */
function months(name: string, width: "long" | "short"): Months {
	// CJK writes a month as a numeral plus a suffix, which
	// `formatToParts()` splits in two. Asking for the month on
	// its own keeps `1月` in one piece.
	const alone = formatter(name, { month: width });

	function read(inDate: boolean): string[] {
		const format = formatter(name, {
			day: inDate === true ? "numeric" : undefined,
			month: width,
			year: "numeric"
		});

		return [...Array(12).keys()].map((month) => {
			const date = new Date(2000, month, 1);
			const value = part(format, date, "month");

			return value === undefined || /^\d+$/.test(value) === true
				? alone.format(date)
				: value;
		});
	}

	const s = read(false);

	// Catalan prepends a preposition inside a full date
	// (`de gener`), which is not part of the month name.
	// A declined form never merely prepends to the nominative.
	const f = read(true).map((month, index) =>
		month.endsWith(s[index]) === true ? s[index] : month
	);

	function pick(dt: Dayjs, pattern: string): string {
		return declined.test(pattern) === true ? f[dt.month()] : s[dt.month()];
	}

	pick.s = s;
	pick.f = f;

	return pick;
}

function part(
	format: Intl.DateTimeFormat,
	date: Date,
	type: string
): string | undefined {
	return format.formatToParts(date).find((entry) => entry.type === type)?.value;
}

/**
 * Matches a Kirby translation code with a locale name the
 * browser has data for, e.g. `pt_BR` to `pt-BR`
 * or `sr@latin` to `sr-Latn`.
 */
export function toName(code: string): string {
	if (typeof code !== "string") {
		return "en";
	}

	const name = code
		.replace(/@(\w+)$/, (match, script) => scripts[script.toLowerCase()] ?? "")
		.replace(/_/g, "-");

	// full name first, then base language
	for (const candidate of [name, name.split("-")[0]]) {
		try {
			if (Intl.DateTimeFormat.supportedLocalesOf(candidate).length > 0) {
				return new Intl.DateTimeFormat(candidate).resolvedOptions().locale;
			}
		} catch {
			continue;
		}
	}

	return "en";
}

/**
 * Returns seven weekday names of a locale
 */
function weekdays(name: string, width: "long" | "short" | "narrow"): string[] {
	const format = formatter(name, { weekday: width });

	// January 2, 2000 was a Sunday
	return [...Array(7).keys()].map((day) =>
		format.format(new Date(2000, 0, 2 + day))
	);
}

declare module "dayjs" {
	/**
	 * Activates a locale by Kirby translation code, e.g. `pt_BR`
	 */
	function locale(code: string): string;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	const parent = dayjs.locale;

	function has(name: string): boolean {
		return dayjs.Ls[name.toLowerCase()] !== undefined;
	}

	function register(name: string, data: Partial<Locale>): void {
		parent(name, data as unknown as Partial<ILocale>, true);
	}

	function activate(
		preset?: string | ILocale,
		object?: Partial<ILocale>,
		isLocal?: boolean
	): string {
		// reading active locale or registering by hand stays untouched
		if (typeof preset !== "string" || preset === "" || object) {
			return parent(preset, object, isLocal);
		}

		const name = has(preset) === true ? preset : toName(preset);

		if (has(name) === false) {
			register(name, locale(name));
		}

		return parent(name, undefined, isLocal);
	}

	Object.assign(dayjs, { locale: activate });

	register("en", { ...dayjs.Ls.en, ...locale("en") });
};

export default plugin;
