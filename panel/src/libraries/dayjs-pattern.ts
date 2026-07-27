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
 * With a datetime, the position is the one in the string that
 * datetime renders into instead.
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

	/**
	 * A missing pattern is treated like an empty one,
	 * so that a display option that isn't set describes
	 * nothing instead of throwing
	 */
	constructor(source?: string | null) {
		this.source = source ?? "";
	}

	at(
		start: number,
		end: number = start,
		dt?: Dayjs | null
	): PatternPart | undefined {
		const parts = this.parts(dt);

		// exact selection found
		const match = parts.find(
			(part) => part.start <= start && part.end >= end - 1
		);

		// fallback to part where selection starts
		return match ?? parts.findLast((part) => part.start <= start);
	}

	format(dt?: Dayjs | null): string | null {
		if (!dt || dt.isValid() === false) {
			return null;
		}

		return dt.format(this.source);
	}

	static from(source?: string | null): DayjsPattern {
		return new DayjsPattern(source);
	}

	/**
	 * The parts of the pattern, scanned by letter sequences.
	 *
	 * Without a datetime, the parts are positioned in the pattern
	 * itself. With one, they are positioned in the string that
	 * datetime renders into, as a marker and what it prints can
	 * differ in width, e.g. `D` printing `13` or `MMMM` printing
	 * `September`.
	 */
	parts(dt?: Dayjs | null): PatternPart[] {
		const value = dt?.isValid() === true ? dt : null;
		const parts: PatternPart[] = [];

		// where the scan stands in the pattern and in the rendering
		let offset = 0;
		let position = 0;

		for (const [index, match] of [
			...this.source.matchAll(/[a-zA-Z]+/g)
		].entries()) {
			const marker = match[0];
			const length =
				value === null ? marker.length : value.format(marker).length;

			// whatever sits between the markers prints as it is
			position += match.index - offset;

			parts.push({
				index,
				unit: units[marker],
				start: position,
				end: position + (length - 1)
			});

			offset = match.index + marker.length;
			position += length;
		}

		return parts;
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
	function pattern(pattern?: string | null): DayjsPattern;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Object.assign(dayjs, {
		pattern(source?: string | null): DayjsPattern {
			return new DayjsPattern(source);
		}
	});
};

export default plugin;
