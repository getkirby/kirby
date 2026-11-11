<script>
import { h } from "vue";

/**
 * Renders as many items of the default slot as fit into
 * the element's width and the `fallback` slot for the rest.
 *
 * The element's width must not depend on its items, e.g.
 * a block-level `display: flex`. Items keep their natural
 * width, an item that shrinks counts as fitting.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	inheritAttrs: false,
	// `render()` passes the listeners to the root element
	preserveListeners: false,
	props: {
		/**
		 * Direction to collapse items and
		 * which side to place the fallback
		 * @values "start", "end"
		 */
		direction: {
			type: String,
			default: "end"
		},
		/**
		 * HTML element to use as container
		 */
		element: {
			type: String,
			default: "div"
		}
	},
	data() {
		return {
			// content box width from the observer, `null` until it reports one
			available: null,
			// whether the fallback is rendered
			isCollapsed: false,
			// keeps own renders from starting another calculation
			isMeasuring: false,
			// how many items to render, all until measured
			offset: Infinity,
			total: 0
		};
	},
	mounted() {
		this.observe();
	},
	unmounted() {
		this.unobserve();
	},
	updated() {
		// new slot content can change the widths
		this.calculate();
	},
	methods: {
		/**
		 * Measures the items and renders as many as fit,
		 * next to the fallback if not all of them do
		 * @returns {Promise<void>}
		 */
		async calculate() {
			if (this.available === null || this.isMeasuring === true) {
				return;
			}

			this.isMeasuring = true;

			// measure all items without the fallback
			this.isCollapsed = false;
			this.offset = Infinity;
			await this.$nextTick();

			// `normal` and the like count as no gap
			const gap = parseFloat(getComputedStyle(this.$el).columnGap) || 0;
			const widths = [...this.$el.children].map(this.measure);
			// tolerates subpixel rounding of the measured widths
			const space = this.available + 0.5;
			let count = this.fit(widths, gap, space);

			this.total = widths.length;
			this.isCollapsed =
				count < widths.length && this.$slots.fallback !== undefined;
			this.offset = count;
			await this.$nextTick();

			// the fallback's width can depend on the offset:
			// hide more items until everything rendered fits
			while (this.isCollapsed === true && count > 0) {
				const rendered = [...this.$el.children].map(this.measure);
				const extent = this.width(rendered, rendered.length, gap);

				if (extent <= space) {
					break;
				}

				const fallback = extent - this.width(widths, count, gap);
				count = Math.min(count - 1, this.fit(widths, gap, space - fallback));
				this.offset = count;
				await this.$nextTick();
			}

			this.isMeasuring = false;
		},
		/**
		 * Counts how many items fit into the space,
		 * taken from the end opposite to `direction`
		 * @param {number[]} widths Widths of all items
		 * @param {number} gap Gap between two items
		 * @param {number} space Width the items need to fit into
		 * @returns {number}
		 */
		fit(widths, gap, space) {
			let count = 0;

			while (
				count < widths.length &&
				this.width(widths, count + 1, gap) <= space
			) {
				count++;
			}

			return count;
		},
		/**
		 * Returns the rendered width of an element incl. its margins,
		 * an item that shrinks counts with its shrunk width
		 * @param {Element} element
		 * @returns {number}
		 */
		measure(element) {
			const style = getComputedStyle(element);
			const left = parseFloat(style.marginLeft) || 0;
			const right = parseFloat(style.marginRight) || 0;
			return element.getBoundingClientRect().width + left + right;
		},
		/**
		 * Starts listening to size changes of the element
		 */
		observe() {
			if (this.$el instanceof Element) {
				this.$panel.observers.resize.observe(this.$el);
				this.$el.addEventListener("resize", this.onResize);
			}
		},
		/**
		 * Recalculates when the element gets a new width,
		 * height changes from own renders don't matter
		 * @param {CustomEvent} event With the content box `width` and `height` as `detail`
		 */
		onResize(event) {
			if (event.detail.width !== this.available) {
				this.available = event.detail.width;
				this.calculate();
			}
		},
		/**
		 * Stops listening to size changes of the element
		 */
		unobserve() {
			if (this.$el instanceof Element) {
				this.$panel.observers.resize.unobserve(this.$el);
				this.$el.removeEventListener("resize", this.onResize);
			}
		},
		/**
		 * Sums up the widths of `count` items and the gaps between
		 * them, taken from the end opposite to `direction`
		 * @param {number[]} widths Widths of the elements in order
		 * @param {number} count How many items to include
		 * @param {number} gap Gap between two items
		 * @returns {number}
		 */
		width(widths, count, gap) {
			if (count <= 0) {
				return 0;
			}

			const slice =
				this.direction === "start"
					? widths.slice(-count)
					: widths.slice(0, count);

			return slice.reduce((sum, w) => sum + w, 0) + (count - 1) * gap;
		}
	},

	render() {
		const props = { offset: this.offset, total: this.total };
		/** @slot Items, render `offset` of `total`, the last ones for `direction="start"` */
		const slot = this.$slots.default?.(props) ?? [];

		if (this.isCollapsed === false) {
			return h(this.element, this.$attrs, slot);
		}

		/** @slot Rendered next to the items when not all fit, gets `offset` and `total` */
		const fallback = this.$slots.fallback?.(props) ?? [];

		if (this.direction === "start") {
			return h(this.element, this.$attrs, [...fallback, ...slot]);
		}

		return h(this.element, this.$attrs, [...slot, ...fallback]);
	}
};
</script>
