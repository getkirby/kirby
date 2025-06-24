import Breadcrumb from "./Breadcrumb.vue";
import Browser from "./Browser.vue";
import Button from "./Button.vue";
import ButtonGroup from "./ButtonGroup.vue";
import FileBrowser from "./FileBrowser.vue";
import Link from "./Link.vue";
import ModelTabs from "./ModelTabs.vue";
import Navigate from "./Navigate.vue";
import PageTree from "./PageTree.vue";
import Pagination from "./Pagination.vue";
import PrevNext from "./PrevNext.vue";
import SearchBar from "./SearchBar.vue";
import Tag from "./Tag.vue";
import Tags from "./Tags.vue";
import Tree from "./Tree.vue";

export default {
	install(app) {
		app.component("k-breadcrumb", Breadcrumb);
		app.component("k-browser", Browser);
		app.component("k-button", Button);
		app.component("k-button-group", ButtonGroup);
		app.component("k-file-browser", FileBrowser);
		app.component("k-link", Link);
		app.component("k-model-tabs", ModelTabs);
		app.component("k-navigate", Navigate);
		app.component("k-page-tree", PageTree);
		app.component("k-pagination", Pagination);
		app.component("k-prev-next", PrevNext);
		app.component("k-search-bar", SearchBar);
		app.component("k-tag", Tag);
		app.component("k-tags", Tags);
		app.component("k-tree", Tree);
	}
};
