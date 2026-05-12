import Extension from "../Extension";

/**
 * Binds arbitrary key handlers passed as options.
 * Each handler returns true to suppress the default browser key event.
 */
export default class Keys extends Extension<Record<string, () => void>> {
	get name() {
		return "keys";
	}

	keys() {
		const keys: Record<string, () => boolean> = {};

		for (const option of Object.keys(this.options)) {
			keys[option] = () => {
				this.options[option]();
				return true;
			};
		}

		return keys;
	}
}
