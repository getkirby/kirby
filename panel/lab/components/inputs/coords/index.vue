<template>
	<k-lab-input-examples
		:placeholder="false"
		:value="value"
		type="coords"
		@input="value = $event"
	>
		<k-lab-example label="Image">
			<k-coords-input :value="value" @input="value = $event">
				<k-image-frame
					:cover="true"
					:src="`https://picsum.photos/id/11/600/850`"
				/>
			</k-coords-input>
		</k-lab-example>
		<k-lab-example label="Disabled with image">
			<k-coords-input :disabled="true" :value="value" @input="value = $event">
				<k-image-frame
					:cover="true"
					:src="`https://picsum.photos/id/11/600/850`"
				/>
			</k-coords-input>
		</k-lab-example>
	</k-lab-input-examples>
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
.k-lab-examples .k-coords-input {
	aspect-ratio: 1/1;
	width: 10rem;
	background: var(--color-gray-300);
}
</style>
