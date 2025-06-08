import ArrayFieldPreview from "./ArrayFieldPreview.vue";
import BubblesFieldPreview from "./BubblesFieldPreview.vue";
import ColorFieldPreview from "./ColorFieldPreview.vue";
import DateFieldPreview from "./DateFieldPreview.vue";
import EmailFieldPreview from "./EmailFieldPreview.vue";
import FilesFieldPreview from "./FilesFieldPreview.vue";
import FlagFieldPreview from "./FlagFieldPreview.vue";
import HtmlFieldPreview from "./HtmlFieldPreview.vue";
import ImageFieldPreview from "./ImageFieldPreview.vue";
import LinkFieldPreview from "./LinkFieldPreview.vue";
import ObjectFieldPreview from "./ObjectFieldPreview.vue";
import PagesFieldPreview from "./PagesFieldPreview.vue";
import TagFieldPreview from "./TagFieldPreview.vue";
import TagsFieldPreview from "./TagsFieldPreview.vue";
import TextFieldPreview from "./TextFieldPreview.vue";
import TimeFieldPreview from "./TimeFieldPreview.vue";
import ToggleFieldPreview from "./ToggleFieldPreview.vue";
import UrlFieldPreview from "./UrlFieldPreview.vue";
import UsersFieldPreview from "./UsersFieldPreview.vue";

export default {
	install(app) {
		app.component("k-array-field-preview", ArrayFieldPreview);
		app.component("k-bubbles-field-preview", BubblesFieldPreview);
		app.component("k-color-field-preview", ColorFieldPreview);
		app.component("k-date-field-preview", DateFieldPreview);
		app.component("k-email-field-preview", EmailFieldPreview);
		app.component("k-files-field-preview", FilesFieldPreview);
		app.component("k-flag-field-preview", FlagFieldPreview);
		app.component("k-html-field-preview", HtmlFieldPreview);
		app.component("k-image-field-preview", ImageFieldPreview);
		app.component("k-link-field-preview", LinkFieldPreview);
		app.component("k-object-field-preview", ObjectFieldPreview);
		app.component("k-pages-field-preview", PagesFieldPreview);
		app.component("k-tag-field-preview", TagFieldPreview);
		app.component("k-tags-field-preview", TagsFieldPreview);
		app.component("k-text-field-preview", TextFieldPreview);
		app.component("k-toggle-field-preview", ToggleFieldPreview);
		app.component("k-time-field-preview", TimeFieldPreview);
		app.component("k-url-field-preview", UrlFieldPreview);
		app.component("k-users-field-preview", UsersFieldPreview);

		/** Extensions **/
		app.component("k-list-field-preview", HtmlFieldPreview);
		app.component("k-writer-field-preview", HtmlFieldPreview);

		app.component("k-checkboxes-field-preview", TagsFieldPreview);
		app.component("k-multiselect-field-preview", TagsFieldPreview);
		app.component("k-radio-field-preview", TagsFieldPreview);
		app.component("k-select-field-preview", TagsFieldPreview);
		app.component("k-toggles-field-preview", TagsFieldPreview);
	}
};
