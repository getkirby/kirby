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
						:has-diff="hasDiff"
						:is-locked="isLocked"
						editor="editor@getkirby.com"
						modified="2024-10-01T17:00:00"
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
					:value="hasDiff"
					text="has-diff"
					@input="hasDiff = $event"
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
		<k-lab-example label="View Buttons: Empty">
			<k-header>
				Home
				<template slot="buttons">
					<k-view-buttons :buttons="[]">
						<template #after>
							<k-form-controls
								:has-diff="true"
								editor="editor@getkirby.com"
								modified="2024-10-01T17:00:00"
								@discard="log('discard')"
								@submit="log('submit')"
							/>
						</template>
					</k-view-buttons>
				</template>
			</k-header>
		</k-lab-example>
		<k-lab-example label="View Buttons: Mixed">
			<k-header>
				Home
				<template slot="buttons">
					<k-view-buttons :buttons="viewButtons">
						<template #after>
							<k-form-controls
								:has-diff="true"
								editor="editor@getkirby.com"
								modified="2024-10-01T17:00:00"
								@discard="log('discard')"
								@submit="log('submit')"
							/>
						</template>
					</k-view-buttons>
				</template>
			</k-header>
		</k-lab-example>
		<k-lab-example label="Saving">
			<k-form-controls :has-diff="true" :is-processing="true" />
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
			hasDiff: false,
			isLocked: false,
			viewButtons: [
				{
					key: "open",
					props: {
						icon: "open",
						title: "Open"
					}
				}
			]
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
