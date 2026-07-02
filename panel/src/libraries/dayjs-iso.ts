import type { Dayjs, PluginFunc } from "dayjs";

export type ISOFormat = "date" | "time" | "datetime";

const dayjsISOformat = (format: string): string => {
	if (format === "date") {
		return "YYYY-MM-DD";
	}

	if (format === "time") {
		return "HH:mm:ss";
	}

	return "YYYY-MM-DD HH:mm:ss";
};

declare module "dayjs" {
	interface Dayjs {
		toISO(format?: ISOFormat): string;
	}
	function iso(string: string, format?: ISOFormat): Dayjs | null;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Dayjs.prototype.toISO = function (
		this: Dayjs,
		format: ISOFormat = "datetime"
	): string {
		return this.format(dayjsISOformat(format));
	};

	Object.assign(dayjs, {
		iso(string: string, format?: ISOFormat): Dayjs | null {
			let fmt: string | string[] | undefined = format
				? dayjsISOformat(format)
				: undefined;

			// if no format is provided, try to parse any of the three ISO formats
			fmt ??= [
				dayjsISOformat("datetime"),
				dayjsISOformat("date"),
				dayjsISOformat("time")
			];

			const dt = dayjs(string, fmt);

			if (!dt || dt.isValid() === false) {
				return null;
			}

			return dt;
		}
	});
};

export default plugin;
