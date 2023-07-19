<template>
	<!-- @slot Code content -->
	<pre class="k-code"><code><slot /></code></pre>
</template>

<script>
/**
 * @public
 */
export default {}
</script>

<style>
:root {
	--code-color-back: var(--color-black);
	--code-color-icon: var(--color-gray-500);
	--code-color-text: var(--color-gray-200, white);
	--code-font-family: var(--font-mono);
	--code-font-size: 1em;
}
.k-code,
.k-text pre {
	position: relative;
	display: block;
	font-family: var(--code-font-family);
	font-size: var(--code-font-size);
	max-width: 100%;
	padding: 0.75rem;
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
</style>
