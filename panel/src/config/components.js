import Vue from "vue";

/* Dialogs */
import Dialog from "@/components/Dialogs/Dialog.vue";
import ErrorDialog from "@/components/Dialogs/ErrorDialog.vue";
import FileRemoveDialog from "@/components/Dialogs/FileRemoveDialog.vue";
import FileRenameDialog from "@/components/Dialogs/FileRenameDialog.vue";
import FilesDialog from "@/components/Dialogs/FilesDialog.vue";
import LanguageCreateDialog from "@/components/Dialogs/LanguageCreateDialog.vue";
import LanguageConvertDialog from "@/components/Dialogs/LanguageConvertDialog.vue";
import LanguageRemoveDialog from "@/components/Dialogs/LanguageRemoveDialog.vue";
import LanguageUpdateDialog from "@/components/Dialogs/LanguageUpdateDialog.vue";
import PageCreateDialog from "@/components/Dialogs/PageCreateDialog.vue";
import PageRemoveDialog from "@/components/Dialogs/PageRemoveDialog.vue";
import PageRenameDialog from "@/components/Dialogs/PageRenameDialog.vue";
import PageStatusDialog from "@/components/Dialogs/PageStatusDialog.vue";
import PageTemplateDialog from "@/components/Dialogs/PageTemplateDialog.vue";
import PageUrlDialog from "@/components/Dialogs/PageUrlDialog.vue";
import SiteRenameDialog from "@/components/Dialogs/SiteRenameDialog.vue";
import UserCreateDialog from "@/components/Dialogs/UserCreateDialog.vue";
import UserEmailDialog from "@/components/Dialogs/UserEmailDialog.vue";
import UserLanguageDialog from "@/components/Dialogs/UserLanguageDialog.vue";
import UserPasswordDialog from "@/components/Dialogs/UserPasswordDialog.vue";
import UserRemoveDialog from "@/components/Dialogs/UserRemoveDialog.vue";
import UserRenameDialog from "@/components/Dialogs/UserRenameDialog.vue";
import UserRoleDialog from "@/components/Dialogs/UserRoleDialog.vue";

Vue.component("k-dialog", Dialog);
Vue.component("k-error-dialog", ErrorDialog);
Vue.component("k-file-rename-dialog", FileRenameDialog);
Vue.component("k-file-remove-dialog", FileRemoveDialog);
Vue.component("k-files-dialog", FilesDialog);
Vue.component("k-language-create-dialog", LanguageCreateDialog);
Vue.component("k-language-convert-dialog", LanguageConvertDialog);
Vue.component("k-language-remove-dialog", LanguageRemoveDialog);
Vue.component("k-language-update-dialog", LanguageUpdateDialog);
Vue.component("k-page-create-dialog", PageCreateDialog);
Vue.component("k-page-rename-dialog", PageRenameDialog);
Vue.component("k-page-remove-dialog", PageRemoveDialog);
Vue.component("k-page-status-dialog", PageStatusDialog);
Vue.component("k-page-template-dialog", PageTemplateDialog);
Vue.component("k-page-url-dialog", PageUrlDialog);
Vue.component("k-site-rename-dialog", SiteRenameDialog);
Vue.component("k-user-create-dialog", UserCreateDialog);
Vue.component("k-user-email-dialog", UserEmailDialog);
Vue.component("k-user-language-dialog", UserLanguageDialog);
Vue.component("k-user-password-dialog", UserPasswordDialog);
Vue.component("k-user-remove-dialog", UserRemoveDialog);
Vue.component("k-user-rename-dialog", UserRenameDialog);
Vue.component("k-user-role-dialog", UserRoleDialog);

/* Forms */
import FormButtons from "@/components/Forms/FormButtons.vue";
import FormChanges from "@/components/Forms/FormChanges.vue";

Vue.component("k-form-buttons", FormButtons);
Vue.component("k-form-changes", FormChanges);

/* Layout */
import Dropzone from "@/components/Layout/Dropzone.vue";
import FilePreview from "@/components/Layout/FilePreview.vue";
import Tabs from "@/components/Layout/Tabs.vue";

Vue.component("k-dropzone", Dropzone);
Vue.component("k-file-preview", FilePreview);
Vue.component("k-tabs", Tabs);

/* Navigation */
import Languages from "@/components/Navigation/Languages.vue";
import Topbar from "@/components/Navigation/Topbar.vue";

Vue.component("k-languages-dropdown", Languages);
Vue.component("k-topbar", Topbar);

/* Sections */
import Sections from "@/components/Sections/Sections.vue";
import InfoSection from "@/components/Sections/InfoSection.vue";
import PagesSection from "@/components/Sections/PagesSection.vue";
import FilesSection from "@/components/Sections/FilesSection.vue";
import FieldsSection from "@/components/Sections/FieldsSection.vue";

Vue.component("k-sections", Sections);
Vue.component("k-info-section", InfoSection);
Vue.component("k-pages-section", PagesSection);
Vue.component("k-files-section", FilesSection);
Vue.component("k-fields-section", FieldsSection);

/* View */
import ErrorView from "@/components/Views/ErrorView.vue";

Vue.component("k-error-view", ErrorView);
