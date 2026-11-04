import Extension from "../Extension";

/**
 * Binds arbitrary key handlers passed as options.
 * A handler suppresses the default browser key event unless it returns
 * `false`, which passes the key on to the next key binding.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default class Keys extends Extension<Record<string, () => unknown>> {
	get name() {
		return "keys";
	}

	keys() {
		const keys: Record<string, () => boolean> = {};

		for (const option of Object.keys(this.options)) {
			keys[option] = () => this.options[option]() !== false;
		}

		return keys;
	}
}
