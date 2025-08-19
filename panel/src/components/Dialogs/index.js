import Elements from "./Elements/index.js";

/** Dialog foundation */
import Dialog from "./Dialog.vue";

/** Custom dialogs */
import ChangesDialog from "./ChangesDialog.vue";
import EmailDialog from "./EmailDialog.vue";
import ErrorDialog from "./ErrorDialog.vue";
import StateDialog from "./StateDialog.vue";
import FilesDialog from "./FilesDialog.vue";
import FilesPickerDialog from "./FilesPickerDialog.vue";
import FormDialog from "./FormDialog.vue";
import LanguageDialog from "./LanguageDialog.vue";
import LicenseDialog from "./LicenseDialog.vue";
import LockAlertDialog from "./LockAlertDialog.vue";
import LinkDialog from "./LinkDialog.vue";
import ModelsDialog from "./ModelsDialog.vue";
import ModelsPickerDialog from "./ModelsPickerDialog.vue";
import PageCreateDialog from "./PageCreateDialog.vue";
import PageMoveDialog from "./PageMoveDialog.vue";
import PagesDialog from "./PagesDialog.vue";
import PagesPickerDialog from "./PagesPickerDialog.vue";
import RemoveDialog from "./RemoveDialog.vue";
import SearchDialog from "./SearchDialog.vue";
import TextDialog from "./TextDialog.vue";
import TotpDialog from "./TotpDialog.vue";
import UploadDialog from "./UploadDialog.vue";
import UploadReplaceDialog from "./UploadReplaceDialog.vue";
import UsersDialog from "./UsersDialog.vue";
import UsersPickerDialog from "./UsersPickerDialog.vue";

export default {
	install(app) {
		app.use(Elements);

		app.component("k-dialog", Dialog);
		app.component("k-changes-dialog", ChangesDialog);
		app.component("k-email-dialog", EmailDialog);
		app.component("k-error-dialog", ErrorDialog);
		app.component("k-state-dialog", StateDialog);
		app.component("k-files-dialog", FilesDialog);
		app.component("k-files-picker-dialog", FilesPickerDialog);
		app.component("k-form-dialog", FormDialog);
		app.component("k-license-dialog", LicenseDialog);
		app.component("k-link-dialog", LinkDialog);
		app.component("k-lock-alert-dialog", LockAlertDialog);
		app.component("k-language-dialog", LanguageDialog);
		app.component("k-models-dialog", ModelsDialog);
		app.component("k-models-picker-dialog", ModelsPickerDialog);
		app.component("k-page-create-dialog", PageCreateDialog);
		app.component("k-page-move-dialog", PageMoveDialog);
		app.component("k-pages-dialog", PagesDialog);
		app.component("k-pages-picker-dialog", PagesPickerDialog);
		app.component("k-remove-dialog", RemoveDialog);
		app.component("k-search-dialog", SearchDialog);
		app.component("k-text-dialog", TextDialog);
		app.component("k-totp-dialog", TotpDialog);
		app.component("k-upload-dialog", UploadDialog);
		app.component("k-upload-replace-dialog", UploadReplaceDialog);
		app.component("k-users-dialog", UsersDialog);
		app.component("k-users-picker-dialog", UsersPickerDialog);
	}
};
