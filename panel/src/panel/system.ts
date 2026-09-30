import State from "./state";

export type SystemState = {
	ascii: Record<string, string>;
	isLocal: boolean;
	locales: Record<string, string>;
	slugs: string[];
	title: string;
};

export function defaults(): SystemState {
	return {
		ascii: {},
		isLocal: false,
		locales: {},
		slugs: [],
		title: ""
	};
}

/**
 * Represents global system information provided by the backend,
 * such as slug rules and available locales
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
export default function System() {
	return State("system", defaults());
}
