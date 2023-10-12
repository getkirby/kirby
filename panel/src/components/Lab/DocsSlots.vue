<template>
	<section v-if="slots.length" class="k-lab-docs-section">
		<k-headline class="h3">Slots</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<th style="width: 10rem">Slot</th>
					<th>Description</th>
				</thead>
				<tbody>
					<tr v-for="slot in slots" :key="slot.name">
						<td style="width: 12rem">
							<k-text>
								<code>{{ slot.name }}</code>
							</k-text>
						</td>
						<td>
							<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
							<k-text v-html="slot.description" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</section>
</template>

<script>
export const props = {
	props: {
		slots: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props]
};
</script>
