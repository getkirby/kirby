<template>
	<div class="k-definition">
		<dt>
			<slot name="term">{{ term }}</slot>
		</dt>
		<dd>
			<slot>{{ description }}</slot>
		</dd>
	</div>
</template>

<script>
/**
 * @since 5.3.0
 */
export default {
	props: {
		description: String,
		term: String
	}
};
</script>

<style>
:root {
	--definition-align: center;
	--definition-height: var(--table-row-height);
	--definition-term-width: clamp(5rem, 100%, 20%);
}

.k-definition {
	display: grid;
	grid-template-columns: var(--definition-term-width) 1fr;
}
.k-definition dt,
.k-definition dd {
	padding-block: var(--spacing-2);
	padding-inline: var(--table-cell-padding);
	height: var(--definition-height);
	min-height: var(--table-row-height);
	display: flex;
	align-items: var(--definition-align);
}
.k-definition:not(:last-child) dt,
.k-definition:not(:last-child) dd {
	border-block-end: 1px solid var(--table-color-border);
}
.k-definition dt {
	background: var(--table-color-th-back);
	border-inline-end: 1px solid var(--table-color-border);
}
</style>
