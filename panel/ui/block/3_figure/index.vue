<template>
	<k-ui-examples>
		<k-ui-example label="Empty">
			<k-block-figure
				:is-empty="true"
				empty-icon="image"
				empty-text="Select an image …"
			/>
		</k-ui-example>
		<k-ui-example label="Image">
			<k-block-figure>
				<k-image-frame
					:cover="true"
					ratio="16/9"
					src="https://picsum.photos/1200/600"
				/>
			</k-block-figure>
		</k-ui-example>
		<k-ui-example label="Caption">
			<k-block-figure caption="This is a nice caption">
				<k-image-frame
					:cover="true"
					ratio="16/9"
					src="https://picsum.photos/1200/600"
				/>
			</k-block-figure>
		</k-ui-example>
		<k-ui-example label="Video">
			<k-block-figure caption="This is a nice caption">
				<k-frame :cover="true" ratio="16/9">
					<iframe
						src="https://www.youtube-nocookie.com/embed/MurryIhhIFg"
						frameborder="0"
						allow="autoplay; encrypted-media"
						allowfullscreen
					/>
				</k-frame>
			</k-block-figure>
		</k-ui-example>
	</k-ui-examples>
</template>
