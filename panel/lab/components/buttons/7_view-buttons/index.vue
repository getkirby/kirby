<template>
	<k-lab-examples>
		<k-lab-example label="View Buttons: buttons & slot">
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
		<k-lab-example label="View Buttons: no buttons & slot">
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
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
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
