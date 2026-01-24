<script>
import { cloneVNode, h } from "vue";

/**
 * Measures available horizontal space and determines
 * how many children from the default slot can fit.
 *
 * Exposes `offset` (visible count) and `total` to slots.
 * When items overflow and a fallback slot exists,
 * the fallback is rendered and items are hidden.
 * @since 6.0.0
 */
export default {
	inheritAttrs: false,
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
			isCollapsed: false,
			isUpdating: false,
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
		if (this.isUpdating === false) {
			this.calculate();
		}
	},
	methods: {
		async calculate() {
			if (
				this.isUpdating ||
				!(this.$el instanceof Element) ||
				!this.$slots.default
			) {
				return;
			}

			this.isUpdating = true;

			const available = this.$el.getBoundingClientRect().width;

			// First: try fitting all items without fallback
			this.isCollapsed = false;
			const expanded = await this.fitItemsInSpace(available);

			// Second: if overflowing and fallback exists,
			// show fallback and shrink items to fit
			if (expanded.overflown && this.hasFallback) {
				this.isCollapsed = true;
				const collapsed = await this.fitItemsInSpace(available);
				this.offset = collapsed.offset;
				this.total = collapsed.total;
			} else {
				this.offset = expanded.offset;
				this.total = expanded.total;
			}

			this.$nextTick(() => {
				this.isUpdating = false;
			});
		},
		async fitItemsInSpace(available) {
			// Show all items so we can measure them
			this.offset = Infinity;
			await this.$nextTick();

			// Separate fallback elements from items using data attribute
			const children = [...this.$el.children];
			const items = children.filter(
				(el) => !el.hasAttribute("data-collapsible-fallback")
			);
			const fallbacks = children.filter((el) =>
				el.hasAttribute("data-collapsible-fallback")
			);

			const total = items.length;
			const style = getComputedStyle(this.$el);
			const gap = parseFloat(style.columnGap ?? style.gap) ?? 0;

			// Handle edge cases
			if (available === 0) {
				return { offset: 0, total, overflown: total > 0 };
			}

			if (total === 0) {
				return { offset: 0, total, overflown: false };
			}

			// Measure each item's width
			const widths = items.map((el) => this.measure(el));

			// Use appropriate fitting strategy
			if (this.isCollapsed) {
				return this.shrinkUntilFallbackFits({
					available,
					fallbacks,
					gap,
					total,
					widths
				});
			}

			return this.growUntilFull({ available, gap, total, widths });
		},
		growUntilFull({ available, gap, total, widths }) {
			let count = 0;

			while (count < total && this.width(widths, count + 1, gap) <= available) {
				count++;
			}

			return { offset: count, total, overflown: count < total };
		},
		measure(element) {
			const rect = element.getBoundingClientRect();
			const style = getComputedStyle(element);
			const left = parseFloat(style.marginLeft) ?? 0;
			const right = parseFloat(style.marginRight) ?? 0;
			// Use scrollWidth to get natural width without flex shrinking
			const width = Math.max(rect.width, element.scrollWidth);
			return width + left + right;
		},
		measureFallback(elements, gap) {
			if (elements.length === 0) {
				return 0;
			}

			const widths = elements.map((el) => this.measure(el));
			return (
				widths.reduce((sum, w) => sum + w, 0) + (elements.length - 1) * gap
			);
		},
		observe() {
			if (this.$el instanceof Element) {
				this.$panel.observers.resize.observe(this.$el);
				this.$el.addEventListener("resize", this.onResize);
				this.calculate();
			}
		},
		onResize() {
			if (this.isUpdating === false) {
				this.calculate();
			}
		},
		async shrinkUntilFallbackFits({
			available,
			fallbacks,
			gap,
			total,
			widths
		}) {
			let count = total;

			while (count >= 0) {
				this.offset = count;
				await this.$nextTick();

				const fallback = this.measureFallback(fallbacks, gap);
				const items = this.width(widths, count, gap);
				gap = count > 0 && fallback > 0 ? gap : 0;
				const needed = items + gap + fallback;

				if (needed <= available) {
					break;
				}

				count--;
			}

			return { offset: Math.max(0, count), total, overflown: true };
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
		},
		unobserve() {
			if (this.$el instanceof Element) {
				this.$panel.observers.resize.unobserve(this.$el);
				this.$el.removeEventListener("resize", this.onResize);
			}
		}
	},

	render() {
		const props = { offset: this.offset, total: this.total };
		const slot = this.$slots.default?.(props) ?? [];

		// Get all fallback vnodes and mark them with a data attribute
		let fallbacks = [];

		if (this.isCollapsed) {
			const fallback = this.$slots.fallback?.(props) ?? [];
			fallbacks = fallback.map((vnode) =>
				cloneVNode(vnode, { "data-collapsible-fallback": "" })
			);
		}

		// Position fallback based on direction
		if (fallbacks.length === 0) {
			return h(this.element, this.$attrs, slot);
		}

		if (this.direction === "start") {
			return h(this.element, this.$attrs, [...fallbacks, ...slot]);
		}

		return h(this.element, this.$attrs, [...slot, ...fallbacks]);
	}
};
</script>
