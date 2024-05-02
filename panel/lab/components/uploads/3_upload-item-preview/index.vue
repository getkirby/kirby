<template>
	<k-lab-examples>
		<k-lab-example label="Image with preview">
			<k-upload-item-preview
				type="image/jpeg"
				url="https://picsum.photos/200/200"
			/>
		</k-lab-example>
		<k-lab-example label="Image without preview">
			<k-upload-item-preview
				type="image/bitmap"
				url="https://picsum.photos/200/200"
			/>
		</k-lab-example>
		<k-lab-example label="Audio">
			<k-upload-item-preview type="audio/mp3" url="/some.mp3" />
		</k-lab-example>
		<k-lab-example label="Video">
			<k-upload-item-preview type="video/mp4" url="/some.mp4" />
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-example .k-upload-item-preview {
	width: 6rem;
}
</style>
