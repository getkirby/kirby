<template>
	<k-lab-examples>
		<k-lab-example>
			<k-header :editable="true">
				Title
				<k-button-group slot="buttons">
					<k-button icon="open" size="sm" variant="filled" />
					<k-button icon="cog" size="sm" variant="filled" />
					<k-button
						icon="circle-filled"
						size="sm"
						text="Public"
						theme="positive-icon"
						variant="filled"
					/>

					<k-form-controls
						:is-unsaved="isUnsaved"
						:is-locked="isLocked"
						editor="editor@getkirby.com"
						modified="5 minutes ago"
						preview="https://getkirby.com"
						@discard="log('discard')"
						@submit="log('submit')"
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
					:value="isUnsaved"
					text="is-unsaved"
					@input="isUnsaved = $event"
				/>
				<k-input
					type="toggle"
					:value="isLocked"
					text="is-locked"
					@input="isLocked = $event"
				/>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Unsaved">
			<k-form-controls
				:is-unsaved="true"
				@discard="log('discard')"
				@submit="log('submit')"
			/>
		</k-lab-example>
		<k-lab-example label="Locked">
			<k-form-controls
				:is-locked="true"
				editor="editor@getkirby.com"
				modified="5 minutes ago"
				preview="https://getkirby.com"
				@discard="log('discard')"
				@submit="log('submit')"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			isLocked: false,
			isUnsaved: false
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
