import Breadcrumb from "./Breadcrumb.vue";
import Button from "./Button.vue";
import ButtonGroup from "./ButtonGroup.vue";
import Dropdown from "./Dropdown.vue";
import DropdownContent from "./DropdownContent.vue";
import DropdownItem from "./DropdownItem.vue";
import Link from "./Link.vue";
import Languages from "./Languages.vue";
import OptionsDropdown from "./OptionsDropdown.vue";
import Pagination from "./Pagination.vue";
import PrevNext from "./PrevNext.vue";
import Search from "./Search.vue";
import Tag from "./Tag.vue";
import Topbar from "./Topbar.vue";

/** @deprecated @todo Remove in v5.0 */
import ButtonDisabled from "./ButtonDisabled.vue";
import ButtonLink from "./ButtonLink.vue";
import ButtonNative from "./ButtonNative.vue";

export default {
	install(app) {
		app.component("k-breadcrumb", Breadcrumb);
		app.component("k-button", Button);
		app.component("k-button-group", ButtonGroup);
		app.component("k-dropdown", Dropdown);
		app.component("k-dropdown-content", DropdownContent);
		app.component("k-dropdown-item", DropdownItem);
		app.component("k-languages-dropdown", Languages);
		app.component("k-link", Link);
		app.component("k-options-dropdown", OptionsDropdown);
		app.component("k-pagination", Pagination);
		app.component("k-prev-next", PrevNext);
		app.component("k-search", Search);
		app.component("k-tag", Tag);
		app.component("k-topbar", Topbar);

		/** @deprecated @todo Remove in v5.0 */
		app.component("k-button-disabled", ButtonDisabled);
		app.component("k-button-link", ButtonLink);
		app.component("k-button-native", ButtonNative);
	}
};
