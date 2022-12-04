<template>
	<draggable
		v-bind="dragOptions"
		:component-data="data"
		:tag="element"
		:list="list"
		:move="move"
		class="k-draggable"
		@add="$emit('add', $event)"
		@change="$emit('change', $event)"
		@clone="$emit('clone', $event)"
		@choose="$emit('choose', $event)"
		@end="onEnd"
		@filter="$emit('filter', $event)"
		@remove="$emit('remove', $event)"
		@sort="$emit('sort', $event)"
		@start="onStart"
		@unchoose="$emit('unchoose', $event)"
		@update="$emit('update', $event)"
	>
		<slot />
		<template #footer>
			<slot name="footer" />
		</template>
	</draggable>
</template>

<script>
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
		draggable: () => import("vuedraggable/src/vuedraggable")
	},
	props: {
		data: Object,
		/**
		 * HTML element for the wrapper
		 */
		element: String,
		/**
		 * Whether to use a sort handle
		 * or, if yes, which CSS selector
		 * can be used
		 */
		handle: [String, Boolean],
		list: [Array, Object],
		move: Function,
		options: Object
	},
	emits: [
		"start",
		"add",
		"remove",
		"update",
		"end",
		"choose",
		"unchoose",
		"sort",
		"filter",
		"clone"
	],
	computed: {
		dragOptions() {
			let handle = false;

			if (this.handle === true) {
				handle = ".k-sort-handle";
			} else {
				handle = this.handle;
			}

			return {
				fallbackClass: "k-sortable-fallback",
				fallbackOnBody: true,
				forceFallback: true,
				ghostClass: "k-sortable-ghost",
				handle: handle,
				scroll: document.querySelector(".k-panel-view"),
				...this.options
			};
		}
	},
	methods: {
		onStart(event) {
			this.$store.dispatch("drag", {});
			this.$emit("start", event);
		},
		onEnd(event) {
			this.$store.dispatch("drag", null);
			this.$emit("end", event);
		}
	}
};
</script>
