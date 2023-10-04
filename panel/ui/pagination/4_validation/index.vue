<template>
	<k-ui-examples>
		<k-ui-example label="Validate function that stops paginate">
			<k-pagination
				:page="page"
				:total="50"
				:limit="5"
				:details="true"
				:validate="validate"
				@paginate="page = $event.page"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			page: 1,
		};
	},
	methods: {
		async validate() {
			throw new Error("Validation error");
		},
	},
};
</script>
