<template>
	<k-lab-examples>
		<k-lab-example label="theme">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					theme="info"
				>
					{{ ratio }}
				</k-frame>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="back: pattern">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					back="pattern"
				>
					<img :src="`https://picsum.photos/600/850?t=` + $helper.uuid()" />
				</k-frame>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="icons">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					theme="passive"
				>
					<k-icon type="heart" />
				</k-frame>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="buttons">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					theme="passive"
				>
					<k-button icon="add" theme="passive">Add something …</k-button>
				</k-frame>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="images">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					theme="passive"
				>
					<img :src="`https://picsum.photos/600/850?t=` + $helper.uuid()" />
				</k-frame>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="images: cover">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:cover="true"
					:ratio="ratio"
					theme="passive"
				>
					<img :src="`https://picsum.photos/600/850?t=` + $helper.uuid()" />
				</k-frame>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="video: cover">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-frame
					v-for="ratio in ratios"
					:key="ratio"
					:cover="true"
					:ratio="ratio"
					theme="passive"
				>
					<iframe
						src="https://www.youtube-nocookie.com/embed/MurryIhhIFg"
						frameborder="0"
						allow="autoplay; encrypted-media"
						allowfullscreen
					></iframe>
				</k-frame>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		ratios() {
			return ["1/1", "4/3", "16/9"];
		}
	}
};
</script>
