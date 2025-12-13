import Collection from "./Collection.vue";
import CollectionLayout from "./CollectionLayout.vue";
import Empty from "./Empty.vue";
import Item from "./Item.vue";
import ItemImage from "./ItemImage.vue";
import Items from "./Items.vue";
import ItemsCollectionLayout from "./ItemsCollectionLayout.vue";
import TableCollectionLayout from "./TableCollectionLayout.vue";

export default {
	install(app) {
		app.component("k-collection", Collection);
		app.component("k-collection-layout", CollectionLayout);
		app.component("k-empty", Empty);
		app.component("k-item", Item);
		app.component("k-item-image", ItemImage);
		app.component("k-items", Items);
		app.component("k-items-collection-layout", ItemsCollectionLayout);
		app.component("k-table-collection-layout", TableCollectionLayout);
	}
};
