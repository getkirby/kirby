import State from "./state";

export type SystemState = {
	ascii: Record<string, string>;
	csrf: string;
	isLocal: boolean;
	locales: Record<string, string>;
	slugs: string[];
	title: string;
};

export function defaults(): SystemState {
	return {
		ascii: {},
		csrf: "",
		isLocal: false,
		locales: {},
		slugs: [],
		title: ""
	};
}

/**
 * Represents global system information provided by the backend,
 * such as the CSRF token, slug rules, and available locales
 *
 * @since 4.0.0
 */
export default function System() {
	return State("system", defaults());
}
