<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-range-field
					name="range"
					label="range"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-range-field
					:autofocus="true"
					label="range"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-range-field
					label="range"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-range-field
					label="range"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-range-field
					label="range"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-range-field
					label="range"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-range-field
					label="range"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-range-field
					:disabled="true"
					label="range"
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
