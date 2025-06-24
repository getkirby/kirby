<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-date-field
					name="date"
					label="date"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-date-field
					:autofocus="true"
					label="date"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-date-field
					label="date"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-date-field
					label="date"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-date-field
					label="date"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-date-field
					label="date"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-date-field
					label="date"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-date-field
					:disabled="true"
					label="date"
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
