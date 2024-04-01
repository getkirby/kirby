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
			const handle = this.handle === true ? ".k-sort-handle" : this.handle;

			return {
				group: this.group,
				disabled: this.disabled,
				handle: handle,
				draggable: ">*",
				dataIdAttr: "data-id",
				filter: ".k-draggable-footer",
				ghostClass: "k-sortable-ghost",
				fallbackClass: "k-sortable-fallback",
				forceFallback: true,
				fallbackOnBody: true
				// scroll: document.querySelector(".k-panel-main"),
				// ...this.options
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
				onStart: (event) => {
					this.$panel.drag.start("data", {});
					this.$emit("start", event);
				},
				onEnd: (event) => {
					this.$panel.drag.stop();
					this.$emit("end", event);
				},
				onSort: (event) => {
					this.$emit("sort", event);
				},
				onMove: (event, originalEvent) => {
					// ensure footer stays non-sortable at the bottom
					if (originalEvent.target === this.$refs.footer) {
						return -1;
					}

					if (this.move) {
						return this.move(event, originalEvent);
					}

					return;
				},
				onChange: (event) => {
					this.$emit("change", event);
				}
			});
		}
	}
};
</script>
