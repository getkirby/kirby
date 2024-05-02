<template>
	<li :data-completed="completed" class="k-upload-item">
		<k-upload-item-preview :type="type" :url="url" />

		<k-input
			:disabled="completed"
			:after="'.' + extension"
			:novalidate="true"
			:required="true"
			:value="name"
			class="k-upload-item-input"
			type="slug"
			@input="$emit('rename', $event)"
		/>

		<div class="k-upload-item-body">
			<p class="k-upload-item-meta">
				{{ niceSize }}
				<template v-if="progress"> - {{ progress }}% </template>
			</p>
			<p v-if="error" class="k-upload-item-error">
				{{ error }}
			</p>
			<k-progress
				v-else-if="progress"
				:value="progress"
				class="k-upload-item-progress"
			/>
		</div>

		<div class="k-upload-item-toggle">
			<k-button
				v-if="!completed && !progress"
				icon="remove"
				@click="$emit('remove')"
			/>
			<k-button v-else-if="!completed" :disabled="true" icon="loader" />
			<k-button v-else icon="check" theme="positive" @click="$emit('remove')" />
		</div>
	</li>
</template>

<script>
export default {
	props: {
		completed: Boolean,
		error: [String, Boolean],
		extension: String,
		id: String,
		name: String,
		niceSize: String,
		progress: Number,
		type: String,
		url: String
	}
};
</script>

<style>
.k-upload-item {
	accent-color: var(--color-focus);
	display: grid;
	grid-template-areas:
		"preview input input"
		"preview body toggle";
	grid-template-columns: 6rem 1fr auto;
	grid-template-rows: var(--input-height) 1fr;
	border-radius: var(--rounded);
	background: var(--color-white);
	box-shadow: var(--shadow);
	min-height: 6rem;
}
.k-upload-item-body {
	grid-area: body;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	padding: var(--spacing-2) var(--spacing-3);
	min-width: 0;
}
.k-upload-item-input.k-input {
	--input-color-border: transparent;
	--input-padding: var(--spacing-2) var(--spacing-3);
	--input-rounded: 0;
	grid-area: input;
	font-size: var(--text-sm);
	border-bottom: 1px solid var(--color-light);
	border-start-end-radius: var(--rounded);
}
.k-upload-item-input.k-input:focus-within {
	outline: 2px solid var(--color-focus);
	z-index: 1;
	border-radius: var(--rounded);
}
.k-upload-item-input .k-input-after {
	color: var(--color-gray-600);
}
.k-upload-item-meta {
	font-size: var(--text-xs);
	color: var(--color-gray-600);
}
.k-upload-item-error {
	font-size: var(--text-xs);
	margin-top: 0.25rem;
	color: var(--color-red-700);
}
.k-upload-item-progress {
	--progress-height: 0.25rem;
	--progress-color-back: var(--color-light);
}
.k-upload-item-toggle {
	grid-area: toggle;
	align-self: end;
}
.k-upload-item-toggle > * {
	padding: var(--spacing-3);
}
.k-upload-item[data-completed] .k-upload-item-progress {
	--progress-color-value: var(--color-green-400);
}
</style>
