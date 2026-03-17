<template>
	<k-lab-examples>
		<k-lab-example label="tabs">
			<k-tabs :tabs="tabs.slice(0, 2)" />
		</k-lab-example>
		<k-lab-example label="too many tabs">
			<k-tabs :tabs="tabs" tab="f" />
		</k-lab-example>
		<k-lab-example label="theme: notice">
			<k-tabs :tabs="tabs" theme="notice" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		tabs() {
			return [
				{
					name: "content",
					label: "Content"
				},
				{
					name: "settings",
					label: "Settings",
					badge: 3
				},
				{
					name: "c",
					label: "Just another tab C"
				},
				{
					name: "d",
					label: "Just another tab D"
				},
				{
					name: "e",
					label: "Just another tab E"
				},
				{
					name: "f",
					label: "Just another tab F"
				},
				{
					name: "g",
					label: "Just another tab G"
				},
				{
					name: "h",
					label: "Just another tab H"
				},
				{
					name: "i",
					label: "Just another tab I"
				},
				{
					name: "j",
					label: "Just another tab J"
				},
				{
					name: "k",
					label: "Just another tab K"
				},
				{
					name: "l",
					label: "Just another tab L"
				}
			];
		}
	}
};
</script>
