import Draggable from "./Draggable.vue";
import ErrorBoundary from "./ErrorBoundary.vue";
import ExpandHandle from "./ExpandHandle.vue";
import Fatal from "./Fatal.vue";
import Icon from "./Icon.vue";
import Icons from "./Icons.vue";
import Loader from "./Loader.vue";
import Notification from "./Notification.vue";
import OfflineWarning from "./OfflineWarning.vue";
import Progress from "./Progress.vue";
import SortHandle from "./SortHandle.vue";

export default {
	install(app) {
		app.component("k-draggable", Draggable);
		app.component("k-error-boundary", ErrorBoundary);
		app.component("k-expand-handle", ExpandHandle);
		app.component("k-fatal", Fatal);
		app.component("k-icon", Icon);
		app.component("k-icons", Icons);
		app.component("k-loader", Loader);
		app.component("k-notification", Notification);
		app.component("k-offline-warning", OfflineWarning);
		app.component("k-progress", Progress);
		app.component("k-sort-handle", SortHandle);
	}
};
