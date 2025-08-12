import Dropdown from "./Dropdown.vue";
import DropdownItem from "./DropdownItem.vue";

import OptionsDropdown from "./OptionsDropdown.vue";
import PicklistDropdown from "./PicklistDropdown.vue";

export default {
	install(app) {
		app.component("k-dropdown", Dropdown);
		app.component("k-dropdown-item", DropdownItem);

		app.component("k-options-dropdown", OptionsDropdown);
		app.component("k-picklist-dropdown", PicklistDropdown);

		// @deprecated
		app.component("k-dropdown-content", Dropdown);
	}
};
