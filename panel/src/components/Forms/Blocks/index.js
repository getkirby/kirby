// main components
import Block from "./Block.vue";
import Blocks from "./Blocks.vue";

// helper components
import BlockOptions from "./BlockOptions.vue";
import BlockPasteboard from "./BlockPasteboard.vue";
import BlockSelector from "./BlockSelector.vue";

// elements
import BlockBackgroundDropdown from "./Elements/BlockBackgroundDropdown.vue";
import BlockFigure from "./Elements/BlockFigure.vue";
import BlockFigureCaption from "./Elements/BlockFigureCaption.vue";
import BlockTitle from "./Elements/BlockTitle.vue";

// types
import BlockTypeCode from "./Types/Code.vue";
import BlockTypeDefault from "./Types/Default.vue";
import BlockTypeFields from "./Types/Fields.vue";
import BlockTypeGallery from "./Types/Gallery.vue";
import BlockTypeHeading from "./Types/Heading.vue";
import BlockTypeImage from "./Types/Image.vue";
import BlockTypeLine from "./Types/Line.vue";
import BlockTypeList from "./Types/List.vue";
import BlockTypeMarkdown from "./Types/Markdown.vue";
import BlockTypeQuote from "./Types/Quote.vue";
import BlockTypeTable from "./Types/Table.vue";
import BlockTypeText from "./Types/Text.vue";
import BlockTypeVideo from "./Types/Video.vue";

export default {
	install(app) {
		app.component("k-block", Block);
		app.component("k-blocks", Blocks);

		app.component("k-block-options", BlockOptions);
		app.component("k-block-pasteboard", BlockPasteboard);
		app.component("k-block-selector", BlockSelector);

		// elements
		app.component("k-block-background-dropdown", BlockBackgroundDropdown);
		app.component("k-block-figure", BlockFigure);
		app.component("k-block-figure-caption", BlockFigureCaption);
		app.component("k-block-title", BlockTitle);

		// block types
		app.component("k-block-type-code", BlockTypeCode);
		app.component("k-block-type-default", BlockTypeDefault);
		app.component("k-block-type-fields", BlockTypeFields);
		app.component("k-block-type-gallery", BlockTypeGallery);
		app.component("k-block-type-heading", BlockTypeHeading);
		app.component("k-block-type-image", BlockTypeImage);
		app.component("k-block-type-line", BlockTypeLine);
		app.component("k-block-type-list", BlockTypeList);
		app.component("k-block-type-markdown", BlockTypeMarkdown);
		app.component("k-block-type-quote", BlockTypeQuote);
		app.component("k-block-type-table", BlockTypeTable);
		app.component("k-block-type-text", BlockTypeText);
		app.component("k-block-type-video", BlockTypeVideo);
	}
};
