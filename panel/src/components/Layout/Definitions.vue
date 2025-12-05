<template>
	<dl class="k-definitions">
		<slot />
	</dl>
</template>

<style>
.k-definitions {
	--definition-term-width: 5rem;
	--definition-height: var(--table-row-height);
	background: var(--table-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	overflow: hidden;
	line-height: 1.25;
}
.k-definitions > div {
	display: grid;
	grid-template-columns: var(--definition-term-width) 1fr;
}
.k-definitions dt,
.k-definitions dd {
	padding-block: var(--spacing-2);
	padding-inline: var(--table-cell-padding);
	height: var(--definition-height);
	min-height: var(--table-row-height);
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
