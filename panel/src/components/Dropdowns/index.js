import DropdownContent from "./DropdownContent.vue";
import DropdownItem from "./DropdownItem.vue";

import OptionsDropdown from "./OptionsDropdown.vue";
import PicklistDropdown from "./PicklistDropdown.vue";

export default {
	install(app) {
		app.component("k-dropdown-content", DropdownContent);
		app.component("k-dropdown-item", DropdownItem);

		app.component("k-options-dropdown", OptionsDropdown);
		app.component("k-picklist-dropdown", PicklistDropdown);
	}
};
