import { reactive } from "vue";
import { StringTemplateValues, template } from "@/helpers/string";
import State from "./state";

type TranslationState = {
	code: string | null;
	data: StringTemplateValues;
	direction: string;
	name: string | null;
	weekday: number;
};

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
 * Converts a translation code like `pt_BR` into a
 * BCP 47 language tag like `pt-BR`
 */
function lang(code: string): string {
	for (const tag of [code.replace("_", "-"), code.split(/[-_@]/)[0]]) {
		try {
			return Intl.getCanonicalLocales(tag)[0];
		} catch {
			// codes like `sr@latin` or `de-sie` have no tag,
			// so try their primary language next
		}
	}

	return "en";
}

/**
 * Represents the interface language for the current user
 *
 * @since 4.0.0
 */
export default function Translation() {
	const parent = State("translation", defaults());

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
				document.documentElement.lang = lang(this.code);
			}

			document.body.dir = this.direction;

			return this.state();
		},

		/**
		 * Fetches a translation string by key and
		 * optionally replaces placeholders
		 * with values from the data argument
		 */
		translate(
			key: unknown,
			data?: StringTemplateValues,
			fallback?: string
		): StringTemplateValues[string] | undefined {
			if (typeof key !== "string") {
				return;
			}

			const value = this.data[key] ?? fallback;

			if (typeof value !== "string") {
				return value;
			}

			return template(value, data);
		}
	});
}
