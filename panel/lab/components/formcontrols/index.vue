<template>
	<k-lab-examples>
		<k-lab-example>
			<k-header :editable="true">
				Title
				<k-button-group slot="buttons">
					<k-button icon="open" size="sm" variant="filled" />
					<k-button icon="cog" size="sm" variant="filled" />
					<k-button
						:icon="isDraft ? 'circle' : 'circle-filled'"
						size="sm"
						variant="filled"
						:text="isDraft ? 'Draft' : 'Public'"
						:theme="isDraft ? 'negative-icon' : 'positive-icon'"
					/>

					<k-form-controls
						:is-draft="isDraft"
						:is-published="isPublished"
						:is-saved="isSaved"
						:is-locked="isLocked ? 'bastian@getkirby.com' : false"
						@discard="log('discard')"
						@save="log('save')"
						@publish="log('publish')"
					/>
				</k-button-group>
			</k-header>

			<k-grid
				style="
					--columns: 2;
					--grid-inline-gap: var(--spacing-1);
					--grid-block-gap: var(--spacing-1);
				"
			>
				<k-input
					type="toggle"
					:value="isDraft"
					text="is-draft"
					@input="isDraft = $event"
				/>
				<k-input
					type="toggle"
					:value="isSaved"
					text="is-saved"
					@input="isSaved = $event"
				/>
				<k-input
					type="toggle"
					:value="isPublished"
					text="is-published"
					@input="isPublished = $event"
				/>
				<k-input
					type="toggle"
					:value="isLocked"
					text="is-locked"
					@input="isLocked = $event"
				/>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Published">
			<k-form-controls :is-published="true" />
		</k-lab-example>
		<k-lab-example label="Unsaved">
			<k-form-controls
				@discard="log('discard')"
				@save="log('save')"
				@publish="log('publish')"
			/>
		</k-lab-example>
		<k-lab-example label="Saved">
			<k-form-controls
				:is-saved="true"
				@discard="log('discard')"
				@publish="log('publish')"
			/>
		</k-lab-example>
		<k-lab-example label="Locked">
			<k-form-controls is-locked="bastian@getkirby.com" />
		</k-lab-example>
		<k-lab-example label="Draft: Saved">
			<k-form-controls
				:is-draft="true"
				:is-saved="true"
				@discard="log('discard')"
				@publish="log('publish')"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			isDraft: false,
			isLocked: false,
			isPublished: false,
			isSaved: false
		};
	},
	methods: {
		log(action) {
			alert(action);
		}
	}
};
</script>

<style>
.k-lab-example .k-header {
	margin-bottom: 2rem;
}
</style>
