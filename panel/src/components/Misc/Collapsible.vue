<script>
import { h } from "vue";

/**
 * Measures available horizontal space and determines
 * how many children from the default slot can fit.
 *
 * Exposes `offset` (visible count) and `total` to slots.
 * When items overflow and a fallback slot exists,
 * the fallback is rendered and items are hidden.
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
		element: {
			type: String,
			default: "div"
		}
	},
	data() {
		return {
			available: null,
			isCollapsed: false,
			isMeasuring: false,
			offset: Infinity,
			total: 0
		};
	},
	computed: {
		hasFallback() {
			return Boolean(this.$slots.fallback);
		}
	},
	mounted() {
		this.observe();
	},
	unmounted() {
		this.unobserve();
	},
	updated() {
		this.calculate();
	},
	methods: {
		async calculate() {
			if (this.available === null || this.isMeasuring === true) {
				return;
			}

			this.isMeasuring = true;

			// measure all items without the fallback
			this.isCollapsed = false;
			this.offset = Infinity;
			await this.$nextTick();

			const style = getComputedStyle(this.$el);
			const gap = parseFloat(style.columnGap ?? style.gap) ?? 0;
			const widths = [...this.$el.children].map(this.measure);
			// tolerates subpixel rounding of the measured widths
			const space = this.available + 0.5;
			let count = this.fit(widths, gap, space);

			this.total = widths.length;
			this.isCollapsed = count < widths.length && this.hasFallback;
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
		 */
		measure(element) {
			const style = getComputedStyle(element);
			const left = parseFloat(style.marginLeft) ?? 0;
			const right = parseFloat(style.marginRight) ?? 0;
			return element.getBoundingClientRect().width + left + right;
		},
		observe() {
			if (this.$el instanceof Element) {
				this.$panel.observers.resize.observe(this.$el);
				this.$el.addEventListener("resize", this.onResize);
			}
		},
		onResize(event) {
			// the content box width, height changes from own renders do not matter
			if (event.detail.width !== this.available) {
				this.available = event.detail.width;
				this.calculate();
			}
		},
		unobserve() {
			if (this.$el instanceof Element) {
				this.$panel.observers.resize.unobserve(this.$el);
				this.$el.removeEventListener("resize", this.onResize);
			}
		},
		/**
		 * Calculates total width of `count` items including gaps.
		 * direction=end: uses first N items
		 * direction=start: uses last N items
		 *
		 * @param {number[]} widths
		 * @param {number} count
		 * @param {number} gap
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
		const slot = this.$slots.default?.(props) ?? [];

		if (this.isCollapsed === false) {
			return h(this.element, this.$attrs, slot);
		}

		const fallback = this.$slots.fallback?.(props) ?? [];

		if (this.direction === "start") {
			return h(this.element, this.$attrs, [...fallback, ...slot]);
		}

		return h(this.element, this.$attrs, [...slot, ...fallback]);
	}
};
</script>
