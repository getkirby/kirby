<template>
	<k-lab-examples>
		<k-lab-example label="Dropzone">
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

		<k-lab-example label="In dialog">
			<k-button variant="filled" @click="openDialog">Open dialog</k-button>
		</k-lab-example>

		<k-lab-example label="In drawer">
			<k-button variant="filled" @click="openDrawer">Open drawer</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		onDrop(files) {
			alert(files.length + " file(s) dropped");
		},
		openDialog() {
			this.$panel.dialog.open({
				component: "k-text-dialog",
				props: { text: "Drop files here", onDrop: this.onDrop }
			});
		},
		openDrawer() {
			this.$panel.drawer.open({
				component: "k-text-drawer",
				props: { text: "Drop files here", visible: true, onDrop: this.onDrop }
			});
		}
	}
};
</script>
