import State from "./state";

type SystemState = {
	ascii: Record<string, string>;
	csrf: string | null;
	isLocal: boolean;
	locales: Record<string, string>;
	slugs: string[];
	title: string | null;
};

export function defaults(): SystemState {
	return {
		ascii: {},
		csrf: null,
		isLocal: false,
		locales: {},
		slugs: [],
		title: null
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
