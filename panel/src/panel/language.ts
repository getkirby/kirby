import { reactive } from "vue";
import State from "./state";

type LanguageState = {
	code: string | null;
	default: boolean;
	direction: string;
	hasCustomDomain: boolean;
	name: string | null;
	rules: Record<string, string>;
};

export function defaults(): LanguageState {
	return {
		code: null,
		default: false,
		direction: "ltr",
		hasCustomDomain: false,
		name: null,
		rules: {}
	};
}

/**
 * Represents the currently active content language
 * in a multi-language setup
 *
 * @since 4.0.0
 */
export default function Language() {
	const parent = State("language", defaults());

	return reactive({
		...parent,

		get isDefault(): boolean {
			return this.default;
		}
	});
}
