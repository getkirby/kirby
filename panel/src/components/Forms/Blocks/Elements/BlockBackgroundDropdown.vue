<template>
	<div class="k-block-background-dropdown">
		<k-button
			:dropdown="true"
			size="xs"
			variant="filled"
			@click="$refs.dropdown.toggle()"
		>
			<k-color-frame :color="value" ratio="1/1" />
		</k-button>
		<k-dropdown-content
			ref="dropdown"
			align-x="end"
			:options="[
				{ text: 'White', value: 'white', click: 'var(--color-white)' },
				{ text: 'Pattern', value: 'pattern', click: 'var(--pattern)' },
				{ text: 'Dark', value: 'dark', click: 'var(--color-dark)' }
			]"
			@action="$emit('input', $event)"
		/>
	</div>
</template>

<script>
export default {
	props: {
		value: String
	}
};
</script>

<style>
.k-block-background-dropdown > .k-button {
	--color-frame-size: 1.25rem;
	--button-height: 1.5rem;
	--button-padding: 0.125rem;
	gap: 0.25rem;
	box-shadow: var(--shadow);
}

.k-block .k-block-background-dropdown {
	position: absolute;
	inset-inline-end: var(--spacing-3);
	bottom: var(--spacing-3);
	opacity: 0;
	transition: opacity 0.2s ease-in-out;
}
.k-block:hover .k-block-background-dropdown {
	opacity: 1;
}
</style>
