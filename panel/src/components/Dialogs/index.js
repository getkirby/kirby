import Elements from "./Elements/index.js";

import Dialog from "./Dialog.vue";
import ErrorDialog from "./ErrorDialog.vue";
import FiberDialog from "./FiberDialog.vue";
import FilesDialog from "./FilesDialog.vue";
import FormDialog from "./FormDialog.vue";
import LanguageDialog from "./LanguageDialog.vue";
import PageCreateDialog from "./PageCreateDialog.vue";
import PagesDialog from "./PagesDialog.vue";
import RemoveDialog from "./RemoveDialog.vue";
import SearchDialog from "./SearchDialog.vue";
import TextDialog from "./TextDialog.vue";
import UsersDialog from "./UsersDialog.vue";

export default {
	install(app) {
		app.use(Elements);

		app.component("k-dialog", Dialog);
		app.component("k-error-dialog", ErrorDialog);
		app.component("k-fiber-dialog", FiberDialog);
		app.component("k-files-dialog", FilesDialog);
		app.component("k-form-dialog", FormDialog);
		app.component("k-language-dialog", LanguageDialog);
		app.component("k-page-create-dialog", PageCreateDialog);
		app.component("k-pages-dialog", PagesDialog);
		app.component("k-remove-dialog", RemoveDialog);
		app.component("k-search-dialog", SearchDialog);
		app.component("k-text-dialog", TextDialog);
		app.component("k-users-dialog", UsersDialog);
	}
};
