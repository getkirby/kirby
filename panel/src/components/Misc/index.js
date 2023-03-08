import Draggable from "./Draggable.vue";
import ErrorBoundary from "./ErrorBoundary.vue";
import Fatal from "./Fatal.vue";
import Icon from "./Icon.vue";
import Icons from "./Icons.vue";
import Loader from "./Loader.vue";
import OfflineWarning from "./OfflineWarning.vue";
import Progress from "./Progress.vue";
import Registration from "./Registration.vue";
import SortHandle from "./SortHandle.vue";
import StatusIcon from "./StatusIcon.vue";
import UserInfo from "./UserInfo.vue";

export default {
	install(app) {
		app.component("k-draggable", Draggable);
		app.component("k-error-boundary", ErrorBoundary);
		app.component("k-fatal", Fatal);
		app.component("k-icon", Icon);
		app.component("k-icons", Icons);
		app.component("k-loader", Loader);
		app.component("k-offline-warning", OfflineWarning);
		app.component("k-progress", Progress);
		app.component("k-registration", Registration);
		app.component("k-status-icon", StatusIcon);
		app.component("k-sort-handle", SortHandle);
		app.component("k-user-info", UserInfo);
	}
};
