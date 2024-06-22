<template>
	<div class="k-file-preview-thumb-column">
		<div class="k-file-preview-thumb">
			<slot />

			<template v-if="options.length">
				<k-button
					icon="dots"
					size="xs"
					style="color: var(--color-gray-500)"
					@click="$refs.dropdown.toggle()"
				/>
				<k-dropdown-content ref="dropdown" :options="options" theme="light" />
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
</style>
