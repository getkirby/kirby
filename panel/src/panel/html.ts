import { isObject } from "@/helpers/object";

/**
 * Wraps and marks a string as trusted, pre-escaped HTML.
 *
 * A plain string flowing through Panel state is treated as untrusted and
 * should get escaped at the render site. An `HtmlString` instance can be
 *  passed through unchanged, so it can be rendered via `v-html`/
 * `v-safe-html` without further escaping.
 *
 * The backend signals safety by emitting JSON with the parent key wrapped
 * in `<…>`, e.g. `"<help>": "<b>html</b>"`. A marked key can also hold a
 * list, in which case every entry is trusted: `"<issues>": ["<b>a</b>"]`.
 * `HtmlString.resolve()` walks incoming state, finds those keys, rewraps
 * their values, and strips the brackets.
 *
 * Since the class extends `String`, instances behave like strings in
 * almost every context (template interpolation, attribute binding,
 * concatenation, `JSON.stringify`), and `instanceof` survives Vue's
 * `reactive()` proxy and prop type validation.
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
	 * Recursively walks `data` and rewraps any value whose parent key
	 * matches `<key>` into an `HtmlString`, stripping the brackets. Plain
	 * keys are kept as-is. Arrays are walked element by element. Class
	 * instances (e.g. component  instances passed as props) are passed
	 * through untouched.
	 *
	 * Returns a new object/array; does not mutate the input.
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
	 * Turns the value of a marked `<key>` into trusted HTML:
	 * a string becomes an `HtmlString`, a list becomes a list
	 * of `HtmlString` values
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
