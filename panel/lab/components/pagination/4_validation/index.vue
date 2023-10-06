<template>
	<k-lab-examples>
		<k-lab-example label="Validate function that stops paginate">
			<k-pagination
				:page="page"
				:total="50"
				:limit="5"
				:details="true"
				:validate="validate"
				@paginate="page = $event.page"
			/>
		</k-lab-example>
	</k-lab-examples>
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
