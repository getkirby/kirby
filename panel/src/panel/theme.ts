import { reactive } from "vue";
import State from "./state";

type ThemeState = {
	setting: string | null;
	system: string;
};

export function defaults(media?: MediaQueryList): ThemeState {
	return {
		setting: localStorage.getItem("kirby$theme"),
		system: media?.matches ? "dark" : "light"
	};
}

/**
 * Tracks the active color theme (light, dark, or system)
 * resolved from user setting, Panel config, or OS preference
 *
 * @since 5.0.0
 */
export default function Theme(panel: { config: { theme: string | null } }) {
	const media = window.matchMedia("(prefers-color-scheme: dark)");
	const parent = State("theme", defaults(media));

	const theme = reactive({
		...parent,

		get config(): string | null {
			return panel.config.theme;
		},

		get current(): string {
			const setting = this.setting ?? this.config;

			if (setting === "system") {
				return this.system;
			}

			return setting ?? this.system;
		},

		reset(): void {
			this.setting = null;
			localStorage.removeItem("kirby$theme");
		},

		set(theme: "light" | "dark" | "system"): void {
			this.setting = theme;
			localStorage.setItem("kirby$theme", theme);
		}
	});

	// watch the media query for changes
	// and update the system state
	media?.addEventListener("change", (event) => {
		theme.system = event.matches ? "dark" : "light";
	});

	return theme;
}
