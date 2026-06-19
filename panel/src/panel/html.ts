/**
 * Wraps and marks a string as trusted, pre-escaped HTML.
 *
 * A plain string flowing through Panel state is treated as untrusted and
 * should get escaped at the render site. An `HtmlString` instance can be
 *  passed through unchanged, so it can be rendered via `v-html`/
 * `v-safe-html` without further escaping.
 *
 * The backend signals safety by emitting JSON with the parent key wrapped
 * in `<…>`, e.g. `"<help>": "<b>html</b>"`. `HtmlString.resolve()` walks
 * incoming state, finds those keys, rewraps their values, and strips the
 * brackets. `Kirby\Toolkit\HtmlString::resolve()` does the inverse on the
 * PHP side.
 *
 * Since the class extends `String`, instances behave like strings in
 * almost every context (template interpolation, attribute binding,
 * concatenation, `JSON.stringify`), and `instanceof` survives Vue's
 * `reactive()` proxy and prop type validation.
 *
 * @since 6.0.0
 */
export class HtmlString extends String {
	constructor(value: string) {
		super(value);
	}

	/**
	 * Recursively walks `data` and rewraps any value whose parent key
	 * matches `<key>` into an `HtmlString`, stripping the brackets. Plain
	 * keys are kept as-is. Arrays are walked element by element.
	 *
	 * Returns a new object/array; does not mutate the input.
	 */
	static resolve<T>(data: T): T {
		if (Array.isArray(data) === true) {
			return data.map((value) => HtmlString.resolve(value)) as T;
		}

		if (data !== null && typeof data === "object") {
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

					if (Object.prototype.hasOwnProperty.call(result, key) === true) {
						console.warn(
							`HtmlString.fromJSON: both "${key}" and "${rawKey}" present — the bracketed version wins`
						);
					}

					result[key] =
						typeof value === "string"
							? new HtmlString(value)
							: HtmlString.resolve(value);
					continue;
				}

				result[rawKey] = HtmlString.resolve(value);
			}

			return result as T;
		}

		return data;
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
