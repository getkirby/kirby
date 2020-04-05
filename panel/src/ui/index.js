/** Plugins */
import events from "./plugins/events.js";
import helpers from "./plugins/helpers.js";

/** Components */
import Bar from "./components/Bar.vue";
import Box from "./components/Box.vue";
import Button from "./components/Button.vue";
import ButtonDisabled from "./components/ButtonDisabled.vue";
import ButtonGroup from "./components/ButtonGroup.vue";
import ButtonLink from "./components/ButtonLink.vue";
import ButtonNative from "./components/ButtonNative.vue";
import Column from "./components/Column.vue";
import Dialog from "./components/Dialog.vue";
import Draggable from "./components/Draggable.vue";
import Dropdown from "./components/Dropdown.vue";
import DropdownContent from "./components/DropdownContent.vue";
import DropdownItem from "./components/DropdownItem.vue";
import Dropzone from "./components/Dropzone.vue";
import Empty from "./components/Empty.vue";
import Grid from "./components/Grid.vue";
import Headline from "./components/Headline.vue";
import Header from "./components/Header.vue";
import Icon from "./components/Icon.vue";
import Image from "./components/Image.vue";
import Link from "./components/Link.vue";
import Pagination from "./components/Pagination.vue";
import PrevNext from "./components/PrevNext.vue";
import Progress from "./components/Progress.vue";
import SortHandle from "./components/SortHandle.vue";
import Tag from "./components/Tag.vue";
import Text from "./components/Text.vue";

export default {
  install(Vue) {
    /** Plugins */
    Vue.use(events);
    Vue.use(helpers);

    Vue.prototype.$t = function(string) {
      return string;
    };

    /** Components */
    Vue.component("k-bar", Bar);
    Vue.component("k-box", Box);
    Vue.component("k-button", Button);
    Vue.component("k-button-disabled", ButtonDisabled);
    Vue.component("k-button-group", ButtonGroup);
    Vue.component("k-button-link", ButtonLink);
    Vue.component("k-button-native", ButtonNative);
    Vue.component("k-column", Column);
    Vue.component("k-dialog", Dialog);
    Vue.component("k-draggable", Draggable);
    Vue.component("k-dropdown", Dropdown);
    Vue.component("k-dropdown-content", DropdownContent);
    Vue.component("k-dropdown-item", DropdownItem);
    Vue.component("k-dropzone", Dropzone);
    Vue.component("k-empty", Empty);
    Vue.component("k-grid", Grid);
    Vue.component("k-headline", Headline);
    Vue.component("k-header", Header);
    Vue.component("k-icon", Icon);
    Vue.component("k-image", Image);
    Vue.component("k-link", Link);
    Vue.component("k-pagination", Pagination);
    Vue.component("k-prev-next", PrevNext);
    Vue.component("k-progress", Progress);
    Vue.component("k-sort-handle", SortHandle);
    Vue.component("k-tag", Tag);
    Vue.component("k-text", Text);
  }
};
