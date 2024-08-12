<template>
	<k-lab-examples>
		<k-lab-example label="Image">
			<k-upload-item-preview
				type="image/jpeg"
				url="https://picsum.photos/100/200"
			/>
		</k-lab-example>
		<k-lab-example label="Image & cover: false">
			<k-upload-item-preview
				:cover="false"
				type="image/jpeg"
				url="https://picsum.photos/100/200"
			/>
		</k-lab-example>
		<k-lab-example label="Image & cover: false & back: white">
			<k-upload-item-preview
				back="white"
				:cover="false"
				type="image/jpeg"
				url="https://picsum.photos/100/200"
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
		<k-lab-example label="Icon">
			<k-upload-item-preview icon="image" url="/some.mp4" />
		</k-lab-example>
		<k-lab-example label="Color">
			<k-upload-item-preview color="pink-500" icon="heart" url="/some.mp4" />
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-example .k-upload-item-preview {
	width: 6rem;
}
</style>
