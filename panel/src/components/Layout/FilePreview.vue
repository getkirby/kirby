<template>
	<div class="k-file-preview">
		<div class="k-file-preview-thumb">
			<k-link :to="url" :title="$t('open')" target="_blank">
				<k-image-frame v-if="image.src" v-bind="image" />
				<k-icon-frame v-else v-bind="image" />
			</k-link>
		</div>

		<ul class="k-file-preview-details">
			<li v-for="detail in details" :key="detail.title">
				<h3>{{ detail.title }}</h3>
				<p>
					<k-link
						v-if="detail.link"
						:to="detail.link"
						tabindex="-1"
						target="_blank"
					>
						/{{ detail.text }}
					</k-link>
					<template v-else>
						{{ detail.text }}
					</template>
				</p>
			</li>
		</ul>
	</div>
</template>

<script>
export default {
	props: {
		details: Array,
		image: Object,
		url: String
	}
};
</script>
<style>
.k-file-preview {
	display: grid;
	background: var(--color-dark);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-6);
}

.k-file-preview-thumb {
	--icon-size: 2rem;

	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--color-slate-800) var(--pattern);
}
.k-file-preview-thumb > .k-link {
	display: block;
	width: 100%;
	padding: min(4vw, var(--spacing-12));
	outline: 0;
}

.k-file-preview-details {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(6rem, 1fr));
	grid-gap: var(--spacing-6) var(--spacing-12);
	padding: var(--spacing-6);
	line-height: 1.5em;
	align-self: center;
}
.k-file-preview-details h3 {
	font-size: var(--text-sm);
	font-weight: 500;
	color: var(--color-gray-500);
}
.k-file-preview-details :where(p, a) {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: rgba(255, 255, 255, 0.75);
	font-size: var(--text-sm);
}

@container (min-width: 30rem) {
	.k-file-preview {
		grid-template-columns: 50% auto;
	}
	.k-file-preview-details {
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	}
}
@container (min-width: 65rem) {
	.k-file-preview {
		grid-template-columns: 33.333% auto;
	}
}
@container (min-width: 90rem) {
	.k-file-preview {
		grid-template-columns: 25% auto;
	}
}
</style>
