import Vue from "vue";

// main components
import Block from "./Block.vue";
import Blocks from "./Blocks.vue";

Vue.component("k-block", Block);
Vue.component("k-blocks", Blocks);

// helper components
import BlockFigure from "./BlockFigure.vue";
import BlockOptions from "./BlockOptions.vue";
import BlockSelector from "./BlockSelector.vue";
import BlockTitle from "./BlockTitle.vue";
import BlockType from "./BlockType.vue";

Vue.component("k-block-figure", BlockFigure);
Vue.component("k-block-options", BlockOptions);
Vue.component("k-block-selector", BlockSelector);
Vue.component("k-block-title", BlockTitle);
Vue.component("k-block-type", BlockType);

// block types
const components = import.meta.globEager("./Types/*.vue");

Object.keys(components).map((key) => {
  // get name and type by filename
  const name = key.match(/\/([a-zA-Z]*)\.vue/)[1];
  const type = name.toLowerCase();

  // load the component
  let component = components[key].default;

  // extend the component with the block abstract
  component.extends = BlockType;

  // globally define the block type component
  Vue.component("k-block-type-" + type, component);
});
