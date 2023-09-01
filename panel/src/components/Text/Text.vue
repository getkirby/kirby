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
 * The <k-text> component is a container for all multi-line text with additional formats.
 * @public
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
	created() {
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
	--text-line-height: 1.375;
}

.k-text {
	font-size: var(--text-font-size);
	line-height: var(--text-line-height);
}

.k-text > * + * {
	margin-block-start: calc(var(--text-line-height) * 1em);
}

.k-text ol,
.k-text ul {
	margin-inline-start: 2em;
}

.k-text ol {
	list-style: numeric;
}

.k-text ul {
	list-style: disc;
}

.k-text a {
	text-decoration: underline;
}

.k-text > * + h6 {
	margin-block-start: calc(var(--text-line-height) * 1.5em);
}

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
.k-text[data-align] {
	text-align: var(--align);
}

/** Code */
.h1 code,
.k-text h1 code,
.h2 code,
.k-text h2 code,
.h3 code,
.k-text h3 code,
.h4 code,
.k-text h4 code,
.h5 code,
.k-text h5 code,
.h6 code,
.k-text h6 code {
	font-family: var(--font-mono, monospace);
	font-size: 0.925em;
	font-weight: var(--font-normal);
}

.k-text iframe {
	width: 100%;
	aspect-ratio: 16/9;
}

/** HR **/
.hr,
.k-text hr {
	background: var(--color-border);
	height: 1px;
}

/** Links **/
:root {
	--link-color: var(--color-blue-800);
	--link-underline-offset: 2px;
}

.k-text :where(.k-link, a) {
	color: var(--link-color);
	text-decoration: underline;
	text-underline-offset: var(--link-underline-offset);
	border-radius: var(--rounded-xs);
	outline-offset: 2px;
}

/** Help */
.k-help {
	color: var(--color-text-dimmed);
}
</style>
