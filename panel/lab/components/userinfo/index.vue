<template>
	<k-lab-examples>
		<k-lab-example label="Name & avatar">
			<k-user-info :user="editor" />
		</k-lab-example>
		<k-lab-example label="Name only (icon fallback)">
			<k-user-info :user="{ name: 'Editor', email: 'editor@example.com' }" />
		</k-lab-example>
		<k-lab-example label="Email only">
			<k-user-info :user="{ email: 'editor@example.com' }" />
		</k-lab-example>
		<k-lab-example label="String">
			<k-user-info user="editor@example.com" />
		</k-lab-example>
		<k-lab-example label="label">
			<k-user-info :user="editor" label="Author" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		editor() {
			return {
				name: "Editor",
				email: "editor@example.com",
				avatar: "https://picsum.photos/200/200?t=" + this.$helper.uuid()
			};
		}
	}
};
</script>
