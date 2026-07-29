/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { Dayjs, PluginFunc } from "dayjs";

export type ISOFormat = "date" | "time" | "datetime";

declare module "dayjs" {
	interface Dayjs {
		/**
		 * Formats the datetime as the ISO string that values
		 * are stored as, e.g. `2024-06-23 14:30:00`
		 *
		 * @param format which part of the datetime to write
		 */
		toISO(format?: ISOFormat): string;
	}

	/**
	 * Parses from an ISO string
	 *
	 * @param string ISO string to parse
	 * @param format which datetime format to expect
	 */
	function iso(string: string, format?: ISOFormat): Dayjs | null;
}

const formats: Record<ISOFormat, string> = {
	date: "YYYY-MM-DD",
	time: "HH:mm:ss",
	datetime: "YYYY-MM-DD HH:mm:ss"
};

/**
 * Returns the dayjs pattern for an ISO format
 */
function pattern(format: ISOFormat): string {
	return formats[format] ?? formats.datetime;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Dayjs.prototype.toISO = function (
		this: Dayjs,
		format: ISOFormat = "datetime"
	): string {
		return this.format(pattern(format));
	};

	Object.assign(dayjs, {
		iso(string: string, format?: ISOFormat): Dayjs | null {
			// if no format is provided, try all of them; strict parsing
			// requires an exact round-trip, so at most one of them can
			// match and the order they are tried in does not matter
			const fmt =
				format !== undefined ? pattern(format) : Object.values(formats);

			// parse strictly: ISO strings are machine-generated, so anything
			// that doesn't match exactly is corrupt and must fail loudly
			// instead of being silently shifted to a different date
			const dt = dayjs(string, fmt, true);

			if (dt.isValid() === false) {
				return null;
			}

			return dt;
		}
	});
};

export default plugin;
