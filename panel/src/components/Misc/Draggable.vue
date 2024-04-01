<template>
	<component :is="element" class="k-draggable">
		<!-- @slot Items to be sortable via drag and drop -->
		<slot />

		<footer v-if="$slots.footer" ref="footer" class="k-draggable-footer">
			<!-- @slot Non-sortable footer -->
			<slot name="footer" />
		</footer>
	</component>
</template>

<script>
import Sortable from "sortablejs";

/**
 * The Draggable component implements the widespread
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
	props: {
		data: Object,
		/**
		 * Whether sorting is disabled
		 */
		disabled: Boolean,
		/**
		 * HTML element for the wrapper
		 */
		element: {
			type: String,
			default: "div"
		},
		/**
		 * Group name for sorting between lists
		 */
		group: String,
		/**
		 * Whether to show a sort handle or, if yes,
		 * which CSS selector to use
		 */
		handle: [String, Boolean],
		/**
		 * Array of items to sync when sorting
		 */
		list: Array,
		move: Function,
		options: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["change", "end", "sort", "start"],
	data() {
		return {
			sortable: null
		};
	},
	computed: {
		dragOptions() {
			return {
				group: this.group,
				disabled: this.disabled,
				handle: this.handle === true ? ".k-sort-handle" : this.handle,
				draggable: ">*",
				filter: ".k-draggable-footer",
				ghostClass: "k-sortable-ghost",
				fallbackClass: "k-sortable-fallback",
				forceFallback: true,
				fallbackOnBody: true,
				// scroll: document.querySelector(".k-panel-main"),
				...this.options
			};
		}
	},
	watch: {
		dragOptions: {
			handler() {
				this.create();
			},
			deep: true
		}
	},
	mounted() {
		this.create();
	},
	methods: {
		create() {
			this.sortable = Sortable.create(this.$el, {
				...this.dragOptions,
				// Element dragging started
				onStart: (event) => {
					this.$panel.drag.start("data", {});
					this.$emit("start", event);
				},
				// Element dragging ended
				onEnd: (event) => {
					this.$panel.drag.stop();
					this.$emit("end", event);
				},
				// Element is dropped into the list from another list
				onAdd: (evt) => {
					if (this.list) {
						const source = this.getInstance(evt.from);
						const element = source.list[evt.oldDraggableIndex];
						this.list.splice(evt.newDraggableIndex, 0, element);
					}
				},
				// Changed sorting within list
				onUpdate: (evt) => {
					if (this.list) {
						const element = this.list[evt.oldDraggableIndex];
						this.list.splice(evt.oldDraggableIndex, 1);
						this.list.splice(evt.newDraggableIndex, 0, element);
					}
				},
				// Element is removed from the list into another list
				onRemove: (evt) => {
					if (this.list) {
						this.list.splice(evt.oldDraggableIndex, 1);
					}
				},

				// Called by any change to the list
				onSort: (event) => {
					this.$emit("sort", event);
				},

				// Event when you move an item in the list or between lists
				onMove: (event, originalEvent) => {
					// ensure footer stays non-sortable at the bottom
					if (originalEvent.target === this.$refs.footer) {
						return -1;
					}

					if (this.move) {
						const form = this.getInstance(event.from);
						event.fromData = form.$props.data;
						const to = this.getInstance(event.to);
						event.toData = to.$props.data;
						console.log(event);

						return this.move(event);
					}

					return true;
				},
				// Called when dragging element changes position
				onChange: (event) => {
					this.$emit("change", event);
				}
			});
		},
		getInstance(element) {
			element = element.__vue__;

			if ("list" in element) {
				return element;
			}

			if (element.$children.length === 1 && "list" in element.$children[0]) {
				return element.$children[0];
			}

			if (element.$parent.$options._componentTag === "k-draggable") {
				return element.$parent;
			}
		}
	}
};
</script>
