<template>
	<div :data-id="id" :data-nested="nested" class="k-drawer">
		<slot />
	</div>
</template>

<script>
export default {
	props: {
		id: String,
		nested: Boolean
	}
};
</script>

<style>
:root {
	--drawer-color-back: var(--color-light);
	--drawer-header-height: 2.5rem;
	--drawer-header-padding: 1.5rem;
	--drawer-shadow: var(--shadow-xl);
	--drawer-width: 50rem;
}

.k-drawer-overlay {
	--overlay-color-back: rgba(0, 0, 0, 0.2);
	display: flex;
	align-items: stretch;
	justify-content: flex-end;
}

.k-drawer {
	z-index: var(--z-toolbar);
	display: flex;
	flex-basis: var(--drawer-width);
	position: relative;
	display: flex;
	flex-direction: column;
	background: var(--drawer-color-back);
	box-shadow: var(--drawer-shadow);
}

/* Nested drawers */
.k-drawer[data-nested="true"] {
	background: none;
}
</style>
