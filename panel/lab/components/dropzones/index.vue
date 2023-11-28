<template>
	<k-lab-examples>
		<k-lab-example label="dropzone">
			<k-dropzone @drop="onDrop">
				<k-box
					theme="empty"
					text="Drag files here …"
					icon="upload"
					height="5rem"
					align="center"
				/>
			</k-dropzone>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		onDrop(files) {
			alert(files.length + " file(s) dropped");
		}
	}
};
</script>
