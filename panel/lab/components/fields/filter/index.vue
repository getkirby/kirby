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

		<k-lab-example label="Single operator">
			<k-filter-field
				:fields="fields"
				:operators="singleOperators"
				:value="single"
				help="All fields share the same single operator, so the operator column is hidden"
				label="Conditions"
				@input="single = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Mixed operators">
			<k-filter-field
				:fields="fields"
				:operators="mixedOperators"
				:value="mixed"
				help="Status only offers a single operator, which is shown as text"
				label="Conditions"
				@input="mixed = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Invalid conditions">
			<k-filter-field
				:fields="fields"
				:operators="operators"
				:value="invalid"
				help="An unknown field and an operator that doesn't fit are shown as empty selection"
				label="Conditions"
				@input="invalid = $event"
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
			invalid: [
				{ field: "unknown", operator: "==", value: "test" },
				{ field: "title", operator: ">", value: "Kirby" }
			],
			mixed: [
				{ field: "status", operator: "==", value: "listed" },
				{ field: "views", operator: ">", value: 100 }
			],
			single: [
				{ field: "status", operator: "==", value: "listed" },
				{ field: "title", operator: "==", value: "Kirby" }
			],
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
		mixedOperators() {
			return {
				...this.operators,
				status: [{ value: "==", text: "is" }]
			};
		},
		operators() {
			const equality = [
				{ value: "==", text: "is" },
				{ value: "!=", text: "is not" }
			];

			const comparison = [
				...equality,
				{ value: ">", text: "is greater than" },
				{ value: "<", text: "is less than" }
			];

			return {
				title: [
					...equality,
					{ value: "*=", text: "contains" },
					{ value: "^=", text: "starts with" }
				],
				status: equality,
				date: comparison,
				views: comparison
			};
		},
		singleOperators() {
			const is = [{ value: "==", text: "is" }];

			return {
				title: is,
				status: is,
				date: is,
				views: is
			};
		}
	}
};
</script>
