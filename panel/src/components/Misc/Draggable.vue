<template>
	<draggable
		v-bind="dragOptions"
		:component-data="data"
		:tag="element"
		:list="list"
		:move="move"
		item-key="id"
		class="k-draggable"
		@change="$emit('change', $event)"
		@end="onEnd"
		@sort="$emit('sort', $event)"
		@start="onStart"
	>
		<!-- @slot Items to be sortable via drag and drop -->
		<template #item="{ element }">
			<slot />
		</template>

		<template #footer>
			<!-- @slot Non-sortable footer -->
			<slot name="footer" />
		</template>
	</draggable>
</template>

<script>
// TODO: turn into a dynmic import again
import Draggable from "vuedraggable/src/vuedraggable";

/**
 * The Draggable component implements the
 * [Vue.Draggable](https://github.com/SortableJS/Vue.Draggable)
 * library which is a wrapper for the widespread
 * [Sortable.js](https://github.com/RubaXa/Sortable) library.
 *
 * @example
 * <k-draggable>
 *   <li>Drag me.</li>
 *   <li>Or me.</li>
 *   <li>Drop it!</li>
 * </k-draggable>
 */
export default {
	components: {
		draggable: Draggable
	},
	props: {
		data: Object,
		/**
		 * HTML element for the wrapper
		 */
		element: {
			type: String,
			default: "div"
		},
		/**
		 * Whether to show a sort handle or, if yes,
		 * which CSS selector to use
		 */
		handle: [String, Boolean],
		/**
		 * Array/object of items to sync when sorting
		 */
		list: [Array, Object],
		move: Function,
		options: Object
	},
	emits: ["change", "end", "sort", "start"],
	computed: {
		dragOptions() {
			let handle = this.handle;

			if (handle === true) {
				handle = ".k-sort-handle";
			}

			return {
				fallbackClass: "k-sortable-fallback",
				fallbackOnBody: true,
				forceFallback: true,
				ghostClass: "k-sortable-ghost",
				handle: handle,
				scroll: document.querySelector(".k-panel-main"),
				...this.options
			};
		}
	},
	methods: {
		onStart(event) {
			this.$panel.drag.start("data", {});
			this.$emit("start", event);
		},
		onEnd(event) {
			this.$panel.drag.stop();
			this.$emit("end", event);
		}
	}
};
</script>
