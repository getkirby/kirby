<template>
	<k-lab-examples>
		<k-lab-example label="External URL">
			<k-video-frame
				url="https://www.youtube.com/watch?v=8xeEK2fiCH8&pp=ygUJa2lyYnkgY21z"
				ratio="16/9"
			/>
		</k-lab-example>
		<k-lab-example v-if="id" label="Internal file UUID">
			<k-video-frame :url="id" :controls="true" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		id: String
	}
};
</script>
