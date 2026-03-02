import type { helper } from "../helpers/index";

declare module "vue" {
	interface ComponentCustomProperties {
		$helper: typeof helper;
		$esc: typeof helper.string.escapeHTML;
	}
}
