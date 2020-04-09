import Vue from "vue";

/* Dialogs */
import ErrorDialog from "@/components/Dialogs/ErrorDialog.vue";
import FileRemoveDialog from "@/components/Dialogs/FileRemoveDialog.vue";
import FileRenameDialog from "@/components/Dialogs/FileRenameDialog.vue";
import FilesDialog from "@/components/Dialogs/FilesDialog.vue";
import FormDialog from "@/components/Dialogs/FormDialog.vue";
import LanguageCreateDialog from "@/components/Dialogs/LanguageCreateDialog.vue";
import LanguageRemoveDialog from "@/components/Dialogs/LanguageRemoveDialog.vue";
import LanguageUpdateDialog from "@/components/Dialogs/LanguageUpdateDialog.vue";
import PageCreateDialog from "@/components/Dialogs/PageCreateDialog.vue";
import PageDuplicateDialog from "@/components/Dialogs/PageDuplicateDialog.vue";
import PageRemoveDialog from "@/components/Dialogs/PageRemoveDialog.vue";
import PageRenameDialog from "@/components/Dialogs/PageRenameDialog.vue";
import PageStatusDialog from "@/components/Dialogs/PageStatusDialog.vue";
import PageTemplateDialog from "@/components/Dialogs/PageTemplateDialog.vue";
import PageUrlDialog from "@/components/Dialogs/PageUrlDialog.vue";
import PagesDialog from "@/components/Dialogs/PagesDialog.vue";
import RemoveDialog from "@/components/Dialogs/RemoveDialog.vue";
import SiteRenameDialog from "@/components/Dialogs/SiteRenameDialog.vue";
import TextDialog from "@/components/Dialogs/TextDialog.vue";
import UserCreateDialog from "@/components/Dialogs/UserCreateDialog.vue";
import UserEmailDialog from "@/components/Dialogs/UserEmailDialog.vue";
import UserLanguageDialog from "@/components/Dialogs/UserLanguageDialog.vue";
import UserPasswordDialog from "@/components/Dialogs/UserPasswordDialog.vue";
import UserRemoveDialog from "@/components/Dialogs/UserRemoveDialog.vue";
import UserRenameDialog from "@/components/Dialogs/UserRenameDialog.vue";
import UserRoleDialog from "@/components/Dialogs/UserRoleDialog.vue";
import UsersDialog from "@/components/Dialogs/UsersDialog.vue";

Vue.component("k-error-dialog", ErrorDialog);
Vue.component("k-file-rename-dialog", FileRenameDialog);
Vue.component("k-file-remove-dialog", FileRemoveDialog);
Vue.component("k-files-dialog", FilesDialog);
Vue.component("k-form-dialog", FormDialog);
Vue.component("k-language-create-dialog", LanguageCreateDialog);
Vue.component("k-language-remove-dialog", LanguageRemoveDialog);
Vue.component("k-language-update-dialog", LanguageUpdateDialog);
Vue.component("k-page-create-dialog", PageCreateDialog);
Vue.component("k-page-duplicate-dialog", PageDuplicateDialog);
Vue.component("k-page-rename-dialog", PageRenameDialog);
Vue.component("k-page-remove-dialog", PageRemoveDialog);
Vue.component("k-page-status-dialog", PageStatusDialog);
Vue.component("k-page-template-dialog", PageTemplateDialog);
Vue.component("k-page-url-dialog", PageUrlDialog);
Vue.component("k-pages-dialog", PagesDialog);
Vue.component("k-remove-dialog", RemoveDialog);
Vue.component("k-site-rename-dialog", SiteRenameDialog);
Vue.component("k-text-dialog", TextDialog);
Vue.component("k-user-create-dialog", UserCreateDialog);
Vue.component("k-user-email-dialog", UserEmailDialog);
Vue.component("k-user-language-dialog", UserLanguageDialog);
Vue.component("k-user-password-dialog", UserPasswordDialog);
Vue.component("k-user-remove-dialog", UserRemoveDialog);
Vue.component("k-user-rename-dialog", UserRenameDialog);
Vue.component("k-user-role-dialog", UserRoleDialog);
Vue.component("k-users-dialog", UsersDialog);

/* Form */
import Form from "@/components/Forms/Form.vue";
import FormButtons from "@/components/Forms/FormButtons.vue";
import FormIndicator from "@/components/Forms/FormIndicator.vue";
import Fieldset from "@/components/Forms/Fieldset.vue";

/** Form Inputs */
import TextareaInput from "@/components/Forms/Input/TextareaInput.vue";

/** Form Fields */
import CheckboxesField from "@/components/Forms/Field/CheckboxesField.vue";
import DateField from "@/components/Forms/Field/DateField.vue";
import EmailField from "@/components/Forms/Field/EmailField.vue";
import FilesField from "@/components/Forms/Field/FilesField.vue";
import HeadlineField from "@/components/Forms/Field/HeadlineField.vue";
import InfoField from "@/components/Forms/Field/InfoField.vue";
import LineField from "@/components/Forms/Field/LineField.vue";
import MultiselectField from "@/components/Forms/Field/MultiselectField.vue";
import NumberField from "@/components/Forms/Field/NumberField.vue";
import PagesField from "@/components/Forms/Field/PagesField.vue";
import PasswordField from "@/components/Forms/Field/PasswordField.vue";
import RadioField from "@/components/Forms/Field/RadioField.vue";
import RangeField from "@/components/Forms/Field/RangeField.vue";
import SelectField from "@/components/Forms/Field/SelectField.vue";
import StructureField from "@/components/Forms/Field/StructureField.vue";
import TagsField from "@/components/Forms/Field/TagsField.vue";
import TelField from "@/components/Forms/Field/TelField.vue";
import TextField from "@/components/Forms/Field/TextField.vue";
import TextareaField from "@/components/Forms/Field/TextareaField.vue";
import TimeField from "@/components/Forms/Field/TimeField.vue";
import ToggleField from "@/components/Forms/Field/ToggleField.vue";
import UrlField from "@/components/Forms/Field/UrlField.vue";
import UsersField from "@/components/Forms/Field/UsersField.vue";

/* Form Toolbar */
import Toolbar from "@/components/Forms/Toolbar.vue";
import ToolbarEmailDialog from "@/components/Forms/Toolbar/EmailDialog.vue";
import ToolbarLinkDialog from "@/components/Forms/Toolbar/LinkDialog.vue";

/* Form Field Previews */
import FilesFieldPreview from "@/components/Forms/Previews/FilesFieldPreview.vue";
import EmailFieldPreview from "@/components/Forms/Previews/EmailFieldPreview.vue";
import PagesFieldPreview from "@/components/Forms/Previews/PagesFieldPreview.vue";
import ToggleFieldPreview from "@/components/Forms/Previews/ToggleFieldPreview.vue";
import UrlFieldPreview from "@/components/Forms/Previews/UrlFieldPreview.vue";
import UsersFieldPreview from "@/components/Forms/Previews/UsersFieldPreview.vue";

Vue.component("k-form", Form);
Vue.component("k-form-buttons", FormButtons);
Vue.component("k-form-indicator", FormIndicator);
Vue.component("k-fieldset", Fieldset);

Vue.component("k-textarea-input", TextareaInput);

Vue.component("k-checkboxes-field", CheckboxesField);
Vue.component("k-date-field", DateField);
Vue.component("k-email-field", EmailField);
Vue.component("k-files-field", FilesField);
Vue.component("k-headline-field", HeadlineField);
Vue.component("k-info-field", InfoField);
Vue.component("k-line-field", LineField);
Vue.component("k-multiselect-field", MultiselectField);
Vue.component("k-number-field", NumberField);
Vue.component("k-pages-field", PagesField);
Vue.component("k-password-field", PasswordField);
Vue.component("k-radio-field", RadioField);
Vue.component("k-range-field", RangeField);
Vue.component("k-select-field", SelectField);
Vue.component("k-structure-field", StructureField);
Vue.component("k-tags-field", TagsField);
Vue.component("k-text-field", TextField);
Vue.component("k-textarea-field", TextareaField);
Vue.component("k-tel-field", TelField);
Vue.component("k-time-field", TimeField);
Vue.component("k-toggle-field", ToggleField);
Vue.component("k-url-field", UrlField);
Vue.component("k-users-field", UsersField);

Vue.component("k-toolbar", Toolbar);
Vue.component("k-toolbar-email-dialog", ToolbarEmailDialog);
Vue.component("k-toolbar-link-dialog", ToolbarLinkDialog);

Vue.component("k-email-field-preview", EmailFieldPreview);
Vue.component("k-files-field-preview", FilesFieldPreview);
Vue.component("k-pages-field-preview", PagesFieldPreview);
Vue.component("k-toggle-field-preview", ToggleFieldPreview);
Vue.component("k-url-field-preview", UrlFieldPreview);
Vue.component("k-users-field-preview", UsersFieldPreview);

/* Layout */
import Collection from "@/components/Layout/Collection.vue";
import FilePreview from "@/components/Layout/FilePreview.vue";
import TabsView from "@/components/Layout/Tabs.vue";

Vue.component("k-collection", Collection);
Vue.component("k-file-preview", FilePreview);
Vue.component("k-tabs-view", TabsView);

/* Navigation */
import Languages from "@/components/Navigation/Languages.vue";
import Search from "@/components/Navigation/Search.vue";
import Topbar from "@/components/Navigation/Topbar.vue";

Vue.component("k-languages-dropdown", Languages);
Vue.component("k-search", Search);
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

/* Views */
import BrowserView from "@/components/Views/BrowserView.vue";
import CustomView from "@/components/Views/CustomView.vue";
import ErrorView from "@/components/Views/ErrorView.vue";
import FileView from "@/components/Views/FileView.vue";
import InstallationView from "@/components/Views/InstallationView.vue";
import LoginView from "@/components/Views/LoginView.vue";
import PageView from "@/components/Views/PageView.vue";
import SettingsView from "@/components/Views/SettingsView.vue";
import SiteView from "@/components/Views/SiteView.vue";
import UsersView from "@/components/Views/UsersView.vue";
import UserView from "@/components/Views/UserView.vue";

Vue.component("k-browser-view", BrowserView);
Vue.component("k-custom-view", CustomView);
Vue.component("k-error-view", ErrorView);
Vue.component("k-file-view", FileView);
Vue.component("k-installation-view", InstallationView);
Vue.component("k-login-view", LoginView);
Vue.component("k-page-view", PageView);
Vue.component("k-settings-view", SettingsView);
Vue.component("k-site-view", SiteView);
Vue.component("k-users-view", UsersView);
Vue.component("k-user-view", UserView);
