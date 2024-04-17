<template>
	<figure :class="['k-block-figure', $attrs.class]" :style="$attrs.style">
		<k-button
			v-if="isEmpty"
			:disabled="disabled"
			:icon="emptyIcon"
			:text="emptyText"
			class="k-block-figure-empty"
			@click="$emit('open')"
		/>
		<span
			v-else
			:data-disabled="disabled"
			class="k-block-figure-container"
			@dblclick="$emit('open')"
		>
			<slot />
		</span>
		<figcaption v-if="caption">
			<k-writer-input
				:disabled="disabled"
				:inline="true"
				:marks="captionMarks"
				:value="caption"
				@input="$emit('update', { caption: $event })"
			/>
		</figcaption>
	</figure>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		caption: String,
		captionMarks: {
			default: true,
			type: [Boolean, Array]
		},
		disabled: Boolean,
		isEmpty: Boolean,
		emptyIcon: String,
		emptyText: String
	},
	emits: ["open", "update"]
};
</script>

<style>
.k-block-figure-container:not([data-disabled="true"]) {
	cursor: pointer;
}
.k-block-figure iframe {
	border: 0;
	pointer-events: none;
	background: var(--color-black);
}
.k-block-figure figcaption {
	padding-top: 0.5rem;
	color: var(--color-text-dimmed);
	font-size: var(--text-sm);
	text-align: center;
}
.k-block-figure-empty {
	--button-width: 100%;
	--button-height: 6rem;
	--button-color-text: var(--color-text-dimmed);
	--button-color-back: var(--color-light);
}

.k-block-figure-empty,
.k-block-figure-container > * {
	border-radius: var(--rounded-sm);
}
</style>
