import Extension from "../Extension";

export default class Keys extends Extension<Record<string, () => void>> {
	get name() {
		return "keys";
	}

	keys() {
		const keys: Record<string, () => boolean> = {};

		for (const option of Object.keys(this.options)) {
			keys[option] = () => {
				this.options[option]();

				// insert a return true in every callback function
				// which suppresses the default key event
				return true;
			};
		}

		return keys;
	}
}
