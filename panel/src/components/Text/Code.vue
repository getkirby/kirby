<template>
	<k-highlight>
		<div>
			<pre
				class="k-code"
				:data-language="language"
			><code :key="$slots.default()[0].children + '-' + language" :class="language ? `language-${language}` : null"><slot /></code></pre>
		</div>
	</k-highlight>
</template>

<script>
import { defineAsyncComponent } from "vue";

/**
 * A code block with syntax highlighting
 * @since 4.0.0
 * @example <k-code language="html"><p>Hello World</p></k-code>
 */
export default {
	components: {
		"k-highlight": defineAsyncComponent(() => import("./Highlight.vue"))
	},
	props: {
		/**
		 * The language of the code block. Used for syntax highlighting.
		 */
		language: {
			type: String
		}
	}
};
</script>

<style>
:root {
	--code-color-back: var(--color-black);
	--code-color-icon: var(--color-gray-500);
	--code-color-text: var(--color-gray-200, var(--color-white));
	--code-font-family: var(--font-mono);
	--code-font-size: 1em;
	--code-inline-color-back: var(--color-blue-300);
	--code-inline-color-border: light-dark(var(--color-blue-400), var(--color-blue-900));
	--code-inline-color-text: var(--color-blue-900);
	--code-inline-font-size: 0.9em;
	--code-padding: var(--spacing-3);
}

code {
	font-family: var(--code-font-family);
	font-size: var(--code-font-size);
	font-weight: var(--font-normal);
}

.k-code,
.k-text pre {
	position: relative;
	display: block;
	max-width: 100%;
	padding: var(--code-padding);
	border-radius: var(--rounded, 0.5rem);
	background: var(--code-color-back);
	color: var(--code-color-text);
	white-space: nowrap;
	overflow-y: hidden;
	overflow-x: auto;
	line-height: 1.5;
	tab-size: 2;
}
.k-code:not(code),
.k-text pre {
	white-space: pre-wrap;
}
.k-code::before {
	position: absolute;
	content: attr(data-language);
	inset-block-start: 0;
	inset-inline-end: 0;
	padding: 0.5rem 0.5rem 0.25rem 0.25rem;
	font-size: calc(0.75 * var(--text-xs));
	background: var(--code-color-back);
	border-radius: var(--rounded, 0.5rem);
}

/** Inline code */
.k-text > code,
.k-text *:not(pre) > code {
	display: inline-flex;
	padding-inline: var(--spacing-1);
	font-size: var(--code-inline-font-size);
	color: var(--code-inline-color-text);
	background: var(--code-inline-color-back);
	border-radius: var(--rounded);
	outline: 1px solid var(--code-inline-color-border);
	outline-offset: -1px;
}
</style>
