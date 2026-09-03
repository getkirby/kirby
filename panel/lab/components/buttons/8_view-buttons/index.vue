<template>
	<k-lab-examples>
		<k-lab-example label="View Buttons: buttons & slot">
			<k-header>
				Home
				<template #buttons>
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
				<template #buttons>
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
				},
				{
					key: "status",
					props: {
						icon: "status-listed",
						responsive: true,
						text: "Published",
						theme: "positive-icon"
					}
				},
				"-",
				{
					key: "magic",
					props: {
						badge: { text: 3, theme: "love" },
						icon: "wand",
						request: "requests/lab/request",
						title: "Do some magic"
					}
				},
				{
					key: "settings",
					props: {
						icon: "cog",
						options: [
							{ icon: "edit", text: "Rename" },
							{ icon: "copy", text: "Duplicate" },
							"-",
							{ icon: "trash", text: "Delete" }
						],
						title: "Settings"
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
