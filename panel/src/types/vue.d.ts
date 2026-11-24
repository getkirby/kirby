import type Panel from "@/panel/panel";
import type * as components from "../components/index";
import type { helper } from "../helpers/index";
import type { library } from "../libraries/index";

type Components = typeof components;

declare module "vue" {
	interface ComponentCustomProperties {
		$api: Panel["api"];
		$dialog: Panel["dialog"]["open"];
		$drawer: Panel["drawer"]["open"];
		$dropdown: Panel["dropdown"]["openAsync"];
		$esc: typeof helper.string.escapeHTML;
		$events: Panel["events"];
		$go: Panel["view"]["open"];
		$h: Panel["html"];
		$helper: typeof helper;
		$library: typeof library;
		$panel: Panel;
		$reload: Panel["reload"];
		$t: Panel["t"];
		$th: Panel["th"];
		$url: Panel["url"];
	}

	// eslint-disable-next-line @typescript-eslint/no-empty-object-type
	interface GlobalComponents extends Components {}
}
