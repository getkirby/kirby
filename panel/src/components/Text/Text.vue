<template>
	<!-- eslint-disable-next-line vue/no-v-html -->
	<div v-if="html" v-bind="attrs" v-html="html"></div>
	<div v-else v-bind="attrs">
		<!-- @slot Text content -->
		<slot />
	</div>
</template>

<script>
/**
 * A container for all multi-line text with additional formats.
 *
 * @example <k-text>
  <b>Lorem</b> <a href="#">ipsum</a> <i>dolor</i> …
</k-text>
 */
export default {
	props: {
		/**
		 * Changes the text alignment
		 * @values start, center, end
		 */
		align: String,
		/**
		 * HTML content to render instead
		 * of the default slot
		 */
		html: String,
		/**
		 * Font size of the text
		 * @values tiny, small, medium, large, huge
		 */
		size: String,
		/**
		 * Visual appearance of the text
		 * @values help
		 * @deprecated 4.0.0 Use `k-help` class instead
		 */
		theme: String
	},
	computed: {
		attrs() {
			return {
				class: "k-text",
				"data-align": this.align,
				"data-size": this.size,
				"data-theme": this.theme
			};
		}
	},
	mounted() {
		if (this.theme) {
			window.panel.deprecated(
				'<k-text>: the `theme` prop will be removed in a future version. For help text, add `.k-help "` CSS class instead.'
			);
		}
	}
};
</script>

<style>
:root {
	--text-font-size: 1em;
	--text-line-height: 1.5;
	--link-color: var(--color-blue-800);
	--link-underline-offset: 2px;
}

.k-text {
	font-size: var(--text-font-size);
	line-height: var(--text-line-height);
}

/* Font sizes */
.k-text[data-size="tiny"] {
	--text-font-size: var(--text-xs);
}
.k-text[data-size="small"] {
	--text-font-size: var(--text-sm);
}
.k-text[data-size="medium"] {
	--text-font-size: var(--text-md);
}
.k-text[data-size="large"] {
	--text-font-size: var(--text-xl);
}

/* Alignment helper */
.k-text[data-align] {
	text-align: var(--align);
}

/* Element margins */
.k-text
	> :where(
		audio,
		blockquote,
		details,
		div,
		figure,
		h1,
		h2,
		h3,
		h4,
		h5,
		h6,
		hr,
		iframe,
		img,
		object,
		ol,
		p,
		picture,
		pre,
		table,
		ul
	)
	+ * {
	margin-block-start: calc(var(--text-line-height) * 1em);
}

/* Links */
.k-text :where(.k-link, a) {
	color: var(--link-color);
	text-decoration: underline;
	text-underline-offset: var(--link-underline-offset);
	border-radius: var(--rounded-xs);
	outline-offset: 2px;
}

/* Lists */
.k-text ol,
.k-text ul {
	padding-inline-start: 1.75em;
}

.k-text ol {
	list-style: numeric;
}
.k-text ol > li {
	list-style: decimal;
}

.k-text ul > li {
	list-style: disc;
}
.k-text ul ul > li {
	list-style: circle;
}
.k-text ul ul ul > li {
	list-style: square;
}

/* Blockquotes */
.k-text blockquote {
	font-size: var(--text-lg);
	line-height: 1.25;
	padding-inline-start: var(--spacing-4);
	border-inline-start: 2px solid var(--color-black);
}

/* Images */
.k-text img {
	border-radius: var(--rounded);
}

/* Embeds */
.k-text iframe {
	width: 100%;
	aspect-ratio: 16/9;
	border-radius: var(--rounded);
}

/* HR */
.k-text hr {
	background: var(--color-border);
	height: 1px;
}

/* Help */
.k-help {
	color: var(--color-text-dimmed);
}
</style>
