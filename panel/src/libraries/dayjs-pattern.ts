/**
 * Reads a display pattern such as `DD.MM.YYYY`
 * and formats and inspects datetimes against it.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { Dayjs, PluginFunc } from "dayjs";
import type { DatetimeType } from "./dayjs";

/**
 * A single span in the pattern with its position,
 * e.g. the `MM` of `YYYY-MM-DD` as `{ index: 1, start: 5, end: 6 }`.
 */
export interface PatternPart {
	index: number;
	start: number;
	end: number;
	unit?: PatternUnit;
}

/**
 * Date/time unit the pattern refers to, e.g. `YYYY` to `year`
 */
export type PatternUnit =
	"year" | "month" | "day" | "hour" | "minute" | "second" | "meridiem";

/**
 * The unit each supported pattern format refers to
 */
const units: Record<string, PatternUnit> = {
	YY: "year",
	YYYY: "year",
	M: "month",
	MM: "month",
	MMM: "month",
	MMMM: "month",
	D: "day",
	DD: "day",
	h: "hour",
	hh: "hour",
	H: "hour",
	HH: "hour",
	m: "minute",
	mm: "minute",
	s: "second",
	ss: "second",
	a: "meridiem",
	A: "meridiem"
};

/**
 * Matches the supported markers in a format string,
 * longest first, so that scanning always reads the widest marker.
 */
const markers = new RegExp(
	Object.keys(units)
		.sort((a, b) => b.length - a.length)
		.join("|"),
	"g"
);

export class DayjsPattern {
	source: string;

	constructor(source: string) {
		this.source = source;
	}

	at(start: number, end: number = start): PatternPart | undefined {
		// exact selection found
		const match = this.parts.find(
			(part) => part.start <= start && part.end >= end - 1
		);

		// fallback to part where selection starts
		return match ?? this.parts.findLast((part) => part.start <= start);
	}

	format(dt?: Dayjs | null): string | null {
		if (!dt || dt.isValid() === false) {
			return null;
		}

		return dt.format(this.source);
	}

	static from(source: string): DayjsPattern {
		return new DayjsPattern(source);
	}

	/**
	 * The parts of the pattern, scanned by letter sequences
	 */
	get parts(): PatternPart[] {
		return [...this.source.matchAll(/[a-zA-Z]+/g)].map((match, index) => {
			const marker = match[0];

			return {
				index,
				unit: units[marker],
				start: match.index,
				end: match.index + (marker.length - 1)
			};
		});
	}

	/**
	 * Whether the pattern describes a time or a date
	 */
	get type(): DatetimeType {
		const calendar: PatternUnit[] = ["year", "month", "day"];

		if (this.units.length === 0) {
			return "date";
		}

		return this.units.some((unit) => calendar.includes(unit) === true)
			? "date"
			: "time";
	}

	/**
	 * The units the pattern is made up of, in the order they
	 * appear in it, e.g. `MM/DD/YYYY` as `["month", "day", "year"]`.
	 */
	get units(): PatternUnit[] {
		return [...this.source.matchAll(markers)].map((match) => units[match[0]]);
	}
}

declare module "dayjs" {
	/**
	 * Reads a display pattern such as `DD.MM.YYYY`
	 * and formats and inspects datetimes against it
	 */
	function pattern(pattern: string): DayjsPattern;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Object.assign(dayjs, {
		pattern(source: string): DayjsPattern {
			return new DayjsPattern(source);
		}
	});
};

export default plugin;
