<template>
	<section v-if="events.length" class="k-lab-docs-section">
		<k-headline class="h3">Events</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<th style="width: 10rem">Event</th>
					<th>Description</th>
					<th v-if="hasProperties">Properties</th>
				</thead>
				<tbody>
					<tr v-for="event in events" :key="event.name">
						<td>
							<k-text>
								<code>{{ event.name }}</code>
								<div v-if="event.since?.length" class="k-lab-docs-since">
									since {{ event.since }}
								</div>
							</k-text>
						</td>
						<td>
							<k-lab-docs-warning title="Deprecated" :text="event.deprecated" />
							<k-text :html="event.description" />
						</td>
						<td v-if="hasProperties">
							<k-lab-docs-params :params="event.properties" />
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
		events: {
			default: () => [],
			type: Array
		}
	},
	computed: {
		hasProperties() {
			return this.events.filter((event) => event.properties.length).length;
		}
	}
};

export default {
	mixins: [props]
};
</script>
