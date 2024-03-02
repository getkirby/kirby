<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-email-input name="email" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-email-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-email-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-email-input
					placeholder="Placeholder text …"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-email-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-email-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Minlegth: 5">
				<k-email-input :minlength="5" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Maxlength: 5">
				<k-email-input :maxlength="5" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Pattern: must end @getkirby.com">
				<k-email-input
					pattern="[a-z]+@getkirby\.com"
					:value="value"
					@input="value = $event"
				/>
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
