import Extension from "../Extension";

export default class Keys extends Extension {
	/** @returns {Record<string, () => boolean>} */
	keys() {
		const keys = /** @type {Record<string, () => boolean>} */ ({});

		for (const option in this.options) {
			keys[option] = () => {
				this.options[option]();

				// insert an return true in every callback function
				// which suppresses the default key event
				return true;
			};
		}

		return keys;
	}

	get name() {
		return "keys";
	}
}
