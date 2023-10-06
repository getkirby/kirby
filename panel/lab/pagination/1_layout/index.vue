<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-pagination
				:page="page"
				:total="total"
				@paginate="page = $event.page"
			/>
		</k-lab-example>
		<k-lab-example label="Details">
			<k-pagination
				:page="page"
				:total="total"
				:details="true"
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
			total: 50,
		};
	},
};
</script>
