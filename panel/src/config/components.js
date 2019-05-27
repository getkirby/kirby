import Vue from "vue";

/* Dialogs */
import Dialog from "@/components/Dialogs/Dialog.vue";
import ErrorDialog from "@/components/Dialogs/ErrorDialog.vue";
import FileRemoveDialog from "@/components/Dialogs/FileRemoveDialog.vue";
import FileRenameDialog from "@/components/Dialogs/FileRenameDialog.vue";
import FilesDialog from "@/components/Dialogs/FilesDialog.vue";
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
import SiteRenameDialog from "@/components/Dialogs/SiteRenameDialog.vue";
import UserCreateDialog from "@/components/Dialogs/UserCreateDialog.vue";
import UserEmailDialog from "@/components/Dialogs/UserEmailDialog.vue";
import UserLanguageDialog from "@/components/Dialogs/UserLanguageDialog.vue";
import UserPasswordDialog from "@/components/Dialogs/UserPasswordDialog.vue";
import UserRemoveDialog from "@/components/Dialogs/UserRemoveDialog.vue";
import UserRenameDialog from "@/components/Dialogs/UserRenameDialog.vue";
import UserRoleDialog from "@/components/Dialogs/UserRoleDialog.vue";
import UsersDialog from "@/components/Dialogs/UsersDialog.vue";

Vue.component("k-dialog", Dialog);
Vue.component("k-error-dialog", ErrorDialog);
Vue.component("k-file-rename-dialog", FileRenameDialog);
Vue.component("k-file-remove-dialog", FileRemoveDialog);
Vue.component("k-files-dialog", FilesDialog);
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
Vue.component("k-site-rename-dialog", SiteRenameDialog);
Vue.component("k-user-create-dialog", UserCreateDialog);
Vue.component("k-user-email-dialog", UserEmailDialog);
Vue.component("k-user-language-dialog", UserLanguageDialog);
Vue.component("k-user-password-dialog", UserPasswordDialog);
Vue.component("k-user-remove-dialog", UserRemoveDialog);
Vue.component("k-user-rename-dialog", UserRenameDialog);
Vue.component("k-user-role-dialog", UserRoleDialog);
Vue.component("k-users-dialog", UsersDialog);

/* Form */
import Autocomplete from "@/components/Forms/Autocomplete.vue";
import Calendar from "@/components/Forms/Calendar.vue";
import Counter from "@/components/Forms/Counter.vue";
import Form from "@/components/Forms/Form.vue";
import FormButtons from "@/components/Forms/FormButtons.vue";
import Field from "@/components/Forms/Field.vue";
import Fieldset from "@/components/Forms/Fieldset.vue";
import Input from "@/components/Forms/Input.vue";
import Upload from "@/components/Forms/Upload.vue";

/** Form Inputs */
import CheckboxInput from "@/components/Forms/Input/CheckboxInput.vue";
import CheckboxesInput from "@/components/Forms/Input/CheckboxesInput.vue";
import DateInput from "@/components/Forms/Input/DateInput.vue";
import DateTimeInput from "@/components/Forms/Input/DateTimeInput.vue";
import EmailInput from "@/components/Forms/Input/EmailInput.vue";
import MultiselectInput from "@/components/Forms/Input/MultiselectInput.vue";
import NumberInput from "@/components/Forms/Input/NumberInput.vue";
import PasswordInput from "@/components/Forms/Input/PasswordInput.vue";
import RadioInput from "@/components/Forms/Input/RadioInput.vue";
import RangeInput from "@/components/Forms/Input/RangeInput.vue";
import SelectInput from "@/components/Forms/Input/SelectInput.vue";
import TagsInput from "@/components/Forms/Input/TagsInput.vue";
import TelInput from "@/components/Forms/Input/TelInput.vue";
import TextInput from "@/components/Forms/Input/TextInput.vue";
import TextareaInput from "@/components/Forms/Input/TextareaInput.vue";
import TimeInput from "@/components/Forms/Input/TimeInput.vue";
import ToggleInput from "@/components/Forms/Input/ToggleInput.vue";
import UrlInput from "@/components/Forms/Input/UrlInput.vue";

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

/* Form Field Previews */
import FilesFieldPreview from "@/components/Forms/Previews/FilesFieldPreview.vue";
import EmailFieldPreview from "@/components/Forms/Previews/EmailFieldPreview.vue";
import PagesFieldPreview from "@/components/Forms/Previews/PagesFieldPreview.vue";
import UrlFieldPreview from "@/components/Forms/Previews/UrlFieldPreview.vue";
import UsersFieldPreview from "@/components/Forms/Previews/UsersFieldPreview.vue";

Vue.component("k-calendar", Calendar);
Vue.component("k-counter", Counter);
Vue.component("k-autocomplete", Autocomplete);
Vue.component("k-form", Form);
Vue.component("k-form-buttons", FormButtons);
Vue.component("k-field", Field);
Vue.component("k-fieldset", Fieldset);
Vue.component("k-input", Input);
Vue.component("k-upload", Upload);

Vue.component("k-checkbox-input", CheckboxInput);
Vue.component("k-checkboxes-input", CheckboxesInput);
Vue.component("k-date-input", DateInput);
Vue.component("k-datetime-input", DateTimeInput);
Vue.component("k-email-input", EmailInput);
Vue.component("k-multiselect-input", MultiselectInput);
Vue.component("k-number-input", NumberInput);
Vue.component("k-password-input", PasswordInput);
Vue.component("k-radio-input", RadioInput);
Vue.component("k-range-input", RangeInput);
Vue.component("k-select-input", SelectInput);
Vue.component("k-tags-input", TagsInput);
Vue.component("k-tel-input", TelInput);
Vue.component("k-text-input", TextInput);
Vue.component("k-textarea-input", TextareaInput);
Vue.component("k-time-input", TimeInput);
Vue.component("k-toggle-input", ToggleInput);
Vue.component("k-url-input", UrlInput);

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

Vue.component("k-email-field-preview", EmailFieldPreview);
Vue.component("k-files-field-preview", FilesFieldPreview);
Vue.component("k-pages-field-preview", PagesFieldPreview);
Vue.component("k-url-field-preview", UrlFieldPreview);
Vue.component("k-users-field-preview", UsersFieldPreview);

/* Layout */
import Bar from "@/components/Layout/Bar.vue";
import Box from "@/components/Layout/Box.vue";
import Card from "@/components/Layout/Card.vue";
import Cards from "@/components/Layout/Cards.vue";
import Collection from "@/components/Layout/Collection.vue";
import Column from "@/components/Layout/Column.vue";
import Dropzone from "@/components/Layout/Dropzone.vue";
import Empty from "@/components/Layout/Empty.vue";
import FilePreview from "@/components/Layout/FilePreview.vue";
import Grid from "@/components/Layout/Grid.vue";
import Header from "@/components/Layout/Header.vue";
import List from "@/components/Layout/List.vue";
import ListItem from "@/components/Layout/ListItem.vue";
import Tabs from "@/components/Layout/Tabs.vue";
import View from "@/components/Layout/View.vue";

Vue.component("k-bar", Bar);
Vue.component("k-box", Box);
Vue.component("k-card", Card);
Vue.component("k-cards", Cards);
Vue.component("k-collection", Collection);
Vue.component("k-column", Column);
Vue.component("k-dropzone", Dropzone);
Vue.component("k-empty", Empty);
Vue.component("k-file-preview", FilePreview);
Vue.component("k-grid", Grid);
Vue.component("k-header", Header);
Vue.component("k-list", List);
Vue.component("k-list-item", ListItem);
Vue.component("k-tabs", Tabs);
Vue.component("k-view", View);

/* Misc */
import Draggable from "@/components/Misc/Draggable.vue";
import ErrorBoundary from "@/components/Misc/ErrorBoundary.vue";
import Headline from "@/components/Misc/Headline.vue";
import Icon from "@/components/Misc/Icon.vue";
import Image from "@/components/Misc/Image.vue";
import Progress from "@/components/Misc/Progress.vue";
import SortHandle from "@/components/Misc/SortHandle.vue";
import Text from "@/components/Misc/Text.vue";

Vue.component("k-draggable", Draggable);
Vue.component("k-error-boundary", ErrorBoundary);
Vue.component("k-headline", Headline);
Vue.component("k-icon", Icon);
Vue.component("k-image", Image);
Vue.component("k-progress", Progress);
Vue.component("k-sort-handle", SortHandle);
Vue.component("k-text", Text);

/* Navigation */
import Button from "@/components/Navigation/Button.vue";
import ButtonGroup from "@/components/Navigation/ButtonGroup.vue";
import Dropdown from "@/components/Navigation/Dropdown.vue";
import DropdownContent from "@/components/Navigation/DropdownContent.vue";
import DropdownItem from "@/components/Navigation/DropdownItem.vue";
import Link from "@/components/Navigation/Link.vue";
import Languages from "@/components/Navigation/Languages.vue";
import Pagination from "@/components/Navigation/Pagination.vue";
import PrevNext from "@/components/Navigation/PrevNext.vue";
import Tag from "@/components/Navigation/Tag.vue";
import Topbar from "@/components/Navigation/Topbar.vue";

Vue.component("k-button", Button);
Vue.component("k-button-group", ButtonGroup);
Vue.component("k-dropdown", Dropdown);
Vue.component("k-dropdown-content", DropdownContent);
Vue.component("k-dropdown-item", DropdownItem);
Vue.component("k-languages-dropdown", Languages);
Vue.component("k-link", Link);
Vue.component("k-pagination", Pagination);
Vue.component("k-prev-next", PrevNext);
Vue.component("k-tag", Tag);
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
