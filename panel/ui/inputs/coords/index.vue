<template>
	<k-ui-input-examples
		:placeholder="false"
		:value="value"
		type="coords"
		@input="value = $event"
	>
		<k-ui-example label="Image">
			<k-coords-input :value="value" @input="value = $event">
				<k-image-frame
					:cover="true"
					:src="`https://picsum.photos/id/11/600/850`"
				/>
			</k-coords-input>
		</k-ui-example>
		<k-ui-example label="Disabled with image">
			<k-coords-input :disabled="true" :value="value" @input="value = $event">
				<k-image-frame
					:cover="true"
					:src="`https://picsum.photos/id/11/600/850`"
				/>
			</k-coords-input>
		</k-ui-example>
	</k-ui-input-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null,
		};
	},
};
</script>

<style>
.k-ui-examples .k-coords-input {
	aspect-ratio: 1/1;
	width: 10rem;
	background: var(--color-gray-300);
}
</style>
