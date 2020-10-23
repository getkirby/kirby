import Vue from "vue";

// main components
import Block from "./Block.vue";
import Blocks from "./Blocks.vue";

Vue.component("k-block", Block);
Vue.component("k-blocks", Blocks);

// helper components
import BlockFigure from "./BlockFigure.vue";
import BlockForm from "./BlockForm.vue";
import BlockHeader from "./BlockHeader.vue";
import BlockOptions from "./BlockOptions.vue";
import BlockSelector from "./BlockSelector.vue";

Vue.component("k-block-figure", BlockFigure);
Vue.component("k-block-form", BlockForm);
Vue.component("k-block-header", BlockHeader);
Vue.component("k-block-options", BlockOptions);
Vue.component("k-block-selector", BlockSelector);

// block types
import Code from "./Types/Code.vue";
import Cta from "./Types/Cta.vue";
import Default from "./Types/Default.vue";
import Heading from "./Types/Heading.vue";
import Image from "./Types/Image.vue";
import Images from "./Types/Images.vue";
import Quote from "./Types/Quote.vue";
import Table from "./Types/Table.vue";
import Text from "./Types/Text.vue";
import Video from "./Types/Video.vue";

Vue.component("k-block-code", Code);
Vue.component("k-block-cta", Cta);
Vue.component("k-block-default", Default);
Vue.component("k-block-heading", Heading);
Vue.component("k-block-image", Image);
Vue.component("k-block-images", Images);
Vue.component("k-block-quote", Quote);
Vue.component("k-block-table", Table);
Vue.component("k-block-text", Text);
Vue.component("k-block-video", Video);
