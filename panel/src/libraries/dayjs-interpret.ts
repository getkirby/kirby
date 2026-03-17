import type { Dayjs, PluginFunc } from "dayjs";

declare module "dayjs" {
	function interpret(input: string, format?: "date" | "time"): Dayjs | null;
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Object.assign(dayjs, {
		interpret(input: string, format: "date" | "time" = "date"): Dayjs | null {
			const variations: Record<"date" | "time", Record<string, boolean>> = {
				date: {
					"YYYY-MM-DD": true,
					"YYYY-MM-D": true,
					"YYYY-MM-": true,
					"YYYY-MM": true,
					"YYYY-M-DD": true,
					"YYYY-M-D": true,
					"YYYY-M-": true,
					"YYYY-M": true,
					"YYYY-": true,
					YYYYMMDD: true,

					"MMM DD YYYY": false,
					"MMM D YYYY": false,
					"MMM DD YY": false,
					"MMM D YY": false,
					"MMM YYYY": true,
					"MMM DD": false,
					"MMM D": false,
					"MM YYYY": true,
					"M YYYY": true,
					"MMMM DD YYYY": true,
					"MMMM D YYYY": true,
					"MMMM DD YY": true,
					"MMMM D YY": true,
					"MMMM DD, YYYY": true,
					"MMMM D, YYYY": true,
					"MMMM DD, YY": true,
					"MMMM D, YY": true,
					"MMMM DD. YYYY": true,
					"MMMM D. YYYY": true,
					"MMMM DD. YY": true,
					"MMMM D. YY": true,

					DDMMYYYY: true,
					DDMMYY: true,
					"DD MMMM YYYY": false,
					"DD MMMM YY": false,
					"DD MMMM": false,
					"D MMMM YYYY": false,
					"D MMMM YY": false,
					"D MMMM": false,

					"DD MMM YYYY": false,
					"D MMM YYYY": false,
					"DD MMM YY": false,
					"D MMM YY": false,
					"DD MMM": false,
					"D MMM": false,

					"DD MM YYYY": false,
					"DD M YYYY": false,
					"D MM YYYY": false,
					"D M YYYY": false,
					"DD MM YY": false,
					"D MM YY": false,
					"DD M YY": false,
					"D M YY": false,

					YYYY: true,
					MMMM: true,
					MMM: true,
					"DD MM": false,
					"DD M": false,
					"D MM": false,
					"D M": false,
					DD: false,
					D: false
				},
				time: {
					"HHmmss a": false,
					"HHmm a": false,
					"HH a": false,
					HHmmss: false,
					HHmm: false,
					"HH:mm:ss a": false,
					"HH:mm:ss": false,
					"HH:mm a": false,
					"HH:mm": false,
					HH: false
				}
			};

			if (typeof input === "string" && input !== "") {
				for (const variation in variations[format]) {
					const dt = dayjs(input, variation, variations[format][variation]);

					if (dt.isValid() === true) {
						return dt;
					}
				}
			}

			return null;
		}
	});
};

export default plugin;
