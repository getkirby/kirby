import Vue from "vue";

/* Form Field Previews */
import ArrayFieldPreview from "./ArrayFieldPreview.vue";
import BubblesFieldPreview from "./BubblesFieldPreview.vue";
import DateFieldPreview from "./DateFieldPreview.vue";
import EmailFieldPreview from "./EmailFieldPreview.vue";
import FilesFieldPreview from "./FilesFieldPreview.vue";
import FlagFieldPreview from "./FlagFieldPreview.vue";
import HtmlFieldPreview from "./HtmlFieldPreview.vue";
import ImageFieldPreview from "./ImageFieldPreview.vue";
import PagesFieldPreview from "./PagesFieldPreview.vue";
import TextFieldPreview from "./TextFieldPreview.vue";
import TimeFieldPreview from "./TimeFieldPreview.vue";
import ToggleFieldPreview from "./ToggleFieldPreview.vue";
import UrlFieldPreview from "./UrlFieldPreview.vue";
import UsersFieldPreview from "./UsersFieldPreview.vue";

Vue.component("k-array-field-preview", ArrayFieldPreview);
Vue.component("k-bubbles-field-preview", BubblesFieldPreview);
Vue.component("k-date-field-preview", DateFieldPreview);
Vue.component("k-email-field-preview", EmailFieldPreview);
Vue.component("k-files-field-preview", FilesFieldPreview);
Vue.component("k-flag-field-preview", FlagFieldPreview);
Vue.component("k-html-field-preview", HtmlFieldPreview);
Vue.component("k-image-field-preview", ImageFieldPreview);
Vue.component("k-pages-field-preview", PagesFieldPreview);
Vue.component("k-text-field-preview", TextFieldPreview);
Vue.component("k-toggle-field-preview", ToggleFieldPreview);
Vue.component("k-time-field-preview", TimeFieldPreview);
Vue.component("k-url-field-preview", UrlFieldPreview);
Vue.component("k-users-field-preview", UsersFieldPreview);

/** Extensions **/
Vue.component("k-list-field-preview", HtmlFieldPreview);
Vue.component("k-writer-field-preview", HtmlFieldPreview);

Vue.component("k-checkboxes-field-preview", BubblesFieldPreview);
Vue.component("k-multiselect-field-preview", BubblesFieldPreview);
Vue.component("k-radio-field-preview", BubblesFieldPreview);
Vue.component("k-select-field-preview", BubblesFieldPreview);
Vue.component("k-tags-field-preview", BubblesFieldPreview);
Vue.component("k-toggles-field-preview", BubblesFieldPreview);
