<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-time-input name="time" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-time-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-time-input :required="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-time-input
					placeholder="Placeholder text …"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-time-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-time-input :disabled="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Min: 12:00">
				<k-time-input :value="value" min="12:00:00" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Max: 18:00">
				<k-time-input :value="value" max="18:00:00" @input="value = $event" />
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
