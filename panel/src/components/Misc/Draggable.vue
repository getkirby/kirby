<template>
	<component :is="element" :class="{ 'k-draggable': !dragOptions.disabled }">
		<!-- @slot Items to be sortable via drag and drop -->
		<slot />

		<footer v-if="$slots.footer" ref="footer" class="k-draggable-footer">
			<!-- @slot Non-sortable footer -->
			<slot name="footer" />
		</footer>
	</component>
</template>

<script>
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
		/**
		 * Data to bind to the event object
		 */
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
		/**
		 * Callback to determine if an item can be moved
		 */
		move: Function,
		/**
		 * Custom options for Sortable.js
		 */
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
				scroll: document.querySelector(".k-panel-main"),
				...this.options
			};
		}
	},
	watch: {
		dragOptions: {
			handler(newOptions, oldOptions) {
				for (const option in newOptions) {
					if (newOptions[option] !== oldOptions[option]) {
						this.sortable.option(option, newOptions[option]);
					}
				}
			},
			deep: true
		}
	},
	mounted() {
		this.create();
	},
	methods: {
		async create() {
			const Sortable = (await import("sortablejs")).default;

			this.sortable = Sortable.create(this.$el, {
				...this.dragOptions,

				// Item dragging started
				onStart: (event) => {
					this.$panel.drag.start("data", {});
					this.$emit("start", event);
				},
				// Item dragging ended
				onEnd: (event) => {
					this.$panel.drag.stop();
					this.$emit("end", event);
				},
				// Item is dropped into the list from another list
				onAdd: (evt) => {
					if (this.list) {
						const source = this.getInstance(evt.from);
						const oldIndex = evt.oldDraggableIndex;
						const newIndex = evt.newDraggableIndex;
						const element = source.list[oldIndex];
						this.list.splice(newIndex, 0, element);
						this.$emit("change", { added: { element, newIndex } });
					}
				},
				// Changed sorting within list
				onUpdate: (evt) => {
					if (this.list) {
						const oldIndex = evt.oldDraggableIndex;
						const newIndex = evt.newDraggableIndex;
						const element = this.list[oldIndex];
						this.list.splice(oldIndex, 1);
						this.list.splice(newIndex, 0, element);
						this.$emit("change", { moved: { element, newIndex, oldIndex } });
					}
				},
				// Item is removed from the list into another list
				onRemove: (evt) => {
					if (this.list) {
						const oldIndex = evt.oldDraggableIndex;
						const element = this.list[oldIndex];
						this.list.splice(oldIndex, 1);
						this.$emit("change", { removed: { element, oldIndex } });
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
						// bind data prop of the source and target component
						// to the event object
						const form = this.getInstance(event.from);
						event.fromData = form.$props.data;
						const to = this.getInstance(event.to);
						event.toData = to.$props.data;

						// call the provided move callback
						// to determine if the move is allowed
						return this.move(event);
					}

					return true;
				}
			});
		},
		getInstance(element) {
			// get the Vue instance from HTMLElement
			element = element.__vue__;

			// if the element is already the draggable component
			if ("list" in element) {
				return element;
			}

			// check if only child is the draggable component
			if (element.$children.length === 1 && "list" in element.$children[0]) {
				return element.$children[0];
			}

			// check if parent is the draggable component
			if (element.$parent.$options._componentTag === "k-draggable") {
				return element.$parent;
			}
		}
	}
};
</script>
