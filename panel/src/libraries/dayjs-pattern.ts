import type { Dayjs, PluginFunc } from "dayjs";

type PatternUnit =
	| "year"
	| "month"
	| "day"
	| "hour"
	| "minute"
	| "second"
	| "meridiem";

interface PatternPart {
	index: number;
	start: number;
	end: number;
	unit?: PatternUnit;
}

class DayjsPattern {
	pattern: string;
	parts: PatternPart[];

	constructor(pattern: string) {
		this.pattern = pattern;

		// unit-tokens map
		const map: Record<PatternUnit, string[]> = {
			year: ["YY", "YYYY"],
			month: ["M", "MM", "MMM", "MMMM"],
			day: ["D", "DD"],
			hour: ["h", "hh", "H", "HH"],
			minute: ["m", "mm"],
			second: ["s", "ss"],
			meridiem: ["a"]
		};

		// get array of parts
		this.parts = this.pattern.split(/\W/).map((part, index) => {
			const start = this.pattern.indexOf(part);
			const key = Object.values(map).findIndex((tokens) =>
				tokens.includes(part)
			);
			const units = Object.keys(map) as PatternUnit[];

			return {
				index,
				unit: units[key],
				start,
				end: start + (part.length - 1)
			};
		});
	}

	/**
	 * Returns information about part at
	 * provided selection/indexes
	 */
	at(start: number, end: number = start): PatternPart | undefined {
		const matches = this.parts.filter(
			(part) => part.start <= start && part.end >= end - 1
		);

		// exact selection found
		if (matches[0]) {
			return matches[0];
		}

		// fallback to part where selection starts
		return this.parts.filter((part) => part.start <= start).pop();
	}

	/**
	 * Returns a string for the dayjs object
	 * in the format of the pattern.
	 */
	format(dt?: Dayjs | null): string | null {
		if (!dt || dt.isValid() === false) {
			return null;
		}

		return dt.format(this.pattern);
	}
}

declare module "dayjs" {
	function pattern(pattern: string): DayjsPattern;
}

export type { DayjsPattern, PatternPart, PatternUnit };

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Object.assign(dayjs, {
		pattern(pattern: string): DayjsPattern {
			return new DayjsPattern(pattern);
		}
	});
};

export default plugin;
