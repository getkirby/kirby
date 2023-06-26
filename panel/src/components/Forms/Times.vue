<template>
	<div class="k-times">
		<div class="k-times-slot">
			<h3><k-icon type="sun" /> <span class="sr-only">Day</span></h3>
			<ul>
				<li v-for="time in day" :key="time.select">
					<hr v-if="time === '-'" />
					<k-button v-else @click="select(time.select)">{{
						time.display
					}}</k-button>
				</li>
			</ul>
		</div>
		<div class="k-times-slot">
			<h3><k-icon type="moon" /> <span class="sr-only">Night</span></h3>
			<ul>
				<li v-for="time in night" :key="time.select">
					<hr v-if="time === '-'" />
					<k-button v-else @click="select(time.select)">{{
						time.display
					}}</k-button>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
/**
 * The Times component displayes available times to choose from
 * @public
 *
 * @example <k-times value="12:12" @input="onInput" />
 */
export default {
	props: {
		display: {
			type: String,
			default: "HH:mm"
		},
		value: String
	},
	computed: {
		day() {
			return this.formatTimes([
				6,
				7,
				8,
				9,
				10,
				11,
				"-",
				12,
				13,
				14,
				15,
				16,
				17
			]);
		},
		night() {
			return this.formatTimes([18, 19, 20, 21, 22, 23, "-", 0, 1, 2, 3, 4, 5]);
		}
	},
	methods: {
		formatTimes(times) {
			return times.map((time) => {
				if (time === "-") {
					return time;
				}

				const dt = this.$library.dayjs(time + ":00", "H:mm");
				return {
					display: dt.format(this.display),
					select: dt.toISO("time")
				};
			});
		},
		select(time) {
			this.$emit("input", time);
		}
	}
};
</script>

<style>
.k-times {
	--button-height: var(--height-sm);
	--button-padding: var(--spacing-3);
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: var(--spacing-3);
}
.k-times h3 {
	display: flex;
	align-items: center;
	padding-inline: var(--button-padding);
	height: var(--button-height);
	margin-bottom: var(--spacing-1);
}
.k-times .k-times-slot hr {
	margin: var(--spacing-2) var(--spacing-3);
}
</style>
