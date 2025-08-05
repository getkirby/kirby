<template>
	<section v-if="slots.length" class="k-lab-docs-section">
		<k-headline class="h3">Slots</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<tr>
						<th style="width: 10rem">Slot</th>
						<th>Description</th>
						<th v-if="hasBindings">Bindings</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="slot in slots" :key="slot.name">
						<td style="width: 12rem">
							<k-text>
								<code>{{ slot.name }}</code>
								<div v-if="slot.since?.length" class="k-lab-docs-since">
									since {{ slot.since }}
								</div>
							</k-text>
						</td>
						<td>
							<k-lab-docs-warning title="Deprecated" :text="slot.deprecated" />
							<k-text :html="slot.description" />
						</td>
						<td v-if="hasBindings">
							<k-lab-docs-params :params="slot.bindings" />
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
	},
	computed: {
		hasBindings() {
			return this.slots.filter((slot) => slot.bindings.length).length;
		}
	}
};

export default {
	mixins: [props]
};
</script>
