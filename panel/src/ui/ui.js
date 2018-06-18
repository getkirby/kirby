/* Dependencies */
import Vue from "vue";
import Vuelidate from "vuelidate";
import Draggable from "vuedraggable";
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
import Dropdown from "./components/Navigation/Dropdown.vue";
import DropdownContent from "./components/Navigation/DropdownContent.vue";
import DropdownItem from "./components/Navigation/DropdownItem.vue";
import Grid from "./components/Layout/Grid.vue";
import Header from "./components/Layout/Header.vue";
import Headline from "./components/Misc/Headline.vue";
import Icon from "./components/Misc/Icon.vue";
import Image from "./components/Misc/Image.vue";
import Link from "./components/Navigation/Link.vue";
import List from "./components/Layout/List.vue";
import ListItem from "./components/Layout/ListItem.vue";
import Pagination from "./components/Navigation/Pagination.vue";
import Progress from "./components/Misc/Progress.vue";
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
import HeadlineField from "./components/Forms/Field/HeadlineField.vue";
import InfoField from "./components/Forms/Field/InfoField.vue";
import LineField from "./components/Forms/Field/LineField.vue";
import NumberField from "./components/Forms/Field/NumberField.vue";
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
import UserField from "./components/Forms/Field/UserField.vue";

export default {
  install(Vue) {

    // default translate filter for Ui components
    Vue.filter("t", function (fallback) {
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

    Vue.component("kirby-bar", Bar);
    Vue.component("kirby-box", Box);
    Vue.component("kirby-button", Button);
    Vue.component("kirby-button-group", ButtonGroup);
    Vue.component("kirby-calendar", Calendar);
    Vue.component("kirby-card", Card);
    Vue.component("kirby-cards", Cards);
    Vue.component("kirby-collection", Collection);
    Vue.component("kirby-column", Column);
    Vue.component("kirby-counter", Counter);
    Vue.component("kirby-dialog", Dialog);
    Vue.component("kirby-draggable", Draggable);
    Vue.component("kirby-dropdown", Dropdown);
    Vue.component("kirby-dropdown-content", DropdownContent);
    Vue.component("kirby-dropdown-item", DropdownItem);
    Vue.component("kirby-grid", Grid);
    Vue.component("kirby-header", Header);
    Vue.component("kirby-headline", Headline);
    Vue.component("kirby-icon", Icon);
    Vue.component("kirby-image", Image);
    Vue.component("kirby-link", Link);
    Vue.component("kirby-list", List);
    Vue.component("kirby-list-item", ListItem);
    Vue.component("kirby-pagination", Pagination);
    Vue.component("kirby-progress", Progress);
    Vue.component("kirby-tag", Tag);
    Vue.component("kirby-text", Text);
    Vue.component("kirby-view", View);

    /** FORMS */
    Vue.component("kirby-autocomplete", Autocomplete);
    Vue.component("kirby-form", Form);
    Vue.component("kirby-field", Field);
    Vue.component("kirby-fieldset", Fieldset);
    Vue.component("kirby-input", Input);
    Vue.component("kirby-upload", Upload);

    /** Form inputs */
    Vue.component("kirby-checkbox-input", CheckboxInput);
    Vue.component("kirby-checkboxes-input", CheckboxesInput);
    Vue.component("kirby-date-input", DateInput);
    Vue.component("kirby-datetime-input", DateTimeInput);
    Vue.component("kirby-email-input", EmailInput);
    Vue.component("kirby-number-input", NumberInput);
    Vue.component("kirby-password-input", PasswordInput);
    Vue.component("kirby-radio-input", RadioInput);
    Vue.component("kirby-range-input", RangeInput);
    Vue.component("kirby-select-input", SelectInput);
    Vue.component("kirby-tags-input", TagsInput);
    Vue.component("kirby-tel-input", TelInput);
    Vue.component("kirby-text-input", TextInput);
    Vue.component("kirby-textarea-input", TextareaInput);
    Vue.component("kirby-time-input", TimeInput);
    Vue.component("kirby-toggle-input", ToggleInput);
    Vue.component("kirby-url-input", UrlInput);

    /** Form fields */
    Vue.component("kirby-checkboxes-field", CheckboxesField);
    Vue.component("kirby-date-field", DateField);
    Vue.component("kirby-email-field", EmailField);
    Vue.component("kirby-headline-field", HeadlineField);
    Vue.component("kirby-info-field", InfoField);
    Vue.component("kirby-line-field", LineField);
    Vue.component("kirby-number-field", NumberField);
    Vue.component("kirby-password-field", PasswordField);
    Vue.component("kirby-radio-field", RadioField);
    Vue.component("kirby-range-field", RangeField);
    Vue.component("kirby-select-field", SelectField);
    Vue.component("kirby-structure-field", StructureField);
    Vue.component("kirby-tags-field", TagsField);
    Vue.component("kirby-text-field", TextField);
    Vue.component("kirby-textarea-field", TextareaField);
    Vue.component("kirby-tel-field", TelField);
    Vue.component("kirby-time-field", TimeField);
    Vue.component("kirby-toggle-field", ToggleField);
    Vue.component("kirby-url-field", UrlField);
    Vue.component("kirby-user-field", UserField);

  }
};
