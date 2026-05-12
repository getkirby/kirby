import { reactive } from "vue";
import { StringTemplateValues, template } from "@/helpers/string";
import State from "./state";

export type TranslationState = {
	code: string | null;
	data: StringTemplateValues;
	direction: string;
	name: string | null;
	weekday: number;
};

type Data = StringTemplateValues;

export function defaults(): TranslationState {
	return {
		code: null,
		data: {},
		direction: "ltr",
		name: null,
		weekday: 1
	};
}

/**
 * Represents the interface language for the current user
 *
 * @since 4.0.0
 */
export default function Translation() {
	const parent = State("translation", defaults());

	/**
	 * Fetches a translation string by key, optionally replacing placeholders
	 * with values from the data argument. Falls back to the given fallback
	 * string, or the key itself if no fallback is provided.
	 *
	 * @example
	 * t("key")                      // key as fallback
	 * t("key", "fallback")          // shorthand fallback
	 * t("key", { name: "…" })       // with placeholder
	 * t("key", { name: "…" }, "f")  // with placeholders and fallback
	 * t(42)                         // undefined
	 */
	function translate(key: string, fallback: string): string;
	function translate(key: string, data?: Data, fallback?: string): string;
	function translate(key: unknown, data?: Data, fallback?: string): undefined;
	function translate(
		this: { data: Data },
		key: unknown,
		data?: Data | string,
		fallback?: string
	): Data[string] | undefined {
		if (typeof key !== "string") {
			return;
		}

		if (typeof data === "string") {
			fallback = data;
			data = undefined;
		}

		const value = this.data[key] ?? fallback ?? key;

		if (typeof value !== "string") {
			return value;
		}

		return template(value, data);
	}

	return reactive({
		...parent,

		/**
		 * Applies the new state and syncs document language and
		 * reading direction so the DOM reflects the active translation.
		 * Direction is also set on <body> since some elements (e.g. drag
		 * ghosts) are injected outside the Panel root.
		 */
		set(state: Partial<TranslationState>): TranslationState {
			parent.set.call(this, state);

			if (this.code) {
				document.documentElement.lang = this.code;
			}

			document.body.dir = this.direction;

			return this.state();
		},

		translate
	});
}
