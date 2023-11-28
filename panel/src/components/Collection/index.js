import Collection from "./Collection.vue";
import Empty from "./Empty.vue";
import Item from "./Item.vue";
import ItemImage from "./ItemImage.vue";
import Items from "./Items.vue";

export default {
	install(app) {
		app.component("k-collection", Collection);
		app.component("k-empty", Empty);
		app.component("k-item", Item);
		app.component("k-item-image", ItemImage);
		app.component("k-items", Items);
	}
};
