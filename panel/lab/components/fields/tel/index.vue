<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-tel-field
					name="tel"
					label="tel"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-tel-field
					:autofocus="true"
					label="tel"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-tel-field
					label="tel"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-tel-field
					label="tel"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-tel-field
					label="tel"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-tel-field
					label="tel"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-tel-field
					label="tel"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-tel-field
					:disabled="true"
					label="tel"
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
