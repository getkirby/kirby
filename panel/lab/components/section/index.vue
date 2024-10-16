<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-section label="Label">
				<p>Nice Section</p>
			</k-section>
		</k-lab-example>
		<k-lab-example label="Label with link">
			<k-section label="Label" link="https://getkirby.com">
				<p>Nice Section</p>
			</k-section>
		</k-lab-example>
		<k-lab-example label="Required">
			<k-section :required="true" label="Label">
				<p>This section is required</p>
			</k-section>
		</k-lab-example>
		<k-lab-example label="Buttons">
			<k-section :buttons="[{ icon: 'add', text: 'Add' }]" label="Label">
				<p>Nice Section</p>
			</k-section>
		</k-lab-example>
		<k-lab-example label="Options Slot">
			<k-section label="Label">
				<k-button icon="add" size="xs" slot="options" variant="filled">
					Add
				</k-button>
				<p>Nice Section</p>
			</k-section>
		</k-lab-example>
		<k-lab-example label="Options Slot & excessive label">
			<k-section
				label="This is a very very long label that could push the options aside and break stuff"
			>
				<k-button icon="add" size="xs" slot="options" variant="filled">
					Add
				</k-button>
				<p>Nice Section</p>
			</k-section>
		</k-lab-example>
	</k-lab-examples>
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
