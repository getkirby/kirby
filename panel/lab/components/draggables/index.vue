<template>
	<k-lab-examples>
		<k-lab-example label="Simple list">
			<k-draggable element="k-grid" style="--columns: 1; gap: 2px">
				<k-box v-for="n in 5" :key="n" :text="'' + n" theme="white" />
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Sync list prop">
			<k-draggable
				:list="itemsA"
				element="k-grid"
				style="--columns: 1; gap: 2px"
			>
				<k-box v-for="n in itemsA" :key="n" :text="'' + n" theme="white" />
			</k-draggable>

			<k-code language="javascript">{{ itemsA }}</k-code>
		</k-lab-example>

		<k-lab-example label="Groups">
			<k-grid variant="fields">
				<k-column width="1/2">
					<k-draggable
						:list="itemsA"
						element="k-grid"
						group="foo"
						style="--columns: 1; gap: 2px"
					>
						<k-box v-for="n in itemsA" :key="n" :text="'' + n" theme="white" />
					</k-draggable>

					<k-code language="javascript">{{ itemsA }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-draggable
						:list="itemsB"
						element="k-grid"
						group="foo"
						style="--columns: 1; gap: 2px"
					>
						<k-box v-for="n in itemsB" :key="n" :text="'' + n" theme="white" />
					</k-draggable>

					<k-code language="javascript">{{ itemsB }}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Different groups">
			<k-grid variant="fields">
				<k-column width="1/2">
					<k-draggable
						:list="itemsA"
						element="k-grid"
						group="foo"
						style="--columns: 1; gap: 2px"
					>
						<k-box v-for="n in itemsA" :key="n" :text="'' + n" theme="white" />
					</k-draggable>
				</k-column>
				<k-column width="1/2">
					<k-draggable
						:list="itemsB"
						element="k-grid"
						group="bar"
						style="--columns: 1; gap: 2px"
					>
						<k-box v-for="n in itemsB" :key="n" :text="'' + n" theme="white" />
					</k-draggable>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Columns">
			<k-draggable element="k-grid" style="--columns: 4; gap: 0.5rem">
				<k-box v-for="n in 12" :key="n" :text="'' + n" theme="white" />
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Handles">
			<k-draggable
				:handle="true"
				element="k-grid"
				style="--columns: 1; gap: 2px"
			>
				<k-box v-for="n in 5" :key="n" theme="white">
					<k-sort-handle />
					{{ n }}
				</k-box>
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Footer non-draggable">
			<k-draggable element="k-grid" style="--columns: 1; gap: 2px">
				<k-box v-for="n in 5" :key="n" :text="'' + n" theme="white" />

				<template #footer>
					<k-box text="Footer" theme="passive" />
				</template>
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Disabled">
			<k-draggable
				:disabled="disabled"
				element="k-grid"
				style="--columns: 1; gap: 2px"
			>
				<k-box v-for="n in 4" :key="n" :text="'' + n" theme="white" />
			</k-draggable>

			<input id="checkbox" type="checkbox" v-model="disabled" />
			<label for="checkbox">Disabled: {{ disabled }}</label>
		</k-lab-example>

		<k-lab-example label="Events">
			<k-draggable
				:list="itemsA"
				element="k-grid"
				style="--columns: 1; gap: 2px"
				@change="event('change', $event)"
				@end="event('end', $event)"
				@sort="event('sort', $event)"
				@start="event('start', $event)"
			>
				<k-box v-for="n in itemsA" :key="n" :text="'' + n" theme="white" />
			</k-draggable>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			itemsA: Array.from({ length: 4 }, (_, i) => i + 1),
			itemsB: Array.from({ length: 4 }, (_, i) => i + 5),
			disabled: true
		};
	},
	methods: {
		event(type, payload) {
			console.log(type, payload);
		}
	}
};
</script>
