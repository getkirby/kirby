// main components
import Block from "./Block.vue";
import Blocks from "./Blocks.vue";

// helper components
import BlockFigure from "./BlockFigure.vue";
import BlockOptions from "./BlockOptions.vue";
import BlockPasteboard from "./BlockPasteboard.vue";
import BlockSelector from "./BlockSelector.vue";
import BlockTitle from "./BlockTitle.vue";
import BlockType from "./BlockType.vue";

export default {
	install(app) {
		app.component("k-block", Block);
		app.component("k-blocks", Blocks);

		app.component("k-block-figure", BlockFigure);
		app.component("k-block-options", BlockOptions);
		app.component("k-block-pasteboard", BlockPasteboard);
		app.component("k-block-selector", BlockSelector);
		app.component("k-block-title", BlockTitle);
		app.component("k-block-type", BlockType);

		// block types
		const components = import.meta.glob("./Types/*.vue", { eager: true });

		for (const key in components) {
			// get name and type by filename
			const name = key.match(/\/([a-zA-Z]*)\.vue/)[1];
			const type = name.toLowerCase();

			// load the component
			let component = components[key].default;

			// extend the component with the block abstract
			component.extends = BlockType;

			// globally define the block type component
			app.component("k-block-type-" + type, component);
		}
	}
};
