<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-pagination
				:page="page"
				:total="total"
				@paginate="page = $event.page"
			/>
		</k-ui-example>
		<k-ui-example label="Details">
			<k-pagination
				:page="page"
				:total="total"
				:details="true"
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
			total: 50,
		};
	},
};
</script>
