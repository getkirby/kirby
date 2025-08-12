<template>
	<div class="k-file-preview-frame-column">
		<div class="k-file-preview-frame">
			<slot />

			<template v-if="options.length">
				<k-button
					icon="dots"
					size="xs"
					class="k-file-preview-frame-dropdown-toggle"
					@click="$refs.dropdown.toggle()"
				/>
				<k-dropdown
					ref="dropdown"
					:options="options"
					theme="light"
					@action="$emit('action', $event)"
				/>
			</template>
		</div>
	</div>
</template>

<script>
/**
 * @since 5.0.0
 */
export default {
	props: {
		options: {
			default: () => [],
			type: Array
		}
	},
	emits: ["action"]
};
</script>

<style>
.k-file-preview-frame-column {
	aspect-ratio: 1/1;
	background: var(--pattern);
}
.k-file-preview-frame {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	padding: var(--spacing-10);
	container-type: size;
}
.k-file-preview-frame :where(img, audio, video) {
	width: auto;
	max-width: 100cqw;
	max-height: 100cqh;
}
.k-file-preview-frame > .k-button {
	position: absolute;
	top: var(--spacing-2);
	inset-inline-start: var(--spacing-2);
}

.k-button.k-file-preview-frame-dropdown-toggle {
	--button-color-icon: var(--color-gray-500);
}

@container (min-width: 36rem) and (max-width: 65rem) {
	.k-file-preview-frame-column {
		aspect-ratio: auto;
	}
}
</style>
