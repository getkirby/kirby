import Extension from "../Extension";

export default class Keys extends Extension {
	keys() {
		const keys = {};

		for (const option in this.options) {
			keys[option] = () => {
				this.options[option]();

				// insert an return true in every callback function
				// which surpresses the default key event
				return true;
			};
		}

		return keys;
	}
}
