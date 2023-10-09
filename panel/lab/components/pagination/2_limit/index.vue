<template>
	<k-lab-examples>
		<k-lab-example label="Limit: 5">
			<k-pagination
				:page="pageA"
				:total="50"
				:limit="5"
				:details="true"
				@paginate="pageA = $event.page"
			/>
		</k-lab-example>

		<k-lab-example label="Limit: 25">
			<k-pagination
				:page="pageB"
				:total="50"
				:limit="25"
				:details="true"
				@paginate="pageB = $event.page"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			pageA: 1,
			pageB: 1,
		};
	},
};
</script>
