<template>
	<dl class="k-definitions">
		<slot />
	</dl>
</template>

<style>
.k-definitions {
	background: var(--table-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	overflow: hidden;
}
.k-definitions > div {
	display: grid;
	grid-template-columns: 5rem 1fr;
}
.k-definitions dt,
.k-definitions dd {
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
	display: flex;
	align-items: center;
}
.k-definitions div:not(:last-child) dt,
.k-definitions div:not(:last-child) dd {
	border-block-end: 1px solid var(--table-color-border);
}
.k-definitions dt {
	background: var(--table-color-th-back);
	border-inline-end: 1px solid var(--table-color-border);
}
</style>
