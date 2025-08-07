<template>
	<k-lab-examples>
		<k-lab-example>
			<k-header :editable="true">
				Title

				<template #buttons>
					<k-button-group>
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
							:has-changes="hasChanges"
							:is-locked="isLocked"
							editor="editor@getkirby.com"
							modified="2024-10-01T17:00:00"
							preview="https://getkirby.com"
							@discard="log('discard')"
							@submit="log('submit')"
						/>
					</k-button-group>
				</template>
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
					:value="hasChanges"
					text="has-changes"
					@input="hasChanges = $event"
				/>
				<k-input
					type="toggle"
					:value="isLocked"
					text="is-locked"
					@input="isLocked = $event"
				/>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Changes">
			<k-form-controls
				:has-diff="true"
				editor="editor@getkirby.com"
				modified="2024-10-01T17:00:00"
				preview="https://getkirby.com"
				@discard="log('discard')"
				@submit="log('submit')"
			/>
		</k-lab-example>
		<k-lab-example label="Locked">
			<k-form-controls
				:is-locked="true"
				editor="editor@getkirby.com"
				modified="2024-10-01T17:00:00"
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
			hasChanges: false,
			isLocked: false
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
