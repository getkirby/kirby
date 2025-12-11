<template>
	<dl class="k-definitions">
		<slot />
	</dl>
</template>

<script>
/**
 * @since 5.3.0
 */
export default {};
</script>

<style>
.k-definitions {
	background: var(--table-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	overflow: hidden;
	line-height: 1.25;
	container-type: inline-size;
}
</style>
