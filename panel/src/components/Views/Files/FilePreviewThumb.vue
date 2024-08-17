<template>
	<div class="k-file-preview-thumb-column">
		<div class="k-file-preview-thumb">
			<slot />

			<template v-if="options.length">
				<k-button
					icon="dots"
					size="xs"
					class="k-file-preview-thumb-dropdown-toggle"
					@click="$refs.dropdown.toggle()"
				/>
				<k-dropdown-content
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
	}
};
</script>

<style>
.k-file-preview-thumb-column {
	aspect-ratio: 1/1;
	background: var(--pattern);
}
.k-file-preview-thumb {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	padding: var(--spacing-10);
	container-type: size;
}
.k-file-preview-thumb :where(img, audio, video) {
	width: auto;
	max-width: 100cqw;
	max-height: 100cqh;
}
.k-file-preview-thumb > .k-button {
	position: absolute;
	top: var(--spacing-2);
	inset-inline-start: var(--spacing-2);
}

.k-button.k-file-preview-thumb-dropdown-toggle {
	--button-color-icon: var(--color-gray-500);
}
.k-panel[data-theme="dark"] .k-button.k-file-preview-thumb-dropdown-toggle {
	--button-color-icon: var(--color-gray-700);
}
</style>
