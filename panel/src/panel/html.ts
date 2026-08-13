import { escapeHTML, StringTemplateValues } from "@/helpers/string";
import { isObject } from "@/helpers/object";

export type HtmlData = {
	[key: string]:
		StringTemplateValues[string] | HtmlString | HtmlData | undefined;
};

/**
 * Wraps and marks a string as trusted, pre-escaped HTML.
 * Used by `v-safe-html` to render as actual HTML.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export class HtmlString extends String {
	constructor(value: string) {
		super(value);
	}

	/**
	 * Recursively walks `data` and rewraps any value
	 * whose parent key matches `<key>` into an `HtmlString`.
	 */
	static resolve<T>(data: T): T {
		if (Array.isArray(data) === true) {
			return data.map((value) => HtmlString.resolve(value)) as T;
		}

		if (isObject(data) === true) {
			const result: Record<string, unknown> = {};
			const rawData = data as Record<string, unknown>;

			for (const rawKey in rawData) {
				const value = rawData[rawKey];

				if (
					rawKey.length > 2 &&
					rawKey.startsWith("<") === true &&
					rawKey.endsWith(">") === true
				) {
					const key = rawKey.slice(1, -1);

					if (Object.hasOwn(result, key) === true) {
						console.warn(
							`HtmlString.resolve: both "${key}" and "${rawKey}" present, using "${rawKey}"`
						);
					}

					result[key] = HtmlString.wrap(value);
					continue;
				}

				result[rawKey] = HtmlString.resolve(value);
			}

			return result as T;
		}

		return data;
	}

	/**
	 * Turns the value of a marked `<key>` into trusted HTML
	 */
	protected static wrap(value: unknown): unknown {
		if (typeof value === "string") {
			return new HtmlString(value);
		}

		if (Array.isArray(value) === true) {
			return value.map((item) => HtmlString.wrap(item));
		}

		// an object cannot be trusted as a whole, but it may
		// carry marked keys of its own further down
		return HtmlString.resolve(value);
	}
}

/**
 * Escapes all values that get interpolated into a trusted string.
 *
 * @since 6.0.0
 */
export function escape(values: HtmlData): StringTemplateValues {
	const escaped: StringTemplateValues = {};

	for (const key in values) {
		const value = values[key];

		if (value instanceof HtmlString) {
			escaped[key] = String(value);
		} else if (isObject(value) === true) {
			escaped[key] = escape(value as HtmlData);
		} else if (value === null || value === undefined) {
			escaped[key] = null;
		} else {
			escaped[key] = escapeHTML(value);
		}
	}

	return escaped;
}

/**
 * Marks a value as trusted HTML by returning an `HtmlString` instance.
 *
 * @since 6.0.0
 */
export default function html(value: unknown): HtmlString {
	if (value instanceof HtmlString) {
		return value;
	}

	return new HtmlString(String(value ?? ""));
}
