<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-list-input name="list" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-list-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-list-input :required="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-list-input
					placeholder="Placeholder text …"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-list-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-list-input :disabled="true" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Minlength: 5">
				<k-list-input :minlength="5" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Maxlength: 10">
				<k-list-input :maxlength="10" :value="value" @input="value = $event" />
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
