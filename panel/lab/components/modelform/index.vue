<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-model-form
				:api="api"
				:columns="columns"
				:content="values.default"
				@input="values.default = $event"
				@submit="log('submit')"
			/>
		</k-lab-example>

		<k-lab-example label="Single column">
			<k-model-form
				:api="api"
				:columns="singleColumn"
				:content="values.single"
				@input="values.single = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Unsaved changes">
			<k-model-form
				:api="api"
				:columns="columns"
				:content="values.diff"
				:diff="diff"
				@input="values.diff = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Locked">
			<k-model-form
				:api="api"
				:columns="columns"
				:content="values.locked"
				:lock="lock"
			/>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-model-form
				:api="api"
				:columns="{}"
				empty="This page does not have any fields yet."
			/>
		</k-lab-example>

		<k-lab-example :code="false" label="Playground">
			<k-stack gap="var(--spacing-12)">
				<k-grid
					style="
						--columns: 2;
						--grid-inline-gap: var(--spacing-1);
						--grid-block-gap: var(--spacing-1);
					"
				>
					<k-toggle-field
						:value="isLocked"
						text="lock"
						@input="isLocked = $event"
					/>
					<k-toggle-field
						:value="hasDiff"
						text="diff"
						@input="hasDiff = $event"
					/>
				</k-grid>

				<k-model-form
					:api="api"
					:columns="columns"
					:content="values.playground"
					:diff="hasDiff ? diff : {}"
					:lock="isLocked ? lock : false"
					@input="values.playground = $event"
					@submit="log('submit')"
				/>

				<k-code>{{ values.playground }}</k-code>
			</k-stack>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		api: String,
		columns: Array,
		content: Object,
		diff: Object,
		lock: Object
	},
	data() {
		return {
			hasDiff: true,
			isLocked: false,
			// every example gets its own copy of the content,
			// otherwise they would all share the same object
			values: {
				default: { ...this.content },
				diff: { ...this.content },
				locked: { ...this.content },
				playground: { ...this.content },
				single: { ...this.content }
			}
		};
	},
	computed: {
		singleColumn() {
			return [
				{
					fields: this.columns.reduce(
						(fields, column) => ({ ...fields, ...column.fields }),
						{}
					),
					width: "1/1"
				}
			];
		}
	},
	methods: {
		log(event) {
			alert(event);
		}
	}
};
</script>

<style>
.k-lab-example .k-model-form + .k-code,
.k-lab-example .k-grid + .k-model-form {
	margin-top: var(--spacing-6);
}
</style>
