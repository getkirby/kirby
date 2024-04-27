<template>
	<div class="k-file-preview">
		<div class="k-file-preview-thumb-column">
			<div class="k-file-preview-thumb">
				<slot name="thumb" />

				<template v-if="options.length">
					<k-button
						icon="dots"
						size="xs"
						style="color: var(--color-gray-500)"
						@click="$refs.dropdown.toggle()"
					/>
					<k-dropdown-content ref="dropdown" :options="options" theme="light" />
				</template>
			</div>
		</div>

		<div class="k-file-preview-details">
			<dl>
				<div v-for="detail in details" :key="detail.title">
					<dt>{{ detail.title }}</dt>
					<dd>
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
					</dd>
				</div>
			</dl>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		details: {
			default: () => [],
			type: Array
		},
		options: {
			default: () => [],
			type: Array
		}
	}
};
</script>
<style>
:root {
	--file-preview-back: var(--color-gray-900);
	--file-preview-text: hsla(0, 100%, var(--color-l-max), 0.75);
}

.k-panel[data-theme="dark"] {
	--file-preview-back: var(--color-gray-100);
	--file-preview-text: hsla(0, 100%, var(--color-l-min), 0.75);
}

.k-file-preview {
	display: grid;
	align-items: stretch;
	background: var(--file-preview-back);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-12);
	overflow: hidden;
}

/* Thumb */
.k-file-preview-thumb-column {
	background: var(--pattern);
	aspect-ratio: 1/1;
}
.k-file-preview-thumb {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
	padding: var(--spacing-10);
	container-type: size;
}
.k-file-preview-thumb :where(img, audio, video) {
	width: auto;
	max-width: 100cqw;
	max-height: 100cqh;
}
.k-file-preview-thumb > .k-button {
	position: absolute;
	top: var(--spacing-2);
	inset-inline-start: var(--spacing-2);
}

/* Details */
.k-file-preview-details {
	display: grid;
}
.k-file-preview-details dl {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
	grid-gap: var(--spacing-6) var(--spacing-12);
	align-self: center;
	padding: var(--spacing-6);
	line-height: 1.5em;
	padding: var(--spacing-6);
}
.k-file-preview-details dt {
	font-size: var(--text-sm);
	font-weight: 500;
	font-weight: var(--font-semi);
	color: var(--color-gray-500);
	margin-bottom: var(--spacing-1);
}
.k-file-preview-details :where(dd, a) {
	font-size: var(--text-xs);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: var(--file-preview-text);
	font-size: var(--text-sm);
}
<<<<<<< HEAD .k-file-preview-focus-info dd {
	display: flex;
	align-items: center;
}

.k-file-preview-focus-info .k-button {
	--button-color-back: var(--color-gray-800);
	--button-padding: var(--spacing-2);
}
.k-file-preview[data-has-focus="true"] .k-file-preview-focus-info .k-button {
	flex-direction: row-reverse;
}
.k-panel[data-theme="dark"] .k-file-preview-focus-info .k-button {
	--button-color-back: var(--color-gray-400);
}
=======>>>>>>>d79c7ccb3
	(File preview components per type)
	@container
	(min-width: 36rem) {
	.k-file-preview {
		grid-template-columns: 50% auto;
	}
	.k-file-preview-thumb-column {
		aspect-ratio: auto;
	}
}

@container (min-width: 65rem) {
	.k-file-preview {
		grid-template-columns: 33.333% auto;
	}
	.k-file-preview-thumb-column {
		aspect-ratio: 1/1;
	}
}
@container (min-width: 90rem) {
	.k-file-preview-layout {
		grid-template-columns: 25% auto;
	}
}
</style>
