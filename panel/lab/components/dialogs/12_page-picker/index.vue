<template>
	<k-lab-examples>
		<k-lab-example label="Empty">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-page-picker-dialog'
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example label="Items">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-page-picker-dialog',
						props: {
							items: items,
							value: selected
						},
						on: {
							submit: ({ ids }) => {
								selected = ids;
								$panel.dialog.close();
							}
						}
					})
				"
			>
				Open
			</k-button>
			<br /><br />
			<k-code>Submitted: {{ selected.join(", ") }}</k-code>
		</k-lab-example>

		<k-lab-example label="With parent">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-page-picker-dialog',
						props: {
							items: items,
							parent: {
								id: 'test/a',
								title: 'Parent',
								parent: 'test'
							},
							value: selected
						},
						on: {
							submit: ({ ids }) => {
								selected = ids;
								$panel.dialog.close();
							}
						}
					})
				"
			>
				Open
			</k-button>
			<br /><br />
			<k-code>Submitted: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
		<k-lab-example label="Layout: cards">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-page-picker-dialog',
						props: {
							items: items,
							layout: 'cards',
							value: selected
						},
						on: {
							submit: ({ ids }) => {
								selected = ids;
								$panel.dialog.close();
							}
						}
					})
				"
			>
				Open
			</k-button>
			<br /><br />
			<k-code>Submitted: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
		<k-lab-example label="Multiple: false">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-page-picker-dialog',
						props: {
							items: items,
							multiple: false,
							value: selected
						},
						on: {
							submit: ({ ids }) => {
								selected = ids;
								$panel.dialog.close();
							}
						}
					})
				"
			>
				Open
			</k-button>
			<br /><br />
			<k-code>Submitted: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
		<k-lab-example label="Size: huge">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-page-picker-dialog',
						props: {
							items: items,
							size: 'huge',
							value: selected
						},
						on: {
							submit: ({ ids }) => {
								selected = ids;
								$panel.dialog.close();
							}
						}
					})
				"
			>
				Open
			</k-button>
			<br /><br />
			<k-code>Submitted: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		items: {
			type: Array
		}
	},
	data() {
		return {
			selected: []
		};
	}
};
</script>
