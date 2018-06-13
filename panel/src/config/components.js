import Vue from "vue";

/* Dialogs */
import Dialog from "@/components/Dialogs/Dialog.vue";
import ErrorDialog from "@/components/Dialogs/ErrorDialog.vue";
import FileRenameDialog from "@/components/Dialogs/FileRenameDialog.vue";
import FileRemoveDialog from "@/components/Dialogs/FileRemoveDialog.vue";
import PageCreateDialog from "@/components/Dialogs/PageCreateDialog.vue";
import PageUrlDialog from "@/components/Dialogs/PageUrlDialog.vue";
import PageStatusDialog from "@/components/Dialogs/PageStatusDialog.vue";
import PageRenameDialog from "@/components/Dialogs/PageRenameDialog.vue";
import PageRemoveDialog from "@/components/Dialogs/PageRemoveDialog.vue";
import PageTemplateDialog from "@/components/Dialogs/PageTemplateDialog.vue";
import SiteRenameDialog from "@/components/Dialogs/SiteRenameDialog.vue";
import UserCreateDialog from "@/components/Dialogs/UserCreateDialog.vue";
import UserRenameDialog from "@/components/Dialogs/UserRenameDialog.vue";
import UserRoleDialog from "@/components/Dialogs/UserRoleDialog.vue";
import UserPasswordDialog from "@/components/Dialogs/UserPasswordDialog.vue";
import UserLanguageDialog from "@/components/Dialogs/UserLanguageDialog.vue";
import UserRemoveDialog from "@/components/Dialogs/UserRemoveDialog.vue";

Vue.component("kirby-dialog", Dialog);
Vue.component("kirby-error-dialog", ErrorDialog);
Vue.component("kirby-file-rename-dialog", FileRenameDialog);
Vue.component("kirby-file-remove-dialog", FileRemoveDialog);
Vue.component("kirby-page-create-dialog", PageCreateDialog);
Vue.component("kirby-page-rename-dialog", PageRenameDialog);
Vue.component("kirby-page-remove-dialog", PageRemoveDialog);
Vue.component("kirby-page-status-dialog", PageStatusDialog);
Vue.component("kirby-page-template-dialog", PageTemplateDialog);
Vue.component("kirby-page-url-dialog", PageUrlDialog);
Vue.component("kirby-site-rename-dialog", SiteRenameDialog);
Vue.component("kirby-user-create-dialog", UserCreateDialog);
Vue.component("kirby-user-rename-dialog", UserRenameDialog);
Vue.component("kirby-user-role-dialog", UserRoleDialog);
Vue.component("kirby-user-password-dialog", UserPasswordDialog);
Vue.component("kirby-user-language-dialog", UserLanguageDialog);
Vue.component("kirby-user-remove-dialog", UserRemoveDialog);

/* Forms */
import FormButtons from "@/components/Forms/FormButtons.vue";

Vue.component("kirby-form-buttons", FormButtons);

/* Layout */
import Dropzone from "@/components/Layout/Dropzone.vue";
import FilePreview from "@/components/Layout/FilePreview.vue";

Vue.component("kirby-dropzone", Dropzone);
Vue.component("kirby-file-preview", FilePreview);

/* Navigation */
import Topbar from "@/components/Navigation/Topbar.vue";

Vue.component("kirby-topbar", Topbar);

/* Sections */
import Sections from "@/components/Sections/Sections.vue";
import PagesSection from "@/components/Sections/PagesSection.vue";
import FilesSection from "@/components/Sections/FilesSection.vue";
import FieldsSection from "@/components/Sections/FieldsSection.vue";

Vue.component("kirby-sections", Sections);
Vue.component("kirby-pages-section", PagesSection);
Vue.component("kirby-files-section", FilesSection);
Vue.component("kirby-fields-section", FieldsSection);

/* Tabs */
import Tabs from "@/components/Tabs/Tabs.vue";
import TabsDropdown from "@/components/Tabs/TabsDropdown.vue";

Vue.component("kirby-tabs", Tabs);
Vue.component("kirby-tabs-dropdown", TabsDropdown);

/* View */
import ErrorView from "@/components/Views/ErrorView.vue";

Vue.component("kirby-error-view", ErrorView);
