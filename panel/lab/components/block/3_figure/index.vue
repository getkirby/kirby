<template>
	<k-lab-examples>
		<k-lab-example label="Empty">
			<k-block-figure
				:is-empty="true"
				empty-icon="image"
				empty-text="Select an image …"
			/>
		</k-lab-example>
		<k-lab-example label="Empty & disabled">
			<k-block-figure
				:disabled="true"
				:is-empty="true"
				empty-icon="image"
				empty-text="Select an image …"
			/>
		</k-lab-example>
		<k-lab-example label="Image">
			<k-block-figure>
				<k-image-frame
					:cover="true"
					ratio="16/9"
					src="https://picsum.photos/1200/600"
				/>
			</k-block-figure>
		</k-lab-example>
		<k-lab-example label="Caption">
			<k-block-figure caption="This is a nice caption">
				<k-image-frame
					:cover="true"
					ratio="16/9"
					src="https://picsum.photos/1200/600"
				/>
			</k-block-figure>
		</k-lab-example>
		<k-lab-example label="Back">
			<k-block-figure
				caption="This is a nice caption"
				back="var(--pattern-light)"
			>
				<k-image-frame
					ratio="16/9"
					src="https://fakeimg.pl/1200x600/ff0000,0/333333,255/?text=Pattern"
				/>
			</k-block-figure>
		</k-lab-example>
		<k-lab-example label="Video">
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
		</k-lab-example>
	</k-lab-examples>
</template>
