<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-coords-input name="coords" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-coords-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-coords-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-coords-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-coords-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

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
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	data() {
		return {
			value: null
		};
	}
};
</script>

<style>
.k-lab-examples .k-coords-input {
	aspect-ratio: 1/1;
	width: 10rem;
	background: var(--color-gray-300);
}
</style>
