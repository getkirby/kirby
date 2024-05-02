<template>
	<k-lab-examples>
		<k-lab-example label="Upload Item">
			<k-upload-item
				extension="jpeg"
				type="image/jpeg"
				name="test"
				niceSize="128 kB"
				url="https://picsum.photos/200/200"
				@remove="remove"
				@rename="rename"
			/>
		</k-lab-example>
		<k-lab-example label="Progress">
			<k-upload-item
				:progress="80"
				extension="jpeg"
				type="image/jpeg"
				name="test"
				niceSize="128 kB"
				url="https://picsum.photos/200/200"
				@remove="remove"
				@rename="rename"
			/>
		</k-lab-example>
		<k-lab-example label="Completed">
			<k-upload-item
				:completed="true"
				extension="jpeg"
				type="image/jpeg"
				name="test"
				niceSize="128 kB"
				url="https://picsum.photos/200/200"
				@remove="remove"
				@rename="rename"
			/>
		</k-lab-example>
		<k-lab-example label="Error">
			<k-upload-item
				extension="jpeg"
				type="image/jpeg"
				name="test"
				niceSize="128 kB"
				error="The file could not be uploaded"
				url="https://picsum.photos/200/200"
				@remove="remove"
				@rename="rename"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		remove() {
			console.log("Remove");
		},
		rename(name) {
			console.log("Rename to", name);
		}
	}
};
</script>
