<template>
	<hr :class="['k-line-field', $attrs.class]" :style="$attrs.style" />
</template>

<script>
/**
 * @example
 * <k-line-field />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	inheritAttrs: false
};
</script>

<style>
.k-line-field {
	position: relative;
	border: 0;
	height: 3rem;
	width: auto;
}
.k-line-field::after {
	position: absolute;
	content: "";
	top: 50%;
	margin-top: -1px;
	inset-inline: 0;
	height: 1px;
	background: var(--color-border);
}
</style>
