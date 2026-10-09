import type { App } from "vue";
import * as components from "@/components/index";
import InputValidator from "@/components/Forms/Input/InputValidator";
import Validator from "@/components/Forms/Validator";

/**
 * Registers all core components and custom elements
 */
export default {
	install(app: App) {
		for (const [name, component] of Object.entries(components)) {
			app.component(name, component);
		}

		customElements.define("k-input-validator", InputValidator);
		customElements.define("k-validator", Validator);
	}
};
