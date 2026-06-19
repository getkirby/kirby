import type Panel from "@/panel/panel";
import type { helper } from "../helpers/index";
import type { library } from "../libraries/index";

declare module "vue" {
	interface ComponentCustomProperties {
		$api: Panel["api"];
		$dialog: Panel["dialog"]["open"];
		$drawer: Panel["drawer"]["open"];
		$dropdown: Panel["dropdown"]["openAsync"];
		$esc: typeof helper.string.escapeHTML;
		$events: Panel["events"];
		$go: Panel["view"]["open"];
		$helper: typeof helper;
		$library: typeof library;
		$panel: Panel;
		$reload: Panel["reload"];
		$t: Panel["t"];
		$url: Panel["url"];
	}
}
