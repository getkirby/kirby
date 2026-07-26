/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { Dayjs, PluginFunc } from "dayjs";
import type { DatetimeType } from "./dayjs";

type ISOFormat = { regex: RegExp; pattern: string };

const formats: Record<DatetimeType, ISOFormat> = {
	date: {
		pattern: "YYYY-MM-DD",
		regex: /^(\d{4})-(\d{2})-(\d{2})$/
	},
	time: {
		pattern: "HH:mm:ss",
		regex: /^(\d{2}):(\d{2}):(\d{2})$/
	},
	datetime: {
		pattern: "YYYY-MM-DD HH:mm:ss",
		regex: /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/
	}
};

/**
 * Returns the dayjs pattern for an ISO format
 */
function format(format: DatetimeType): ISOFormat {
	return formats[format] ?? formats.datetime;
}

declare module "dayjs" {
	interface Dayjs {
		/**
		 * Formats the datetime as the ISO string that values
		 * are stored as, e.g. `2024-06-23 14:30:00`
		 *
		 * @param type which part of the datetime to write
		 */
		toISO(type?: DatetimeType): string;
	}

	/**
	 * Parses from an ISO string
	 *
	 * @param string ISO string to parse
	 * @param type which part of the datetime to expect
	 */
	function iso(string: string, type?: DatetimeType): Dayjs | null;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Dayjs.prototype.toISO = function (
		this: Dayjs,
		type: DatetimeType = "datetime"
	): string {
		return this.format(format(type).pattern);
	};

	Object.assign(dayjs, {
		iso(string: string, type?: DatetimeType): Dayjs | null {
			const types = type ? [type] : (Object.keys(formats) as DatetimeType[]);

			for (const dttype of types) {
				const { regex, pattern } = format(dttype);
				const match = regex.exec(string);

				if (match === null) {
					continue;
				}

				const values = match.slice(1).map(Number);

				// a time carries no date of its own
				const [year, month, day, hour = 0, minute = 0, second = 0] =
					dttype === "time" ? [1970, 1, 1, ...values] : values;

				const dt = dayjs(new Date(year, month - 1, day, hour, minute, second));

				// out-of-range values rolled over into another unit,
				// which writing them back out gives away:
				// `2020-02-30` returns as `2020-03-01`
				if (dt.format(pattern) === string) {
					return dt;
				}
			}

			return null;
		}
	});
};

export default plugin;
