<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-text-input name="text" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-text-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-text-input :required="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-text-input
					placeholder="Placeholder text …"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-text-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-text-input :disabled="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Minlength: 5">
				<k-text-input :minlength="5" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Maxlength: 10">
				<k-text-input :maxlength="10" :value="value" @input="value = $event" />
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
