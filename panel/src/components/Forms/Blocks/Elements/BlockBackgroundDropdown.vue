<template>
	<div class="k-block-background-dropdown">
		<k-button
			:dropdown="true"
			size="xs"
			variant="filled"
			@click="$refs.dropdown.toggle()"
		>
			<k-color-frame :color="valueAdapted" ratio="1/1" />
		</k-button>
		<k-dropdown
			ref="dropdown"
			align-x="end"
			:options="[
				{
					text: $t('field.blocks.figure.back.plain'),
					click: 'var(--block-color-back)'
				},
				{
					text: $t('field.blocks.figure.back.pattern.light'),
					click: 'var(--pattern-light)'
				},
				{
					text: $t('field.blocks.figure.back.pattern.dark'),
					click: 'var(--pattern)'
				}
			]"
			@action="$emit('input', $event)"
		/>
	</div>
</template>

<script>
export default {
	props: {
		value: String
	},
	emits: ["input"],
	computed: {
		valueAdapted() {
			if (this.value === "transparent") {
				return "var(--block-color-back)";
			}

			return this.value;
		}
	}
};
</script>

<style>
.k-block-background-dropdown > .k-button {
	--color-frame-rounded: 0;
	--color-frame-size: 1.5rem;
	--button-height: 1.5rem;
	--button-padding: 0 0.125rem;
	--button-color-back: var(--block-color-back);
	gap: 0.25rem;
	box-shadow: var(--shadow-toolbar);
	border: 1px solid var(--button-color-back);
	overflow: clip;
}
.k-block-background-dropdown .k-color-frame {
	border-right: 1px solid var(--color-border);
}
.k-block-background-dropdown .k-color-frame::after {
	box-shadow: none;
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
