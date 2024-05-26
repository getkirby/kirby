<template>
	<a
		v-if="to && !disabled"
		ref="link"
		:download="downloadAttr"
		:href="href"
		:rel="relAttr"
		:tabindex="tabindex"
		:target="target"
		:title="title"
		class="k-link"
		@click="onClick"
	>
		<!-- @slot Visible linked text -->
		<slot />
	</a>
	<span v-else :title="title" class="k-link" aria-disabled>
		<slot />
	</span>
</template>

<script>
export const props = {
	props: {
		/**
		 * A disabled button/link will have no pointer events and
		 * the opacity is be reduced.
		 */
		disabled: Boolean,
		/**
		 * Whether the link should be downloaded directly
		 */
		download: Boolean,
		/**
		 * `rel` attribute for the link
		 */
		rel: String,
		/**
		 * Custom tabindex; only use if you really know
		 * how to adjust the order properly
		 */
		tabindex: [String, Number],
		/**
		 * Set the target of the link
		 */
		target: String,
		/**
		 * The title attribute can be used to add additional text
		 * to the button/link, which is shown on mouseover.
		 */
		title: String
	}
};

/**
 * Wapper around a native HTML `<a>` element that ensures the
 * correct routing behavior for Panel as well as external links.
 *
 * @example <k-link to="https://getkirby.com">Kirby Website</k-link>
 */
export default {
	mixins: [props],
	props: {
		/**
		 * The path or absolute URL for the link.
		 */
		to: [String, Function]
	},
	emits: ["click"],
	computed: {
		downloadAttr() {
			return this.download ? this.href.split("/").pop() : undefined;
		},
		href() {
			if (typeof this.to === "function") {
				return "";
			}

			if (this.to[0] === "/" && !this.target) {
				return this.$url(this.to);
			}

			if (
				this.to.includes("@") === true &&
				this.to.includes("/") === false &&
				this.to.startsWith("mailto:") === false
			) {
				return `mailto:` + this.to;
			}

			return this.to;
		},
		relAttr() {
			return this.target === "_blank" ? "noreferrer noopener" : this.rel;
		}
	},
	methods: {
		isRoutable(e) {
			// don't route with control keys
			if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) {
				return false;
			}

			// don't route when preventDefault called
			if (e.defaultPrevented) {
				return false;
			}

			// don't route on right click
			if (e.button !== undefined && e.button !== 0) {
				return false;
			}

			// don't route if a target is set
			if (this.target) {
				return false;
			}

			if (typeof this.href === "string") {
				// don't route if it's an absolute link
				if (this.href.includes("://") || this.href.startsWith("//")) {
					return false;
				}

				// don't route if it's an email
				if (this.href.includes("mailto:")) {
					return false;
				}
			}

			return true;
		},
		onClick(e) {
			if (this.disabled === true) {
				e.preventDefault();
				return false;
			}

			if (typeof this.to === "function") {
				e.preventDefault();
				this.to();
			}

			if (this.isRoutable(e)) {
				e.preventDefault();
				this.$go(this.to);
			}

			/**
			 * The link has been clicked
			 */
			this.$emit("click", e);
		}
	}
};
</script>
