<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The pages field does not work without the backend yet
			</k-box>

			<k-lab-example label="Default">
				<k-pages-field
					name="pages"
					label="pages"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-pages-field
					:autofocus="true"
					label="pages"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-pages-field
					label="pages"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-pages-field
					label="pages"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-pages-field
					label="pages"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-pages-field
					label="pages"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-pages-field
					label="pages"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-pages-field
					:disabled="true"
					label="pages"
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
			value: []
		};
	}
};
</script>
