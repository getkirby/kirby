<template>
	<code :data-type="(type ?? 'true').toLowerCase()">
		<slot />
	</code>
</template>

<script>
export default {
	props: {
		type: String
	}
};
</script>

<style>
/** Inline code tokens */
:root {
	--code-inline-color-back: var(--color-blue-300);
	--code-inline-color-border: light-dark(
		var(--color-blue-400),
		var(--color-blue-900)
	);
	--code-inline-color-text: var(--color-blue-900);
	--code-inline-font-size: 0.9em;
}

code[data-type],
.k-text > code,
.k-text *:not(pre) > code {
	display: inline-flex;
	line-height: var(--text-line-height);
	padding-inline: var(--spacing-1);
	font-size: var(--code-inline-font-size);
	color: var(--code-inline-color-text);
	background: var(--code-inline-color-back);
	border-radius: var(--rounded);
	outline: 1px solid var(--code-inline-color-border);
	outline-offset: -1px;
}

code:is([data-type="gray"], [data-type="undefined"]) {
	color: light-dark(var(--color-gray-800), var(--color-gray-900));
	outline-color: light-dark(var(--color-gray-400), var(--color-gray-900));
	background: var(--color-gray-300);
}
code:is([data-type="purple"], [data-type="boolean"]) {
	color: light-dark(var(--color-purple-800), var(--color-purple-900));
	outline-color: light-dark(var(--color-purple-400), var(--color-purple-900));
	background: var(--color-purple-300);
}
code:is([data-type="green"], [data-type="string"]) {
	color: light-dark(var(--color-green-800), var(--color-green-900));
	outline-color: light-dark(var(--color-green-500), var(--color-green-900));
	background: var(--color-green-300);
}
code:is([data-type="orange"], [data-type="number"]) {
	color: light-dark(var(--color-orange-800), var(--color-orange-900));
	outline-color: light-dark(var(--color-orange-500), var(--color-orange-900));
	background: var(--color-orange-300);
}
code:is([data-type="aqua"], [data-type="array"]) {
	color: light-dark(var(--color-aqua-800), var(--color-aqua-900));
	outline-color: light-dark(var(--color-aqua-500), var(--color-aqua-900));
	background: var(--color-aqua-300);
}
code:is([data-type="yellow"], [data-type="object"]) {
	color: light-dark(var(--color-yellow-800), var(--color-yellow-900));
	outline-color: light-dark(var(--color-yellow-500), var(--color-yellow-900));
	background: var(--color-yellow-300);
}
code:is([data-type="pink"], [data-type="func"]) {
	color: light-dark(var(--color-pink-800), var(--color-pink-900));
	outline-color: var(--color-pink-400);
	background: var(--color-pink-300);
}
code:is([data-type="red"]) {
	color: light-dark(var(--color-red-800), var(--color-red-900));
	outline-color: var(--color-red-400);
	background: var(--color-red-300);
}
code:is([data-type="black"]) {
	color: var(--color-gray-100);
	outline-color: var(--color-black);
	background: var(--color-gray-950);
}
code:is([data-type="white"]) {
	color: var(--color-gray-900);
	outline-color: var(--color-border);
	background: var(--color-white);
}
</style>
