<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-tel-input name="tel" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-tel-input :autofocus="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Required">
				<k-tel-input :required="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-tel-input
					placeholder="Placeholder text …"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-tel-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-tel-input :disabled="true" :value="value" @input="value = $event" />
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
