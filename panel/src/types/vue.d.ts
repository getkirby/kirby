import type Api from "@/api";
import type Panel from "@/panel/panel";
import type { helper } from "../helpers/index";
import type { library } from "../libraries/index";

declare module "vue" {
	interface ComponentCustomProperties {
		$api: Api;
		$esc: typeof helper.string.escapeHTML;
		$helper: typeof helper;
		$library: typeof library;
		$panel: Panel;
	}
}
