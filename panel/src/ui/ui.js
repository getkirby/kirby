/* Dependencies */
import Vue from "vue";
import Vuelidate from "vuelidate";
import Events from "./plugins/events.js";

Vue.use(Vuelidate);
Vue.use(Events);

/* Components */
import Bar from "./components/Layout/Bar.vue";
import Box from "./components/Layout/Box.vue";
import Button from "./components/Navigation/Button.vue";
import ButtonGroup from "./components/Navigation/ButtonGroup.vue";
import Calendar from "./components/Forms/Calendar.vue";
import Card from "./components/Layout/Card.vue";
import Cards from "./components/Layout/Cards.vue";
import Collection from "./components/Layout/Collection.vue";
import Column from "./components/Layout/Column.vue";
import Counter from "./components/Forms/Counter.vue";
import Dialog from "./components/Navigation/Dialog.vue";
import Draggable from "./components/Misc/Draggable.vue";
import Dropdown from "./components/Navigation/Dropdown.vue";
import DropdownContent from "./components/Navigation/DropdownContent.vue";
import DropdownItem from "./components/Navigation/DropdownItem.vue";
import Empty from "./components/Layout/Empty.vue";
import ErrorBoundary from "./components/Misc/ErrorBoundary.vue";
import Grid from "./components/Layout/Grid.vue";
import Header from "./components/Layout/Header.vue";
import Headline from "./components/Misc/Headline.vue";
import Icon from "./components/Misc/Icon.vue";
import Image from "./components/Misc/Image.vue";
import Link from "./components/Navigation/Link.vue";
import List from "./components/Layout/List.vue";
import ListItem from "./components/Layout/ListItem.vue";
import Pagination from "./components/Navigation/Pagination.vue";
import PrevNext from "./components/Navigation/PrevNext.vue";
import Progress from "./components/Misc/Progress.vue";
import SortHandle from "./components/Misc/SortHandle.vue";
import Tag from "./components/Navigation/Tag.vue";
import Text from "./components/Misc/Text.vue";
import View from "./components/Layout/View.vue";

/** FORMS */

/** Special elements */
import Autocomplete from "./components/Forms/Autocomplete.vue";
import Form from "./components/Forms/Form.vue";
import Field from "./components/Forms/Field.vue";
import Fieldset from "./components/Forms/Fieldset.vue";
import Input from "./components/Forms/Input.vue";
import Upload from "./components/Forms/Upload.vue";

/** Form Inputs */
import CheckboxInput from "./components/Forms/Input/CheckboxInput.vue";
import CheckboxesInput from "./components/Forms/Input/CheckboxesInput.vue";
import DateInput from "./components/Forms/Input/DateInput.vue";
import DateTimeInput from "./components/Forms/Input/DateTimeInput.vue";
import EmailInput from "./components/Forms/Input/EmailInput.vue";
import MultiselectInput from "./components/Forms/Input/MultiselectInput.vue";
import NumberInput from "./components/Forms/Input/NumberInput.vue";
import PasswordInput from "./components/Forms/Input/PasswordInput.vue";
import RadioInput from "./components/Forms/Input/RadioInput.vue";
import RangeInput from "./components/Forms/Input/RangeInput.vue";
import SelectInput from "./components/Forms/Input/SelectInput.vue";
import TagsInput from "./components/Forms/Input/TagsInput.vue";
import TelInput from "./components/Forms/Input/TelInput.vue";
import TextInput from "./components/Forms/Input/TextInput.vue";
import TextareaInput from "./components/Forms/Input/TextareaInput.vue";
import TimeInput from "./components/Forms/Input/TimeInput.vue";
import ToggleInput from "./components/Forms/Input/ToggleInput.vue";
import UrlInput from "./components/Forms/Input/UrlInput.vue";

/** Form Fields */
import CheckboxesField from "./components/Forms/Field/CheckboxesField.vue";
import DateField from "./components/Forms/Field/DateField.vue";
import EmailField from "./components/Forms/Field/EmailField.vue";
import FilesField from "./components/Forms/Field/FilesField.vue";
import HeadlineField from "./components/Forms/Field/HeadlineField.vue";
import InfoField from "./components/Forms/Field/InfoField.vue";
import LineField from "./components/Forms/Field/LineField.vue";
import MultiselectField from "./components/Forms/Field/MultiselectField.vue";
import NumberField from "./components/Forms/Field/NumberField.vue";
import PagesField from "./components/Forms/Field/PagesField.vue";
import PasswordField from "./components/Forms/Field/PasswordField.vue";
import RadioField from "./components/Forms/Field/RadioField.vue";
import RangeField from "./components/Forms/Field/RangeField.vue";
import SelectField from "./components/Forms/Field/SelectField.vue";
import StructureField from "./components/Forms/Field/StructureField.vue";
import TagsField from "./components/Forms/Field/TagsField.vue";
import TelField from "./components/Forms/Field/TelField.vue";
import TextField from "./components/Forms/Field/TextField.vue";
import TextareaField from "./components/Forms/Field/TextareaField.vue";
import TimeField from "./components/Forms/Field/TimeField.vue";
import ToggleField from "./components/Forms/Field/ToggleField.vue";
import UrlField from "./components/Forms/Field/UrlField.vue";
import UsersField from "./components/Forms/Field/UsersField.vue";

/* Form Field Previews */
import FilesFieldPreview from "./components/Forms/Previews/FilesFieldPreview.vue";
import EmailFieldPreview from "./components/Forms/Previews/EmailFieldPreview.vue";
import PagesFieldPreview from "./components/Forms/Previews/PagesFieldPreview.vue";
import UrlFieldPreview from "./components/Forms/Previews/UrlFieldPreview.vue";
import UsersFieldPreview from "./components/Forms/Previews/UsersFieldPreview.vue";

export default {
  install(Vue) {
    // default translate filter for Ui components
    Vue.filter("t", function(fallback) {
      return fallback;
    });

    // tab directive
    Vue.directive("tab", {
      inserted: el => {
        el.addEventListener("keyup", e => {
          if (e.keyCode === 9) {
            el.dataset.tabbed = true;
          }
        });
        el.addEventListener("blur", () => {
          delete el.dataset.tabbed;
        });
      }
    });

    Vue.component("k-bar", Bar);
    Vue.component("k-box", Box);
    Vue.component("k-button", Button);
    Vue.component("k-button-group", ButtonGroup);
    Vue.component("k-calendar", Calendar);
    Vue.component("k-card", Card);
    Vue.component("k-cards", Cards);
    Vue.component("k-collection", Collection);
    Vue.component("k-column", Column);
    Vue.component("k-counter", Counter);
    Vue.component("k-dialog", Dialog);
    Vue.component("k-draggable", Draggable);
    Vue.component("k-dropdown", Dropdown);
    Vue.component("k-dropdown-content", DropdownContent);
    Vue.component("k-dropdown-item", DropdownItem);
    Vue.component("k-empty", Empty);
    Vue.component("k-error-boundary", ErrorBoundary);
    Vue.component("k-grid", Grid);
    Vue.component("k-header", Header);
    Vue.component("k-headline", Headline);
    Vue.component("k-icon", Icon);
    Vue.component("k-image", Image);
    Vue.component("k-link", Link);
    Vue.component("k-list", List);
    Vue.component("k-list-item", ListItem);
    Vue.component("k-pagination", Pagination);
    Vue.component("k-prev-next", PrevNext);
    Vue.component("k-progress", Progress);
    Vue.component("k-sort-handle", SortHandle);
    Vue.component("k-tag", Tag);
    Vue.component("k-text", Text);
    Vue.component("k-view", View);

    /** Forms */
    Vue.component("k-autocomplete", Autocomplete);
    Vue.component("k-form", Form);
    Vue.component("k-field", Field);
    Vue.component("k-fieldset", Fieldset);
    Vue.component("k-input", Input);
    Vue.component("k-upload", Upload);

    /** Form inputs */
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

    /** Form fields */
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

    /** Form field previews */
    Vue.component("k-email-field-preview", EmailFieldPreview);
    Vue.component("k-files-field-preview", FilesFieldPreview);
    Vue.component("k-pages-field-preview", PagesFieldPreview);
    Vue.component("k-url-field-preview", UrlFieldPreview);
    Vue.component("k-users-field-preview", UsersFieldPreview);
  }
};
