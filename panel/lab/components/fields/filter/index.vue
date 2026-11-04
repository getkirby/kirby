<template>
	<k-lab-examples class="k-lab-field-examples">
		<k-lab-example label="Default">
			<k-filter-field
				:fields="fields"
				:operators="operators"
				:value="value"
				label="Conditions"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-filter-field
				:fields="fields"
				:operators="operators"
				:value="[]"
				label="Conditions"
			/>
		</k-lab-example>

		<k-lab-example label="Max: 2">
			<k-filter-field
				:fields="fields"
				:max="2"
				:operators="operators"
				:value="value"
				label="Conditions"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Help">
			<k-filter-field
				:fields="fields"
				:operators="operators"
				:value="value"
				help="This is some help text"
				label="Conditions"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Disabled">
			<k-filter-field
				:disabled="true"
				:fields="fields"
				:operators="operators"
				:value="value"
				label="Conditions"
			/>
		</k-lab-example>

		<k-lab-example label="No fields">
			<k-filter-field :operators="operators" label="Conditions" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			value: [
				{ field: "status", operator: "==", value: "listed" },
				{ field: "title", operator: "*=", value: "Kirby" }
			]
		};
	},
	computed: {
		fields() {
			return {
				title: { name: "title", label: "Title", type: "text" },
				status: {
					name: "status",
					label: "Status",
					type: "select",
					options: [
						{ value: "draft", text: "Draft" },
						{ value: "unlisted", text: "Unlisted" },
						{ value: "listed", text: "Listed" }
					]
				},
				date: { name: "date", label: "Date", type: "date" },
				views: { name: "views", label: "Views", type: "number" }
			};
		},
		operators() {
			return [
				{ value: "==", text: "is" },
				{ value: "!=", text: "is not" },
				{ value: "*=", text: "contains" },
				{ value: ">", text: "is greater than" },
				{ value: "<", text: "is less than" }
			];
		}
	}
};
</script>
