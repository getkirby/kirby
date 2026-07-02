import type { helper } from "../helpers/index";
import type { library } from "../libraries/index";

declare module "vue" {
	interface ComponentCustomProperties {
		$esc: typeof helper.string.escapeHTML;
		$helper: typeof helper;
		$library: typeof library;
	}
}
