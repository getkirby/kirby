import Bar from "./Bar.vue";
import Box from "./Box.vue";
import Column from "./Column.vue";
import ColorFrame from "./Frame/ColorFrame.vue";
import Dropzone from "./Dropzone.vue";
import Frame from "./Frame/Frame.vue";
import Grid from "./Grid.vue";
import Header from "./Header.vue";
import IconFrame from "./Frame/IconFrame.vue";
import ImageFrame from "./Frame/ImageFrame.vue";
import Overlay from "./Overlay.vue";
import Stack from "./Stack.vue";
import Stat from "./Stat.vue";
import Stats from "./Stats.vue";
import Table from "./Table.vue";
import TableCell from "./TableCell.vue";
import Tabs from "./Tabs.vue";
import VideoFrame from "./Frame/VideoFrame.vue";

export default {
	install(app) {
		app.component("k-bar", Bar);
		app.component("k-box", Box);
		app.component("k-color-frame", ColorFrame);
		app.component("k-column", Column);
		app.component("k-dropzone", Dropzone);
		app.component("k-frame", Frame);
		app.component("k-grid", Grid);
		app.component("k-header", Header);
		app.component("k-icon-frame", IconFrame);
		app.component("k-image-frame", ImageFrame);
		app.component("k-image", ImageFrame);
		app.component("k-overlay", Overlay);
		app.component("k-stack", Stack);
		app.component("k-stat", Stat);
		app.component("k-stats", Stats);
		app.component("k-table", Table);
		app.component("k-table-cell", TableCell);
		app.component("k-tabs", Tabs);
		app.component("k-video-frame", VideoFrame);
	}
};
